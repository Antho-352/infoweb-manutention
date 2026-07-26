#!/usr/bin/env python3
"""Curation de l'annuaire des prestataires manutention/levage.

Classe les établissements collectés en trois catégories : retenu, a_trancher, ecarte.

Ajoute un champ `motif` qui explique la décision en une phrase.

Usage :
    python3 scripts/curation_annuaire.py 57            # un département
    python3 scripts/curation_annuaire.py 57 54 88 67   # plusieurs
    python3 scripts/curation_annuaire.py --tous        # les 101
"""
import json
import os
import re
import sys
import unicodedata


BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
IN_DIR = os.path.join(BASE, 'data', 'annuaire')
OUT_DIR = os.path.join(BASE, 'data', 'annuaire', 'cure')
os.makedirs(OUT_DIR, exist_ok=True)


def sans_accents(t):
    """Normalise un texte : minuscules, pas d'accents."""
    return ''.join(c for c in unicodedata.normalize('NFD', (t or '').lower())
                   if unicodedata.category(c) != 'Mn')


# Mots-clés qui signalent clairement une activité de manutention/levage.
MOTS_POSITIFS_FORTS = {
    'manutention', 'chariot', 'gerbeur', 'transpalette', 'elevateur',
    'elevatrice', 'nacelle', 'palan', 'treuil', 'pont roulant', 'caces',
    'cariste', 'rayonnage', 'palettis', 'levage'
}

# Mots-clés ambigus ou faibles : peuvent indiquer manutention, mais pas garanti.
MOTS_POSITIFS_FAIBLES = {
    'logistique', 'intralogistique', 'hydraulique', 'stockage'
}

# Mots-clés qui désignent clairement un secteur d'activité HORS manutention.
MOTS_NEGATIFS = {
    'transport routier', 'commission', 'affrètement', 'demenagement',
    'deménagement', 'nettoyage', 'proprete', 'propreté', 'recyclage',
    'terrassement', 'dragage', 'travaux publics', 'btp', 'tp services',
    'frigorifique', 'frigo transport', 'utilitaires', 'vehicules',
    'véhicules', 'evenementiel', 'événementiel', 'medical', 'médical',
    'agricole', 'batiment', 'bâtiment', 'ascenseur', 'kone', 'immeubles',
    'diagnostique', 'radiologie', 'scan', 'irm'
}

# Marques/modèles de matériel de manutention : signal fort.
MARQUES_MANUTENTION = {
    'fenwick', 'linde', 'still', 'hyster', 'yale', 'crown', 'toyota',
    'jungheinrich', 'manitou', 'haulotte', 'jlg'
}

# Noms complets qui doivent être écartés indépendamment du NAF/mots-clés.
ENTREPRISES_EXCLUES = {
    'kone',  # Ascenseurs, pas manutention
}


def normalise_text(text):
    """Normalise un texte pour la recherche : minuscules, pas d'accents."""
    if not text:
        return ''
    return sans_accents(text).strip()


def contains_negative_keywords(nom, enseignes):
    """Retourne True si le nom ou une enseigne contient un mot-clé négatif."""
    hay = ' '.join([nom] + (enseignes or [])).lower()
    hay = normalise_text(hay)
    for kw in MOTS_NEGATIFS:
        if kw in hay:
            return kw
    return None


def contains_positive_strong_keywords(nom, enseignes, mots_cles):
    """Retourne la liste des mots-clés positifs forts trouvés."""
    combined = ' '.join([nom] + (enseignes or []) + (mots_cles or []))
    hay = normalise_text(combined)
    found = []
    for kw in MOTS_POSITIFS_FORTS:
        if kw in hay:
            found.append(kw)
    return found


def contains_positive_weak_keywords(nom, enseignes, mots_cles):
    """Retourne la liste des mots-clés positifs faibles trouvés."""
    combined = ' '.join([nom] + (enseignes or []) + (mots_cles or []))
    hay = normalise_text(combined)
    found = []
    for kw in MOTS_POSITIFS_FAIBLES:
        if kw in hay:
            found.append(kw)
    return found


def contains_brand(nom, enseignes):
    """Retourne True si nom/enseignes contiennent une marque manutention."""
    combined = ' '.join([nom] + (enseignes or []))
    hay = normalise_text(combined)
    for brand in MARQUES_MANUTENTION:
        if brand in hay:
            return brand
    return None


