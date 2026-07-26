#!/usr/bin/env python3
"""Enrichissement des fiches de l'annuaire avec les données Google My Business.

Passe par l'API Places de Google, qui expose exactement les champs d'une fiche
GMB : téléphone, site web, horaires d'ouverture, note, nombre d'avis, statut
d'activité, coordonnées, adresse formatée, et l'identifiant de lieu.

Pourquoi l'API et non un aspirateur de pages Maps : Google bloque les accès
automatisés de ses pages, il faudrait donc contourner sa détection, et
l'extraction casserait à chaque changement de balisage. L'API renvoie la même
donnée, structurée, sans blocage ni rupture.

La base SIRENE déjà collectée sert d'entrée : on cherche par « raison sociale
+ adresse exacte », ce qui donne un taux d'appariement bien supérieur à une
recherche à l'aveugle, et on ne paie qu'un appel par établissement.

    export GOOGLE_PLACES_API_KEY="..."
    python3 scripts/enrichir_gmb.py 57              # un département
    python3 scripts/enrichir_gmb.py --tous          # tous les collectés
    python3 scripts/enrichir_gmb.py 57 --simuler    # coût, sans appel facturé
"""
import json
import os
import subprocess
import sys
import time

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(BASE, 'data', 'annuaire')
OUT = os.path.join(SRC, 'gmb')
CACHE = os.path.join(SRC, 'gmb', '_cache.json')
os.makedirs(OUT, exist_ok=True)

CLE = os.environ.get('GOOGLE_PLACES_API_KEY', '')

# Le palier de facturation dépend des champs demandés, pas d'un réglage :
# téléphone, horaires, note et site web relèvent du palier Enterprise, qui
# offre 1 000 appels gratuits par mois. C'est exactement le rythme de
# publication de l'annuaire (lots de 100), donc la collecte reste gratuite
# tant qu'on ne dépasse pas ce quota mensuel — d'où le garde-fou ci-dessous.
CHAMPS_RECHERCHE = 'places.id,places.displayName,places.formattedAddress'
CHAMPS_DETAIL = ','.join([
    'id', 'displayName', 'formattedAddress', 'nationalPhoneNumber',
    'websiteUri', 'regularOpeningHours', 'rating', 'userRatingCount',
    'businessStatus', 'location', 'primaryTypeDisplayName',
])

QUOTA_MENSUEL_GRATUIT = 1000          # palier Enterprise
TARIF = {'recherche': 32.0 / 1000, 'detail': 25.0 / 1000}  # USD au-delà du quota
COMPTEUR = os.path.join(SRC, 'gmb', '_quota.json')


def quota_utilise():
    """Appels Enterprise déjà consommés sur le mois en cours."""
    mois = time.strftime('%Y-%m')
    if os.path.exists(COMPTEUR):
        with open(COMPTEUR) as f:
            d = json.load(f)
        if d.get('mois') == mois:
            return d.get('appels', 0)
    return 0


def quota_ajouter(n):
    mois = time.strftime('%Y-%m')
    with open(COMPTEUR, 'w') as f:
        json.dump({'mois': mois, 'appels': quota_utilise() + n}, f)


def charger_cache():
    if os.path.exists(CACHE):
        with open(CACHE, encoding='utf-8') as f:
            return json.load(f)
    return {}


def sauver_cache(c):
    with open(CACHE, 'w', encoding='utf-8') as f:
        json.dump(c, f, ensure_ascii=False)


def poster(url, corps, masque):
    cmd = ['curl', '-s', '-m', '30', '-X', 'POST', url,
           '-H', f'X-Goog-Api-Key: {CLE}',
           '-H', f'X-Goog-FieldMask: {masque}',
           '-H', 'Content-Type: application/json',
           '-d', json.dumps(corps)]
    r = subprocess.run(cmd, capture_output=True)
    try:
        return json.loads(r.stdout)
    except json.JSONDecodeError:
        return None


def obtenir(url, masque):
    cmd = ['curl', '-s', '-m', '30', url,
           '-H', f'X-Goog-Api-Key: {CLE}',
           '-H', f'X-Goog-FieldMask: {masque}']
    r = subprocess.run(cmd, capture_output=True)
    try:
        return json.loads(r.stdout)
    except json.JSONDecodeError:
        return None


