# Arborescence et plan de nommage d'URL — infoweb-manutention.fr

Date : 2026-07-24. Découle de l'analyse (docs/analyse-critique-brief.md) et des décisions actées : phase 0 immédiate, chariots+levage en parallèle, annuaire par VGP/SAV, pas d'occasion.

## Principe directeur

Deux régimes d'URL coexistent :

1. **URLs héritées** : toute page de `data/redirections-exactes.csv` marquée RECREATE est recréée à l'**URL historique exacte**, même si le slug est laid (`/chariotelevateur-heli/`, `/gerbeur/le-gerbeur-fenwick-tout-savoir-sur-cet-appareil/`). On ne déplace jamais une URL qui ranke. Consolidations (301) uniquement quand plusieurs URLs se cannibalisent sur la même intention.
2. **URLs nouvelles** : suivent le plan de nommage ci-dessous.

Règle de trailing slash : partout. Règle de casse : minuscules, tirets. Pas d'accents ni d'apostrophes dans les slugs.

## Étage 1 — Univers (5 pages piliers)

```
/chariot-elevateur/          Chariots élévateurs (cluster historique le plus fort)
/levage/                     Levage (palans, treuils, aimants, ponts roulants…)
/manutention/                Manutention manuelle et roulante
/nacelle/                    Nacelles élévatrices (fusion de /nacelle/ et /nacelles-elevatrices/ hérités)
/stockage/                   Stockage et entrepôt
```

Ouverture : chariot-elevateur + levage (phase 0-1) → manutention (phase 1) → nacelle (phase 2) → stockage (phase 2).

## Étage 2 — Familles (guides d'achat, monétisation)

```
/chariot-elevateur/electrique/            [affil. non — devis]
/chariot-elevateur/diesel/                [devis]
/chariot-elevateur/gaz/                   [devis]   ← cible 301 de /categorie/chariot-elevateur-gaz/ et /produit/chariot-elevateur-a-gaz-*
/chariot-elevateur/retractable/           [devis]
/chariot-elevateur/telescopique/          [devis]

/levage/palan/                            [affiliation]  ← cible 301 des /produit/palan-* et /treuil-palonnier/*
/levage/treuil/                           [affiliation]  ← cible 301 des /produit/treuil-*
/levage/aimant-de-levage/                 [affiliation]  ← cible 301 de /categorie/aimant-de-levage/ + /produit/*aimant*/*magnetique*
/levage/pont-roulant/                     [devis]        ← cible 301 des articles pont-roulant redondants
/levage/potence/                          [devis]
/levage/table-elevatrice/                 [affiliation petites / devis grosses]  ← /table-elevatrice/ hérité recréé puis 301 progressif
/levage/monte-charge/                     [devis]
/levage/chariot-porte-palan/              [affiliation]  ← /chariot-porte-palan/ hérité recréé tel quel, maillé ici

/manutention/transpalette/                [affiliation]  ← consolidation des /transpalette/* hérités (guides marques/prix conservés en satellites)
/manutention/transpalette-electrique/     [affiliation]
/manutention/gerbeur/                     [affiliation]  ← le satellite hérité /gerbeur/le-gerbeur-fenwick-… reste tel quel et maille ici
/manutention/diable/                      [affiliation]  ← cible 301 de /manutention/chariot-diable/ ET /diable-chariot/* (doublons hérités)
/manutention/chariot/                     [affiliation]  (chariots de manutention, rolls)
/manutention/roll-conteneur/              [affiliation]  ← /stockage/roll-conteneur/ hérité recréé, 301 vers ici en phase 2

/nacelle/ciseaux/                         [devis location]
/nacelle/articulee/                       [devis location]
/nacelle/telescopique/                    [devis location]
/nacelle/araignee/                        [devis location]
/nacelle/mat-vertical/                    [devis location]

/stockage/rayonnage/                      [affiliation]
/stockage/palettier/                      [devis + affiliation]
/stockage/mezzanine/                      [devis]
/stockage/abri/                           [devis]
```

Monétisation par page : UNE seule logique par page (règle du brief conservée). Familles affiliation = petit matériel ; familles devis = formulaire lead.

## Étage 3 — Satellites

