#!/usr/bin/env python3
"""Collecte des établissements de l'annuaire depuis l'API officielle
« Recherche d'entreprises » (annuaire-entreprises / data.gouv.fr).

Pourquoi cette source plutôt qu'un scraper de fiches Google :
  - donnée publique officielle, libre de réutilisation, sans authentification
  - SIRET, adresse réelle de l'établissement, commune, coordonnées GPS,
    tranche d'effectif, état administratif, date de création
  - stable : c'est une API versionnée, pas un DOM qui change chaque mois
  - aucun risque juridique ni blocage anti-robot

Les données Google (horaires, note, téléphone) viendront enrichir ensuite,
sur la base des SIRET déjà collectés.

Usage :
    python3 scripts/collecte_annuaire.py 57            # un département
    python3 scripts/collecte_annuaire.py 57 54 88 67   # plusieurs
    python3 scripts/collecte_annuaire.py --tous        # les 101
"""
import json
import os
import re
import subprocess
import sys
import time
import unicodedata

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUT = os.path.join(BASE, 'data', 'annuaire')
os.makedirs(OUT, exist_ok=True)
API = 'https://recherche-entreprises.api.gouv.fr/search'

# NAF retenus, avec l'activité qu'ils désignent dans notre annuaire.
# `strict` : le code suffit à qualifier l'établissement.
# `large`  : le code est trop générique, un mot-clé métier est exigé en plus.
NAF = {
    # 77.32Z couvre TOUTE la location d'engins de chantier — dragage,
    # terrassement, échafaudage, nacelles. Il représente 72 % de la collecte
    # et ne qualifie donc rien à lui seul : classé `large` après le pilote
    # Moselle, où il ramenait l'essentiel du bruit.
    '77.32Z': ('loueur',          'large'),
    '77.39Z': ('loueur',          'large'),   # location d'autres machines
    '46.69B': ('concessionnaire', 'large'),   # gros de fournitures et équipements industriels
    '28.22Z': ('concessionnaire', 'strict'),  # fabrication d'équipements de levage et manutention
    '33.12Z': ('sav',             'large'),   # réparation de machines
    '71.20B': ('controle_vgp',    'large'),   # analyses, essais et inspections techniques
    '85.59A': ('formation',       'large'),   # formation continue d'adultes
}

# Signaux d'exclusion : métiers voisins que les NAF ci-dessus ramènent
# systématiquement et qui ne relèvent pas de la manutention.
EXCLUSIONS = [
    'dragage', 'terrassement', 'travaux publics', 'btp', 'echafaudage',
    'demenagement', 'transport frigorifique', 'frigo', 'recyclage',
    'nettoyage', 'utilitaire', 'ascenseur', 'espaces verts', 'benne',
]

# Mots-clés métier, cherchés dans la raison sociale et l'enseigne.
MOTS = [
    'manutention', 'levage', 'chariot', 'elevateur', 'elevatrice', 'nacelle',
    'transpalette', 'gerbeur', 'palettis', 'palan', 'treuil', 'pont roulant',
    'caces', 'cariste', 'hydraulique', 'stockage', 'rayonnage', 'logistique',
    'intralogistique', 'fenwick', 'manitou', 'jungheinrich', 'toyota material',
    'linde material', 'still', 'hyster', 'yale', 'crown', 'haulotte',
]


def sans_accents(t):
    return ''.join(c for c in unicodedata.normalize('NFD', (t or '').lower())
                   if unicodedata.category(c) != 'Mn')


def pertinent(nom, enseignes):
    """Mots-clés métier trouvés dans la dénomination, et signaux d'exclusion."""
    hay = sans_accents(nom) + ' ' + sans_accents(' '.join(enseignes or []))
    return [m for m in MOTS if m in hay], [x for x in EXCLUSIONS if x in hay]


