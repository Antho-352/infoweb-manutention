#!/usr/bin/env python3
"""Enrichissement des fiches par OpenStreetMap, via l'API Overpass.

Source libre, gratuite, sans clé ni quota : ce qui est récupérable ici l'est
avant de consommer le quota Google. La couverture des entreprises B2B
françaises reste partielle — de l'ordre de 10 à 15 % sur le pilote Moselle —
mais ce sont des données acquises sans rien dépenser.

Champs récupérés : téléphone, site web, horaires, adresse, coordonnées.

    python3 scripts/enrichir_osm.py 57
    python3 scripts/enrichir_osm.py --tous
"""
import json
import os
import re
import subprocess
import sys
import time
import unicodedata

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(BASE, 'data', 'annuaire')
OUT = os.path.join(SRC, 'osm')
os.makedirs(OUT, exist_ok=True)
OVERPASS = 'https://overpass-api.de/api/interpreter'

DEPTS = {  # nom OSM du département, nécessaire à la requête de zone
    '01': 'Ain', '02': 'Aisne', '03': 'Allier', '04': 'Alpes-de-Haute-Provence',
    '05': 'Hautes-Alpes', '06': 'Alpes-Maritimes', '07': 'Ardèche', '08': 'Ardennes',
    '09': 'Ariège', '10': 'Aube', '11': 'Aude', '12': 'Aveyron',
    '13': 'Bouches-du-Rhône', '14': 'Calvados', '15': 'Cantal', '16': 'Charente',
    '17': 'Charente-Maritime', '18': 'Cher', '19': 'Corrèze', '21': "Côte-d'Or",
    '22': "Côtes-d'Armor", '23': 'Creuse', '24': 'Dordogne', '25': 'Doubs',
    '26': 'Drôme', '27': 'Eure', '28': 'Eure-et-Loir', '29': 'Finistère',
    '30': 'Gard', '31': 'Haute-Garonne', '32': 'Gers', '33': 'Gironde',
    '34': 'Hérault', '35': 'Ille-et-Vilaine', '36': 'Indre', '37': 'Indre-et-Loire',
    '38': 'Isère', '39': 'Jura', '40': 'Landes', '41': 'Loir-et-Cher',
    '42': 'Loire', '43': 'Haute-Loire', '44': 'Loire-Atlantique', '45': 'Loiret',
    '46': 'Lot', '47': 'Lot-et-Garonne', '48': 'Lozère', '49': 'Maine-et-Loire',
    '50': 'Manche', '51': 'Marne', '52': 'Haute-Marne', '53': 'Mayenne',
    '54': 'Meurthe-et-Moselle', '55': 'Meuse', '56': 'Morbihan', '57': 'Moselle',
    '58': 'Nièvre', '59': 'Nord', '60': 'Oise', '61': 'Orne', '62': 'Pas-de-Calais',
    '63': 'Puy-de-Dôme', '64': 'Pyrénées-Atlantiques', '65': 'Hautes-Pyrénées',
    '66': 'Pyrénées-Orientales', '67': 'Bas-Rhin', '68': 'Haut-Rhin', '69': 'Rhône',
    '70': 'Haute-Saône', '71': 'Saône-et-Loire', '72': 'Sarthe', '73': 'Savoie',
    '74': 'Haute-Savoie', '75': 'Paris', '76': 'Seine-Maritime', '77': 'Seine-et-Marne',
    '78': 'Yvelines', '79': 'Deux-Sèvres', '80': 'Somme', '81': 'Tarn',
    '82': 'Tarn-et-Garonne', '83': 'Var', '84': 'Vaucluse', '85': 'Vendée',
    '86': 'Vienne', '87': 'Haute-Vienne', '88': 'Vosges', '89': 'Yonne',
    '90': 'Territoire de Belfort', '91': 'Essonne', '92': 'Hauts-de-Seine',
    '93': 'Seine-Saint-Denis', '94': 'Val-de-Marne', '95': "Val-d'Oise",
}


