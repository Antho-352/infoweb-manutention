# Thème — administration et exploitation au quotidien

Comment le site se pilote une fois construit. Principe unique et non négociable : **le plus natif possible**. Chaque écran ajouté est un écran à maintenir, à documenter et à réapprendre dans six mois. WordPress sait déjà faire l'essentiel — on ne le réécrit pas.

---

## 1. Les réglages SEO par page

### Ce qu'on ne fait pas

Pas de Yoast, pas de Rank Math. Ces extensions apportent des centaines de réglages dont on utilisera cinq, ralentissent l'administration, et rendent le site dépendant d'un tiers pour ses balises les plus critiques.

### Ce qu'on fait

Une **métabox unique** sous l'éditeur, trois champs, rien d'autre :

| Champ | Comportement si vide |
|---|---|
| **Titre SEO** | Reprend le titre de la page, suffixé du nom du site |
| **Méta description** | Reprend le chapô, tronqué proprement à 160 caractères |
| **Indexation** | Indexée par défaut ; case à cocher pour passer en `noindex` |

Chaque champ affiche son compteur de caractères avec la limite recommandée (60 pour le titre, 160 pour la description) et un aperçu du rendu en résultat de recherche. C'est le seul confort qui compte réellement.

Le reste est automatique et n'a pas à être réglé page par page : URL canonique, balises Open Graph, fil d'Ariane, `BreadcrumbList`, `Article`, `FAQPage`, `LocalBusiness`, sitemaps. Le thème les génère à partir de ce qu'il sait déjà de la page.

Stockage : trois `post_meta`. Aucune table, aucune dépendance. Si le thème disparaît un jour, les données restent lisibles.

---

## 2. Le menu et le pied de page

**Menus natifs de WordPress**, sans exception. Apparence → Menus, deux emplacements déclarés : `principal` et `pied`. Glisser-déposer, sous-menus, tout est déjà là et Anthony le connaît.

Le seul ajout : la navigation principale accepte une **description courte par entrée** (champ natif de WordPress, à activer dans les options d'écran) qui s'affiche sous l'intitulé dans le déroulant *Équipements*. Zéro code spécifique.

Le pied de page a une zone de widgets classique. Les mentions légales, la confidentialité et la transparence sont des pages ordinaires — pas des réglages du thème.

---

## 3. Les données de formulaire

### Où elles sont stockées

Une table dédiée, `arw_leads` (schéma dans `docs/schema-bdd.sql`). Pas dans `wp_postmeta`, pas dans un CPT : une demande de devis n'est pas un article, et les stocker en articles fait gonfler les tables de WordPress pour rien.

### L'écran d'administration

Une entrée de menu **Demandes**, avec une liste standard WordPress (`WP_List_Table`) — donc le tri, la pagination, la recherche et les actions groupées sont ceux que WordPress fournit déjà. Colonnes : date, type, machine, département, entreprise, statut, partenaire.

Le statut se change directement dans la liste : nouveau → transmis → qualifié → signé → perdu. Pas de fiche à ouvrir pour un changement d'état.

### L'export

Bouton **Exporter en CSV** au-dessus de la liste, qui exporte exactement ce que les filtres affichent. Séparateur point-virgule et encodage UTF-8 avec BOM, pour que le fichier s'ouvre directement dans Excel en français sans manipulation.

### Ce qui part par e-mail

Notification immédiate à chaque demande, avec le contenu complet et un lien direct vers la fiche. `wp_mail`, rien de plus.

### RGPD

Consentement horodaté à l'enregistrement, durée de conservation inscrite dans la table, et une tâche planifiée quotidienne qui purge ce qui a dépassé l'échéance. Les demandes signées sont anonymisées plutôt que supprimées, pour garder l'historique commercial sans garder les personnes.

---

## 4. L'annuaire — import et mise à jour

C'est le seul endroit où il faut sortir du natif, parce que WordPress n'a rien pour gérer des dizaines de milliers d'établissements. Le reste du principe tient quand même : **un seul écran, trois actions**.

### Écran unique : Annuaire

**Action 1 — Importer.** On dépose le fichier JSON produit par `scripts/collecte_annuaire.py`, éventuellement enrichi par `scripts/enrichir_gmb.py`. L'import est un *upsert* sur le SIRET : un établissement déjà connu est mis à jour, jamais dupliqué. L'écran affiche, avant d'écrire quoi que ce soit, un récapitulatif — combien de nouveaux, combien de mis à jour, combien d'ignorés et pourquoi.

**Action 2 — Modérer.** La file de revue, triée par confiance croissante : les cas douteux en premier. Trois boutons par ligne — publier, écarter, corriger. La catégorie Google sert de colonne de décision : c'est elle qui tranche les raisons sociales ambiguës.

**Action 3 — Publier un lot.** Sélectionne les 100 pages de croisement les plus denses qui remplissent les conditions (au moins trois établissements et un bloc de contenu local rédigé), les passe en publié et les inscrit au sitemap. Un compteur rappelle le taux d'indexation du lot précédent — en dessous de 70 %, l'écran refuse de lancer le suivant et dit pourquoi.

### La mise à jour dans le temps

La collecte se relance quand on veut ; l'upsert par SIRET fait que rien ne se duplique. Les établissements fermés — l'état administratif passe à `F` dans les données officielles — sont dépubliés automatiquement, avec leur page en 410.

Les fiches revendiquées par leur propriétaire sont **verrouillées** : un ré-import ne les écrase jamais. C'est ce qui rend la revendication crédible auprès d'un gérant.

---

## 5. Ce qui n'existera pas

Pas de constructeur de pages. Pas de panneau d'options du thème avec cinquante réglages. Pas de sélecteur de couleurs, de police ou de mise en page — ces choix sont faits, ils sont dans le code, et les rouvrir c'est rouvrir le débat à chaque publication.

Pas de champs personnalisés à remplir pour qu'un article s'affiche correctement. Un article rempli avec l'éditeur de WordPress, catégorisé, doit s'afficher juste. Si un gabarit exige qu'on remplisse trois champs annexes pour être présentable, c'est le gabarit qui est mal conçu.

---

## 6. Questions à trancher

1. **Fiches établissement : pages réelles ou pages virtuelles ?** Les stocker en table custom et les servir par règle de réécriture est plus léger et plus rapide, mais elles échappent alors à l'éditeur de WordPress. L'alternative — un article par établissement — serait éditable mais ferait gonfler les tables de WordPress de plusieurs dizaines de milliers de lignes. Je penche pour la table custom, avec un écran d'édition dédié minimal pour les fiches revendiquées.

2. **Multi-utilisateurs ?** Si des professionnels revendiquent leur fiche, faut-il leur créer un compte WordPress avec un rôle restreint, ou passer par des liens signés à durée limitée envoyés par e-mail ? Les liens signés évitent d'ouvrir les comptes sur le site — c'est plus simple et plus sûr, mais moins confortable pour un gérant qui modifie souvent.

3. **Sauvegarde des tables `arw_*`** : elles ne sont pas dans les exports natifs de WordPress. Faut-il une exportation planifiée vers un fichier, ou la sauvegarde du serveur suffit-elle ?