def get(url, essais=3):
    for n in range(essais):
        r = subprocess.run(['curl', '-s', '-m', '30', url], capture_output=True)
        if r.returncode == 0 and r.stdout:
            try:
                return json.loads(r.stdout)
            except json.JSONDecodeError:
                pass
        time.sleep(2 + 2 * n)
    return None


def collecter_dept(dept):
    trouves = {}
    for naf, (activite, mode) in NAF.items():
        page = 1
        while True:
            url = (f'{API}?activite_principale={naf}&departement={dept}'
                   f'&per_page=25&page={page}')
            d = get(url)
            if not d or not d.get('results'):
                break

            for r in d['results']:
                nom = r.get('nom_complet') or r.get('nom_raison_sociale') or ''
                for e in (r.get('matching_etablissements') or []):
                    # établissement fermé, ou hors du département visé
                    if e.get('etat_administratif') != 'A':
                        continue
                    if not (e.get('code_postal') or '').startswith(str(dept).zfill(2)):
                        continue

                    mots, exclus = pertinent(nom, e.get('liste_enseignes'))
                    if exclus:
                        continue  # métier voisin explicitement hors périmètre
                    if mode == 'large' and not mots:
                        continue  # code NAF trop générique et aucun signal métier

                    siret = e.get('siret')
                    if not siret:
                        continue
                    fiche = trouves.setdefault(siret, {
                        'siret': siret,
                        'siren': r.get('siren'),
                        'nom': (e.get('nom_commercial') or nom).strip(),
                        'enseignes': e.get('liste_enseignes') or [],
                        'adresse': e.get('adresse'),
                        'code_postal': e.get('code_postal'),
                        'commune': e.get('libelle_commune'),
                        'departement': str(dept).zfill(2),
                        'lat': e.get('latitude'),
                        'lon': e.get('longitude'),
                        'naf': e.get('activite_principale'),
                        'effectif': e.get('tranche_effectif_salarie'),
                        'date_creation': e.get('date_creation'),
                        'est_siege': e.get('est_siege'),
                        'activites': [],
                        'mots_cles': [],
                        'confiance': 'a_verifier',
                    })
                    if activite not in fiche['activites']:
                        fiche['activites'].append(activite)
                    for m in mots:
                        if m not in fiche['mots_cles']:
                            fiche['mots_cles'].append(m)
                    # La confiance définitive ne se joue pas ici : c'est la
                    # catégorie Google, récupérée à l'enrichissement, qui
                    # tranchera. Ce champ n'ordonne que la file de revue.
                    if mode == 'strict':
                        fiche['confiance'] = 'haute'
                    elif fiche['confiance'] != 'haute':
                        fiche['confiance'] = 'moyenne'

            if page >= (d.get('total_pages') or 1) or page >= 40:
                break
            page += 1
            time.sleep(0.35)
        time.sleep(0.35)

    fiches = sorted(trouves.values(), key=lambda f: (f['commune'] or '', f['nom']))
    chemin = os.path.join(OUT, f'dept-{str(dept).zfill(2)}.json')
    with open(chemin, 'w', encoding='utf-8') as f:
        json.dump(fiches, f, ensure_ascii=False, indent=1)

    par_act = {}
    for f in fiches:
        for a in f['activites']:
            par_act[a] = par_act.get(a, 0) + 1
    print(f"dept {str(dept).zfill(2)} : {len(fiches):4d} établissements  {par_act}", flush=True)
    return fiches


def main():
    args = sys.argv[1:]
    if not args:
        print(__doc__)
        return 1
    if args[0] == '--tous':
        depts = [f'{i:02d}' for i in range(1, 96) if i != 20] + ['2A', '2B', '971', '972', '973', '974']
    else:
        depts = args

    total = 0
    for d in depts:
        try:
            total += len(collecter_dept(d))
        except Exception as e:
            print(f"dept {d} : ERREUR {e}", flush=True)
    print(f"\nTotal : {total} établissements → {OUT}")
    return 0


if __name__ == '__main__':
    sys.exit(main())