def is_explicitly_excluded(nom):
    """Retourne True si l'entreprise doit être systématiquement exclue."""
    hay = normalise_text(nom)
    for exc in ENTREPRISES_EXCLUES:
        if exc in hay:
            return exc
    return None


def curate(fiche):
    """
    Classe une fiche en (categorie, motif).
    Catégories : 'retenu', 'a_trancher', 'ecarte'.
    """
    nom = fiche.get('nom', '').strip()
    enseignes = fiche.get('enseignes') or []
    naf = fiche.get('naf', '')
    activites = fiche.get('activites') or []
    mots_cles = fiche.get('mots_cles') or []
    confiance = fiche.get('confiance', '')
    date_creation = fiche.get('date_creation', '')

    # Étape 1 : Exclusions explicites
    exc = is_explicitly_excluded(nom)
    if exc:
        return 'ecarte', f"Entreprise explicitement exclue ({exc})"

    # Étape 2 : Vérifier les mots-clés négatifs
    neg_kw = contains_negative_keywords(nom, enseignes)
    if neg_kw:
        return 'ecarte', f"Contient un mot-clé d'exclusion : {neg_kw}"

    # Étape 3 : Vérifier les mots-clés positifs forts
    pos_strong = contains_positive_strong_keywords(nom, enseignes, mots_cles)
    if pos_strong:
        return 'retenu', f"Contient mot(s)-clé(s) fort(s) : {', '.join(pos_strong)}"

    # Étape 4 : Vérifier les marques de manutention
    brand = contains_brand(nom, enseignes)
    if brand:
        return 'retenu', f"Marque manutention identifiée : {brand}"

    # Étape 5 : Traiter les NAF "stricts" (qui suffisent à qualifier)
    # 77.32Z = location machines construction
    # 28.22Z = fabrication équipements levage/manutention
    if naf in ('77.32Z', '28.22Z'):
        # Si confiance haute et pas de doute, retenir.
        if confiance == 'haute':
            return 'retenu', f"NAF strict ({naf}) avec confiance haute"
        # Sinon, attention : 77.32Z peut aussi être terrassement, dragage, etc.
        # Si le nom est trop vague et pas d'autre signal, à trancher.
        if nom and len(nom) > 3 and not any(c.isalpha() for c in nom[0]):
            # Nom commence par un signe → très probablement générique. À trancher.
            return 'a_trancher', f"NAF {naf} (loueur machines) mais nom trop vague"
        # Si aucun mot-clé faible non plus, à trancher par défaut.
        pos_weak = contains_positive_weak_keywords(nom, enseignes, mots_cles)
        if pos_weak:
            return 'a_trancher', f"NAF {naf} avec mot-clé(s) faible(s) : {', '.join(pos_weak)}"
        return 'a_trancher', f"NAF {naf} (loueur) : incertitude sur la spécialité"

    # Étape 6 : Autres NAF spécifiques
    # 46.69B = commerce gros fournitures industrielles + mot-clé manutention
    if naf == '46.69B':
        pos_weak = contains_positive_weak_keywords(nom, enseignes, mots_cles)
        if 'manutention' in mots_cles or pos_strong:
            return 'retenu', f"Concessionnaire manutention (NAF 46.69B + mot-clé)"
        if pos_weak:
            return 'a_trancher', f"NAF 46.69B (gros industriel) + mots faibles : {', '.join(pos_weak)}"
        return 'a_trancher', f"NAF 46.69B : trop générique sans mot-clé métier"

    # 33.12Z = réparation machines
    # Généralement bon signal si "hydraulique" → réparation d'appareils de levage
    if naf == '33.12Z':
        if 'hydraulique' in mots_cles or 'levage' in mots_cles or 'hydraulique' in activites:
            return 'retenu', f"Réparation machines (NAF 33.12Z) + hydraulique ou levage"
        if mots_cles:
            return 'a_trancher', f"Réparation machines (NAF 33.12Z) : domaine à confirmer"
        return 'a_trancher', f"Réparation machines (NAF 33.12Z) : ambigüe sans mots-clés"

    # 71.20B = analyses, essais, inspections techniques
    # Peut être organisme VGP (vérification générale périodique) → retenir
    # Mais peut aussi être autre chose → à trancher
    if naf == '71.20B':
        if 'vgp' in normalise_text(nom) or 'verif' in normalise_text(nom):
            return 'retenu', f"Organisme inspection/VGP (NAF 71.20B)"
        if 'levage' in mots_cles or 'levage' in normalise_text(nom):
            return 'retenu', f"Inspection appareils levage (NAF 71.20B + levage)"
        return 'a_trancher', f"Analyses/essais/inspections (NAF 71.20B) : domaine à déterminer"

    # 85.59A = formation continue
    # Retenir si CACES (code autorisation conduite engins) ou levage/manutention
    if naf == '85.59A':
        if 'caces' in normalise_text(nom) or 'caces' in mots_cles:
            return 'retenu', f"Formation CACES (NAF 85.59A)"
        if 'levage' in mots_cles or 'manutention' in mots_cles:
            return 'retenu', f"Formation levage/manutention (NAF 85.59A)"
        if 'logistique' in mots_cles:
            return 'a_trancher', f"Formation logistique (NAF 85.59A) : peut être supply chain pure"
        return 'a_trancher', f"Formation continue (NAF 85.59A) : domaine à confirmer"

    # Étape 7 : Autres NAF non spécifiques
    # Si NAF n'est pas dans la liste de collecte et pas de signal fort, à trancher
    expected_nafs = {'77.32Z', '77.39Z', '46.69B', '28.22Z', '28.30Z', '28.12Z', '33.12Z',
                     '71.20B', '85.59A', '25.62B', '46.63Z', '47.79Z', '28.92Z', '33.19Z', '70.10Z'}
    if naf not in expected_nafs:
        pos_weak = contains_positive_weak_keywords(nom, enseignes, mots_cles)
        if pos_weak:
            return 'a_trancher', f"NAF inattendu ({naf}) + mots faibles : {', '.join(pos_weak)}"
        return 'ecarte', f"NAF {naf} hors périmètre manutention/levage"

    # Étape 8 : Défaut : à trancher
    # Cela s'applique à des NAF attendus mais sans signal positif fort
    return 'a_trancher', f"NAF {naf} compatible mais pas de signal positif suffisant"