def chercher_lieu(fiche):
    """Retrouve l'identifiant de lieu à partir du nom et de l'adresse SIRENE."""
    requete = f"{fiche['nom']} {fiche.get('adresse') or ''}".strip()
    d = poster('https://places.googleapis.com/v1/places:searchText',
               {'textQuery': requete, 'languageCode': 'fr', 'maxResultCount': 1},
               CHAMPS_RECHERCHE)
    if not d:
        return None, 'reponse illisible'
    if 'error' in d:
        return None, d['error'].get('message', 'erreur API')
    lieux = d.get('places') or []
    if not lieux:
        return None, 'aucun resultat'
    return lieux[0].get('id'), None


def detailler(place_id):
    d = obtenir(f'https://places.googleapis.com/v1/places/{place_id}', CHAMPS_DETAIL)
    if not d or 'error' in d:
        return None
    horaires = (d.get('regularOpeningHours') or {}).get('weekdayDescriptions')
    return {
        'place_id': d.get('id'),
        'nom_gmb': (d.get('displayName') or {}).get('text'),
        'adresse_gmb': d.get('formattedAddress'),
        'telephone': d.get('nationalPhoneNumber'),
        'site_web': d.get('websiteUri'),
        'horaires': horaires,
        'note': d.get('rating'),
        'nb_avis': d.get('userRatingCount'),
        'statut': d.get('businessStatus'),
        'categorie_gmb': (d.get('primaryTypeDisplayName') or {}).get('text'),
        'lat_gmb': (d.get('location') or {}).get('latitude'),
        'lon_gmb': (d.get('location') or {}).get('longitude'),
    }


def traiter(dept, simuler=False):
    chemin = os.path.join(SRC, f'dept-{str(dept).zfill(2)}.json')
    if not os.path.exists(chemin):
        print(f"dept {dept} : pas de collecte SIRENE, ignoré")
        return 0, 0.0

    with open(chemin, encoding='utf-8') as f:
        fiches = json.load(f)

    if simuler:
        cout = len(fiches) * (TARIF['recherche'] + TARIF['detail'])
        print(f"dept {str(dept).zfill(2)} : {len(fiches):4d} fiches → {cout:6.2f} $ estimés")
        return len(fiches), cout

    if not CLE:
        print("ERREUR : la variable GOOGLE_PLACES_API_KEY n'est pas définie.")
        sys.exit(1)

    cache = charger_cache()
    enrichies, appels, echecs = [], 0, 0
    deja = quota_utilise()

    for i, fiche in enumerate(fiches, 1):
        siret = fiche['siret']
        if deja + appels >= QUOTA_MENSUEL_GRATUIT:
            # On s'arrête net plutôt que de basculer en facturé sans le dire.
            fiche['gmb'] = None
            fiche['gmb_motif'] = 'quota mensuel gratuit atteint'
            enrichies.append(fiche)
            continue
        if siret in cache:
            fiche.update(cache[siret])
            enrichies.append(fiche)
            continue

        place_id, motif = chercher_lieu(fiche)
        appels += 1
        if not place_id:
            fiche['gmb'] = None
            fiche['gmb_motif'] = motif
            echecs += 1
        else:
            detail = detailler(place_id)
            appels += 1
            if detail:
                fiche.update(detail)
                fiche['gmb'] = 'ok'
                cache[siret] = detail
            else:
                fiche['gmb'] = None
                fiche['gmb_motif'] = 'detail indisponible'
                echecs += 1

        enrichies.append(fiche)
        if i % 25 == 0:
            sauver_cache(cache)
            print(f"  … {i}/{len(fiches)}", flush=True)
        time.sleep(0.12)  # on reste loin des limites de débit

    sauver_cache(cache)
    quota_ajouter(appels)
    with open(os.path.join(OUT, f'dept-{str(dept).zfill(2)}.json'), 'w', encoding='utf-8') as f:
        json.dump(enrichies, f, ensure_ascii=False, indent=1)

    ok = len(enrichies) - echecs
    restant = max(0, QUOTA_MENSUEL_GRATUIT - quota_utilise())
    print(f"dept {str(dept).zfill(2)} : {ok}/{len(fiches)} appariés, "
          f"{echecs} sans correspondance · quota gratuit restant ce mois : {restant}",
          flush=True)
    return ok, 0.0


def main():
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    simuler = '--simuler' in sys.argv
    tous = '--tous' in sys.argv

    if tous:
        args = sorted(f[5:-5] for f in os.listdir(SRC) if f.startswith('dept-') and f.endswith('.json'))
    if not args:
        print(__doc__)
        return 1

    total, cout = 0, 0.0
    for d in args:
        n, c = traiter(d, simuler)
        total += n
        cout += c
    print(f"\nTotal : {total} fiches · ~{cout:.2f} $")
    if simuler:
        print("Simulation : aucun appel facturé n'a été émis.")
    return 0


if __name__ == '__main__':
    sys.exit(main())