def normaliser(t):
    t = ''.join(c for c in unicodedata.normalize('NFD', (t or '').lower())
                if unicodedata.category(c) != 'Mn')
    # on retire les formes juridiques, qui ne figurent jamais dans OSM
    t = re.sub(r'\b(sarl|sas|sasu|sa|eurl|snc|scop|ets|etablissements?)\b', ' ', t)
    return re.sub(r'[^a-z0-9]+', ' ', t).strip()


def mots_significatifs(nom):
    return {m for m in normaliser(nom).split() if len(m) >= 4}


def interroger(dept):
    nom = DEPTS.get(str(dept).zfill(2))
    if not nom:
        return []
    q = f'''[out:json][timeout:180];
area["name"="{nom}"]["admin_level"="6"]->.a;
(
  nwr(area.a)["office"];
  nwr(area.a)["shop"="trade"];
  nwr(area.a)["shop"="hardware"];
  nwr(area.a)["craft"];
  nwr(area.a)["industrial"];
  nwr(area.a)["landuse"="industrial"]["name"];
);
out center tags;'''
    r = subprocess.run(['curl', '-s', '-m', '240', '-X', 'POST', '-d', q, OVERPASS],
                       capture_output=True)
    try:
        return json.loads(r.stdout).get('elements', [])
    except Exception:
        return []


def apparier(fiches, elements):
    """Rapproche par mots significatifs du nom + proximité de commune."""
    index = []
    for e in elements:
        t = e.get('tags') or {}
        if not t.get('name'):
            continue
        index.append((mots_significatifs(t['name']), t, e))

    trouves = 0
    for f in fiches:
        cible = mots_significatifs(f['nom'])
        if not cible:
            continue
        meilleur, score_max = None, 0
        for mots, tags, e in index:
            if not mots:
                continue
            commun = cible & mots
            # au moins deux mots communs, ou un seul mais très distinctif
            score = len(commun)
            if score and (score >= 2 or max(len(m) for m in commun) >= 7):
                ville_ok = normaliser(tags.get('addr:city', '')) == normaliser(f.get('commune', ''))
                score += 1 if ville_ok else 0
                if score > score_max:
                    meilleur, score_max = tags, score
        if meilleur:
            f['osm'] = {
                'telephone': meilleur.get('phone') or meilleur.get('contact:phone'),
                'site_web': meilleur.get('website') or meilleur.get('contact:website'),
                'horaires': meilleur.get('opening_hours'),
                'email': meilleur.get('email') or meilleur.get('contact:email'),
            }
            f['osm'] = {k: v for k, v in f['osm'].items() if v}
            if f['osm']:
                trouves += 1
            else:
                del f['osm']
    return trouves


def traiter(dept):
    d = str(dept).zfill(2)
    chemin = os.path.join(SRC, f'dept-{d}.json')
    if not os.path.exists(chemin):
        print(f"dept {d} : pas de collecte SIRENE, ignoré")
        return 0
    with open(chemin, encoding='utf-8') as f:
        fiches = json.load(f)

    elements = interroger(d)
    n = apparier(fiches, elements)
    with open(os.path.join(OUT, f'dept-{d}.json'), 'w', encoding='utf-8') as f:
        json.dump(fiches, f, ensure_ascii=False, indent=1)

    tel = sum(1 for f in fiches if (f.get('osm') or {}).get('telephone'))
    print(f"dept {d} : {len(elements):5d} objets OSM · {n} fiches enrichies "
          f"({tel} avec téléphone) sur {len(fiches)}", flush=True)
    return n


def main():
    args = [a for a in sys.argv[1:] if not a.startswith('--')]
    if '--tous' in sys.argv:
        args = sorted(f[5:-5] for f in os.listdir(SRC)
                      if f.startswith('dept-') and f.endswith('.json'))
    if not args:
        print(__doc__)
        return 1
    total = 0
    for d in args:
        total += traiter(d)
        time.sleep(4)  # Overpass est un service public : on l'épargne
    print(f"\nTotal enrichi par OSM : {total}")
    return 0


if __name__ == '__main__':
    sys.exit(main())