`/{univers}/{slug}/` — comme l'existant. Les satellites hérités qui rankent gardent leur URL. Nouveaux satellites : slug court orienté requête (`/chariot-elevateur/norme-passage/`… mais on ne renomme PAS les hérités).

## Silos transverses

```
/reglementation/                          pilier (hub CACES + VGP + obligations)
/reglementation/caces/                    guide global   ← /caces-chariot-elevateur-guide-complet/ hérité recréé tel quel, maillé ici
/reglementation/caces-r489/               chariots       (+ r486 nacelles, r485 gerbeurs, r484 ponts roulants, r482 engins chantier)
/reglementation/vgp/                      hub VGP        → maille vers /sav/{machine}/{departement}/
/reglementation/autorisation-de-conduite/
/reglementation/obligations-employeur/
/reglementation/aptitude-medicale/

/prix/                                    pilier coûts (intention d'achat)
/prix/chariot-elevateur/                  ← cible 301 de /chariot-elevateur/quel-prix-pour-un-prix-chariot-elevateur-neuf-noatre-avis/
/prix/location-nacelle/
/prix/location-chariot-elevateur/
/prix/transpalette/                       ← cible 301 de /transpalette/prix-des-transpalettes-guide-des-tarifs-complet/ (phase 1, pas phase 0)
/prix/vgp/
/prix/caces/

/marque/                                  hub marques
/marque/{marque}/                         fenwick, toyota, linde, jungheinrich, still, crown, hyster, manitou, haulotte, genie, jlg, heli…
                                          ← /chariotelevateur-heli/ hérité recréé tel quel puis canonique vers /marque/heli/ en phase 2
                                          ← /fabricants-chariots-elevateurs/ 301 → /chariot-elevateur/classement-des-marques-chariot-elevateur/ (hérité conservé)
```

## Annuaire

```
/annuaire/                                          hub recherche + carte
/entreprise/{ville}-{slug}/                         fiche établissement (pattern hérité conservé)
/sav/{machine}/{departement}/                       vague 1 — réparateurs + organismes VGP   (croisement vide en SERP)
/location/{machine}/{departement}/                  vague 2 — loueurs
/concessionnaire/{marque}/{departement}/            vague 3 — distributeurs par marque
/formation-caces/{recommandation}/{departement}/    vague 4 — seulement si donnée différenciante (prix/dates réels)
```

`{machine}` = slug famille (chariot-elevateur, nacelle, transpalette, gerbeur, pont-roulant…). `{departement}` = `{nom}-{code}` (ex. `moselle-57`). Règles anti-thin du brief appliquées : ≥ 3 établissements sinon noindex + lien vers niveau supérieur, bloc local unique obligatoire, sitemaps segmentés.

## Outils et assets linkables

```
/outils/capacite-residuelle/              calculateur capacité résiduelle chariot (asset lien #1)
/outils/selecteur-caces/                  quel engin → quelle recommandation/catégorie
/outils/tco/                              comparateur achat / financement / LLD / crédit-bail (outil de conversion)
/barometre/                               baromètre annuel des prix de la manutention (asset lien #2, phase 2+)
```

## Pages système

```
/a-propos/            page auteur/éditeur (E-E-A-T, requis pour vente d'espace éditorial)
/contact/
/mentions-legales/    Anthony Russo, SIRET 98497752000019, contact@infoweb-manutention.fr, OVH
/confidentialite/     RGPD (leads : consentement, conservation, suppression)
/transparence/        mention liens affiliés + méthodologie prix
/devis/               formulaire devis générique (les formulaires contextuels des pages familles postent sur la même brique)
/go/{slug}            redirections affiliées trackées (noindex, disallow robots.txt, rel="sponsored" sur les liens)
```

## Maillage (règles du brief conservées, complétées)

- Satellite → famille (ancre exacte), famille → univers, univers ↔ univers.
- Annuaire croisement → famille correspondante et inversement (`/location/nacelle/moselle-57/` ↔ `/nacelle/ciseaux/`).
- `/reglementation/vgp/` → `/sav/{machine}/{dept}/` (le contenu réglementaire alimente l'annuaire en jus et en contexte).
- `/prix/*` → familles + formulaires devis ; jamais d'affiliation ET de devis sur la même page.
- Outils → familles selon résultat (TCO : location gagnante → formulaire loueur ; achat gagnant → affiliation ou devis concessionnaire).
```
