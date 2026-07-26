#!/usr/bin/env python3
"""Lint des contenus réglementaires et tarifaires avant publication.

Détecte les modes de défaillance connus des agents rédacteurs :
  - affirmation réglementaire sans référence de source à proximité
  - erreur classique "le CACES est obligatoire"
  - périodicité VGP approximative
  - prix écrit en dur dans la prose (doit venir de la table arw_prix)
  - prix ou fourchette sans date de constat
  - marqueurs [À VÉRIFIER] restants
  - références d'articles du Code du travail hors plage plausible

Usage :
    python3 scripts/lint_reglementaire.py content/pages/*.md
    python3 scripts/lint_reglementaire.py --strict content/pages/vgp.md

Sortie : une ligne par problème. Code retour 1 si au moins une ERREUR.
"""
import argparse
import re
import sys

# Motifs indiquant qu'une source est citée à proximité
SOURCE = re.compile(
    r'(R\s?4323-\d+|arrêté du \d{1,2}\w* \w+ \d{4}|R\.?\s?4[89]\d\b|R\.?\s?48[2-7]\b|'
    r'légifrance|ameli\.fr|inrs\.fr|code du travail, art)',
    re.I)

ERREURS = [
    (re.compile(r'\ble CACES\b[^.]{0,60}\b(est|sont)\s+obligatoire', re.I),
     "Le CACES n'est PAS obligatoire — c'est l'autorisation de conduite qui l'est (R4323-56)."),
    # On ne doit pas déclencher sur la forme négative, qui est la bonne :
    # « le CACES n'est pas une obligation légale » est exactement ce qu'on veut lire.
    (re.compile(r"\bCACES\b(?![^.]{0,40}\bn['e]\w*\s+(?:est|constitue)\s+pas)"
                r"[^.]{0,40}\b(?:est|constitue)\s+une\s+obligation\s+légale\b", re.I),
     "Le CACES n'est pas une obligation légale."),
    (re.compile(r'\[À VÉRIFIER\]|\[A VERIFIER\]', re.I),
     "Marqueur [À VÉRIFIER] non résolu — publication interdite."),
    (re.compile(r'\b(environ|à peu près|généralement|en principe)\s+tous les\s+\d+\s*(mois|ans)', re.I),
     "Périodicité approximative — donner la périodicité exacte avec sa référence."),
    (re.compile(r'\bnous vous conseillons juridiquement|notre conseil juridique\b', re.I),
     "Formulation de conseil juridique interdite."),
]

AVERTISSEMENTS = [
    (re.compile(r'\b(la loi|la réglementation|le code du travail)\s+(impose|oblige|exige|prévoit)', re.I),
     "Affirmation réglementaire — vérifier qu'une référence d'article est citée à proximité."),
    (re.compile(r'\b(VGP|vérification générale périodique)\b', re.I),
     "Mention VGP — la périodicité doit citer l'arrêté du 1er mars 2004."),
]

# Vérifications factuelles : ne dépendent pas de la présence d'une source voisine
AVERTISSEMENTS_INCONDITIONNELS = [
    (re.compile(r'\bR\.?\s?389\b', re.I),
     "R389 mentionnée : recommandation abrogée depuis 2020. Préciser explicitement "
     "le remplacement par la R489 et l'absence de correspondance directe entre catégories."),
]

# Prix en dur dans la prose : "12 000 €", "de 3 000 à 8 000 euros"
PRIX = re.compile(r'\b\d[\d\s.,]{2,}\s?(€|euros?)\b', re.I)
# Un bloc de prix injecté depuis la base est balisé ainsi
BLOC_PRIX = re.compile(r'\[arw_prix\b|\{\{\s*prix[:.]|<!--\s*arw:prix', re.I)
DATE_CONSTAT = re.compile(
    r'(constat[ée]e?\s+(le|en)|relev[ée]e?\s+(le|en)|à jour au|au \d{1,2}/\d{4}|en \d{4})', re.I)

# Articles du Code du travail plausibles pour notre périmètre
ART_CT = re.compile(r'\bR\s?\.?\s?4323-(\d+)\b')
ART_PLAGE = (1, 106)


def lint(path, strict=False):
    try:
        with open(path, encoding='utf-8') as f:
            lignes = f.read().splitlines()
    except OSError as e:
        print(f"{path}: IMPOSSIBLE DE LIRE — {e}")
        return 1, 0

    n_err = n_warn = 0
    a_bloc_prix = any(BLOC_PRIX.search(l) for l in lignes)

    for i, ligne in enumerate(lignes, 1):
        for motif, message in ERREURS:
            if motif.search(ligne):
                print(f"{path}:{i}: ERREUR — {message}")
                n_err += 1

        for motif, message in AVERTISSEMENTS:
            if motif.search(ligne):
                # une source citée sur la ligne ou la suivante lève l'avertissement
                voisinage = ' '.join(lignes[max(0, i - 2):i + 2])
                if not SOURCE.search(voisinage):
                    print(f"{path}:{i}: ATTENTION — {message}")
                    n_warn += 1

        for motif, message in AVERTISSEMENTS_INCONDITIONNELS:
            if motif.search(ligne):
                print(f"{path}:{i}: ATTENTION — {message}")
                n_warn += 1

        for m in ART_CT.finditer(ligne):
            num = int(m.group(1))
            if not (ART_PLAGE[0] <= num <= ART_PLAGE[1]):
                print(f"{path}:{i}: ERREUR — R4323-{num} hors plage plausible "
                      f"({ART_PLAGE[0]}-{ART_PLAGE[1]}) : référence probablement inventée.")
                n_err += 1

        if PRIX.search(ligne) and not BLOC_PRIX.search(ligne):
            niveau = "ERREUR" if strict else "ATTENTION"
            print(f"{path}:{i}: {niveau} — prix écrit en dur dans la prose ; "
                  f"il doit provenir de la table arw_prix via un bloc dédié.")
            if strict:
                n_err += 1
            else:
                n_warn += 1

    if a_bloc_prix and not any(DATE_CONSTAT.search(l) for l in lignes):
        print(f"{path}: ERREUR — bloc de prix sans date de constat visible.")
        n_err += 1

    return n_err, n_warn


def main():
    p = argparse.ArgumentParser(description=__doc__,
                                formatter_class=argparse.RawDescriptionHelpFormatter)
    p.add_argument('fichiers', nargs='+')
    p.add_argument('--strict', action='store_true',
                   help="Transforme les prix en dur en erreurs bloquantes.")
    args = p.parse_args()

    total_err = total_warn = 0
    for f in args.fichiers:
        e, w = lint(f, args.strict)
        total_err += e
        total_warn += w

    print(f"\n{len(args.fichiers)} fichier(s) — {total_err} erreur(s), {total_warn} avertissement(s)")
    if total_err:
        print("PUBLICATION BLOQUÉE tant que les erreurs subsistent.")
    return 1 if total_err else 0


if __name__ == '__main__':
    sys.exit(main())