def curate_dept(dept):
    """Traite un département : lit, curate, écrit."""
    dept_str = str(dept).zfill(2)
    input_file = os.path.join(IN_DIR, f'dept-{dept_str}.json')

    if not os.path.exists(input_file):
        print(f"dept {dept_str} : fichier non trouvé", flush=True)
        return None

    with open(input_file, 'r', encoding='utf-8') as f:
        fiches = json.load(f)

    # Curation
    categorised = {'retenu': [], 'a_trancher': [], 'ecarte': []}
    for fiche in fiches:
        categorie, motif = curate(fiche)
        fiche['categorie'] = categorie
        fiche['motif'] = motif
        categorised[categorie].append(fiche)

    # Écriture
    output_file = os.path.join(OUT_DIR, f'dept-{dept_str}.json')
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(fiches, f, ensure_ascii=False, indent=1)

    total = len(fiches)
    stats = {k: len(v) for k, v in categorised.items()}
    print(f"dept {dept_str} : {total:3d} établissements  "
          f"retenu={stats['retenu']:3d}  "
          f"a_trancher={stats['a_trancher']:3d}  "
          f"ecarte={stats['ecarte']:3d}",
          flush=True)

    return {
        'dept': dept_str,
        'total': total,
        'stats': stats,
        'categorised': categorised,
        'output_file': output_file
    }


def main():
    """Point d'entrée."""
    args = sys.argv[1:]
    if not args:
        print(__doc__)
        return 1

    depts = []
    if args[0] == '--tous':
        # Récupérer tous les fichiers dept-XX.json existants
        depts = []
        for fname in os.listdir(IN_DIR):
            if fname.startswith('dept-') and fname.endswith('.json'):
                dept_str = fname[5:7]
                if dept_str != 'XX':
                    depts.append(dept_str)
        depts.sort()
    else:
        depts = [str(d).zfill(2) for d in args]

    results = []
    for d in depts:
        try:
            result = curate_dept(d)
            if result:
                results.append(result)
        except Exception as e:
            print(f"dept {d} : ERREUR {e}", flush=True)

    print(f"\nRésultats écrits dans : {OUT_DIR}", flush=True)
    return 0


if __name__ == '__main__':
    sys.exit(main())
