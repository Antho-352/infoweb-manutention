# Relecture critique — brief « chariot élévateur manuel »

Relu le 2026-07-29. Brief source : `seo-cocoon/briefs/chariot-elevateur-manuel.md`.

## Note globale : 6/10 — à corriger avant rédaction (pas encore prêt)

Le diagnostic SERP est juste et le positionnement « hub qui désambiguïse » est la bonne intuition. Mais le brief a un **problème factuel de fond** (contradiction avec l'article transpalettes déjà publié), une frontière de cannibalisation qui n'est pas assez verrouillée pour le futur guide gerbeur, et une structure Hn trop lente pour une requête transactionnelle. Rien n'est bloquant à réécrire entièrement, mais 3 points sont **obligatoires** avant de lancer la rédaction.

---

## 1. Intention — 7/10

Le diagnostic est correct : Shopping dominant (VEVOR, CORMAK, Crossfer), + Amazon/Leroy Merlin/Provost/Bricoman en organique, + Kiloutou (location) + Leboncoin (occasion). Le CSV `data/bulk-mots-cles-28-07-2026.csv` confirme et affine : pour « chariot élévateur manuel », `si_transactional=true` et `si_commercial=false` — c'est plus tranché que « commerciale » : **transactionnel pur**, pas de la simple comparaison de marques. Le brief devrait dire « transactionnelle » plutôt que « commerciale/transactionnelle », ça a une incidence sur le ton (moins pédagogique, plus « aide-moi à acheter maintenant »).

Le format hub/guide d'achat est le bon choix **par défaut** faute d'alternative réaliste : vous n'êtes pas un marchand, donc un vrai comparatif produit (fiches produits, stock, achat direct) est hors de portée — seul un guide d'achat affilié a du sens. Mais attention à un point non traité par le brief : sur le top 10, 6 des 10 résultats sont des **pages produit/listing organiques** (Amazon, Leroy Merlin, Provost, Bricoman), pas seulement du Shopping Ads. Une page hub qui se contente de "router" vers vos familles ajoute un clic entre le chercheur et l'achat — ce qui est le point faible de ce format face à des pages qui vendent tout de suite. Le brief l'anticipe correctement en prévoyant des liens produits affiliés `[comparatif]` par famille sur la page elle-même : **rendez ça obligatoire dès la V1**, pas "à ajouter plus tard" — sinon la page perd sa seule vraie arme différenciante (convertir sans clic supplémentaire) face à des concurrents qui vendent en un clic.

## 2. Cannibalisation — 4/10 (le point le plus grave)

**Contradiction factuelle avec l'article transpalettes.json déjà publié.** L'article publié dit noir sur blanc :
> « Au-delà de 1,20 mètre de hauteur de levée, le besoin n'est plus un transpalette mais un gerbeur : le classement CACES change avec lui. »

Le brief, lui, traite « transpalette élévateur manuel » comme une **4ᵉ famille distincte**, à côté du gerbeur, dans un même H2 « Diable & transpalette élévateur ». C'est faux au regard de votre propre contenu publié : un « transpalette élévateur » à haute levée EST un gerbeur (ou en tout cas bascule dans cette catégorie dès qu'il lève en hauteur), pas une famille à part. Publier ce brief tel quel créerait une contradiction entre deux pages du site — mauvais pour la cohérence éditoriale et pour Google (deux pages qui se contredisent sur la même notion technique).

**Correction obligatoire** : soit vous supprimez la distinction « transpalette élévateur » comme famille séparée et vous la rattachez explicitement au gerbeur (« ce que les fiches produit appellent "transpalette élévateur" est en réalité un gerbeur — voir la famille gerbeur »), soit vous gardez un renvoi vers `transpalettes.json` en réutilisant sa propre formulation (1,20 m = bascule vers gerbeur) pour rester cohérent. Le H2 « Diable & transpalette élévateur » doit être scindé : le diable n'a rien à voir avec un transpalette élévateur (un diable bascule la charge sur essieu, ce n'est pas un appareil de levage vertical) — les regrouper dans un même H2 aggrave la confusion que la page est censée dissiper.

**Frontière avec le futur guide gerbeur (`/manutention/gerbeur/`, pas encore rédigé)** : la logique retenue par `transpalettes.json` pour la CACES (manuel = 0 formation ; porté = R.489/R.485) sera très probablement reprise dans le futur guide gerbeur pour distinguer « gerbeur manuel » (à manivelle/pédale, notre brief) de « gerbeur électrique accompagnant/porté » (le sujet de l'article Fenwick déjà publié, `content/publie/gerbeur-fenwick.txt`, qui lui ne parle QUE d'électrique haut de gamme 1-1,6 t). Le risque de doublon n'est donc pas avec l'article Fenwick (pas de recoupement, gammes et prix totalement différents), mais avec le futur guide famille gerbeur qui devra, comme celui du transpalette, expliquer ce qu'est un gerbeur manuel pour poser la base CACES. **Verrouillez la frontière dans le brief** : le hub ne doit **jamais mentionner le CACES** (aucune obligation ni référentiel R.485/R.489) — ce terrain appartient au Silo B et au futur guide famille gerbeur. Le hub se limite à « appareil / capacité / hauteur / prix », zéro mot réglementaire. Ajoutez cette interdiction explicite dans le brief (elle n'y est pas actuellement).

**Règle du cocon (`docs/cocon-silos.md` §2, Silo A)** : les money pages ne sont prévues que pour le « petit matériel affiliable » (transpalette, gerbeur, diable, palan, rayonnage, table élévatrice) — chaque famille a SA propre money page. Ce hub, en construisant lui-même un tableau de choix + prix par famille, empiète en partie sur ce que les futures money pages gerbeur et table élévatrice sont censées faire. Ce n'est pas rédhibitoire (le hub reste plus haut dans l'entonnoir, format comparatif inter-familles vs comparatif intra-famille), mais **le brief doit le dire explicitement** : la colonne prix de ce hub reste à la maille "famille" (une fourchette par type), jamais à la maille "modèle" — dès qu'on entre dans le comparatif de modèles/marques, c'est le rôle de la money page famille, pas du hub. Ajoutez cette phrase dans le brief pour cadrer le rédacteur.

Enfin, ce hub n'a pas de case dans `docs/cocon-silos.md` : ce n'est ni un pilier de famille, ni une money page, ni un pousseur — c'est un hub de désambiguïsation inter-familles sous le Pilier A. Ce n'est pas un problème en soi (bonne réponse à une requête fourre-tout), mais ça mérite d'être documenté dans `docs/cocon-silos.md` comme un type de page à part, pour ne pas créer un précédent ad hoc si d'autres requêtes fourre-tout apparaissent (ex. « chariot manutention » générique).

## 3. Ciblage mot-clé — 7/10

Principal et secondaires cohérents. Le brief affiche des volumes (536/436) qui ne correspondent pas exactement à `data/bulk-mots-cles-28-07-2026.csv` (`volumeh`=44, `volume_ads`=480 pour l'exact-match « chariot élévateur manuel ») — probablement deux sources différentes (Ahrefs vs Google Ads), à noter mais pas grave en soi ; évitez juste d'afficher deux chiffres de volume incompatibles dans le même document sans préciser la source.

Manquent : « petit chariot élévateur manuel », « chariot élévateur manuel prix », « quel chariot élévateur manuel choisir » (ce dernier calque bien l'angle "grille de choix"). Le doublet gerbeur manuel / transpalette élévateur devant être corrigé (point 2), pensez à ajuster les secondaires en conséquence une fois la frontière clarifiée.

## 4. Sémantique / structure Hn — 5/10

Pour une requête transactionnelle pure, la structure actuelle est trop lente : 3 H2 de désambiguïsation par famille (gerbeur / table élévatrice / diable-transpalette) **avant** d'arriver au tableau de choix et aux prix. Un chercheur pressé (SERP Shopping = clic rapide) doit se farcir 3 sections avant la partie qui l'intéresse vraiment.

**Suggestion** : fusionner les 3 H2 « famille » en **un seul H2** « Chariot élévateur manuel : les 3 familles qui se cachent derrière ce nom » avec un sous-bloc court par famille (2-3 phrases + lien, pas un H2 chacun), puis enchaîner immédiatement sur « Comment choisir » + le tableau prix. Cela réduit aussi mécaniquement le risque de ré-expliquer chaque famille en profondeur (point cannibalisation). Le FAQ et « Neuf ou occasion » peuvent rester en fin d'article, c'est la bonne place pour du contenu qui capte la longue traîne sans ralentir le lecteur pressé.

## 5. Format — 6/10

Tableau de choix + prix datés : format justifié pour ce type de requête (la SERP elle-même est faite de fourchettes de prix produit). Le maillage sortant vers 4 pages familles respecte la règle d'or de `docs/cocon-silos.md` (« le lecteur a-t-il besoin d'aller là ? » — oui, pour chaque famille identifiée) et n'est pas du sur-maillage (4 liens justifiés, pas un lien vers toutes les pages du silo). Attention cependant à ne pas faire pointer le lien "gerbeur" vers l'article Fenwick premium (`gerbeur-fenwick.txt`, gammes 1-1,6 t / 5 000-6 000 €) — ce serait trompeur pour un lecteur qui cherche un gerbeur manuel à 300-800 €. Le lien doit pointer vers le futur guide famille gerbeur générique (`/manutention/gerbeur/`), pas vers l'article marque. Précisez-le dans le brief pour éviter l'erreur au moment du maillage.

## 6. Métadonnées — réécriture proposée

Actuelles (vérifiées) :
- Title : 59 caractères (OK, ≤60)
- Meta : 143 caractères (OK, ≤155), mais alourdie par les guillemets typographiques autour du mot-clé et le mot-clé arrive en milieu de phrase.

Proposition, mot-clé exact en tête, angle prix/2026 conservé (cohérent avec l'intention transactionnelle) :

- **Title** : `Chariot élévateur manuel : gerbeur, diable ou transpalette ?` (61 car. — ajuster à `Chariot élévateur manuel : gerbeur, table ou diable ?` pour rester à 55 si vous voulez de la marge)
- **Meta** : `Chariot élévateur manuel : gerbeur, table élévatrice ou diable — comment choisir selon charge, hauteur et budget, avec fourchettes de prix 2026.` (147 car.)

Ceci met le mot-clé exact en position 1 (meilleur signal + meilleur CTR sur une requête à fort volume de recherche exacte) et garde la promesse « prix 2026 » qui répond à l'intention transactionnelle identifiée.

## 7. Prix + affiliation — 6/10

Donner des fourchettes datées est cohérent avec votre protocole (prix = donnée datée, pas une invention), à condition — et c'est déjà écrit dans le brief — qu'elles restent **des fourchettes par famille**, jamais des prix fermes attribués à une marque/un modèle précis relevé sur la SERP (VEVOR 248-343 €, CORMAK 440 €, Crossfer 1 440 € cités dans l'analyse SERP ne doivent PAS être recopiés tels quels dans l'article : ce serait citer les prix d'un concurrent précis à une date qui devient vite fausse, et ça ressemble à du scraping de listing plutôt qu'à une donnée éditoriale). Le brief ne le dit pas explicitement — **ajoutez une consigne noire sur blanc** : "fourchettes par famille reconstituées à partir de plusieurs sources, jamais un prix affiché copié d'un listing marchand identifiable."

Piège affiliation à éviter, confirmé par le positionnement du brief lui-même ("routeur, pas remarchande") : ne mettez PAS de bouton "Acheter" ou de widget prix en temps réel sur cette page — ça la ferait ressembler à une fiche produit e-commerce alors que vous êtes un média. Les liens `[comparatif]` vers les familles/produits affiliés doivent rester des liens texte contextuels dans le corps de l'article, pas des blocs CTA façon boutique. C'est cohérent avec "on n'est pas un marchand" mentionné dans `docs/cocon-silos.md` en tête de fichier.

---

## Corrections OBLIGATOIRES avant rédaction

1. Supprimer la 4ᵉ famille "transpalette élévateur" comme catégorie distincte du gerbeur — aligner sur `transpalettes.json` (>1,20 m de levée = gerbeur). Scinder le H2 "Diable & transpalette élévateur" en deux angles distincts (le diable n'est pas un appareil de levage vertical).
2. Interdire toute mention CACES/réglementaire dans le hub (terrain Silo B + futur guide gerbeur) — l'ajouter en toutes lettres dans le brief.
3. Préciser que le lien "gerbeur" du maillage doit pointer vers le futur guide famille générique, pas vers l'article Fenwick premium déjà publié.
4. Ajouter la consigne : prix = fourchettes par famille reconstituées, jamais un prix de listing marchand identifiable recopié tel quel.

## Suggestions (non bloquantes)

- Fusionner les 3 H2 familles en 1 seul H2 avec sous-blocs courts, remonter "Comment choisir" + "Prix" plus tôt dans la page.
- Intégrer les liens produits affiliés `[comparatif]` dès la V1 plutôt qu'en post-publication.
- Ajouter les variantes mot-clé : "chariot élévateur manuel prix", "quel chariot élévateur manuel choisir".
- Documenter ce type de page ("hub de désambiguïsation inter-familles") dans `docs/cocon-silos.md` pour cadrer de futurs cas similaires.
- Reformuler l'intention de "commerciale/transactionnelle" à "transactionnelle" (cohérent avec `si_transactional=true` / `si_commercial=false` du CSV).

## Métadonnées réécrites (à utiliser si vous suivez cette relecture)

- Title : `Chariot élévateur manuel : gerbeur, table ou diable ?` (55 car.)
- Meta : `Chariot élévateur manuel : gerbeur, table élévatrice ou diable — comment choisir selon charge, hauteur et budget, avec fourchettes de prix 2026.` (147 car.)

## Verdict

**À corriger d'abord.** Les 4 points obligatoires sont rapides à intégrer (reformulation + 2-3 phrases de cadrage), mais indispensables : publier tel quel créerait une contradiction factuelle avec l'article transpalettes déjà en ligne et ouvrirait une brèche de cannibalisation mal bornée avec le futur guide gerbeur.
