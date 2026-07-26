# Gabarits du thème — infoweb-manutention.fr

Spécification des modèles de page avant écriture du code. Découle de `strategie-media-industrie.md` (§1.6 hiérarchie par valeur business, §7 gabarits) et de l'audit du cocon de pluscestsimple, dont les six défauts deviennent ici des contraintes.

---

## 0. Les trois contraintes qui gouvernent tout

**1. Le contenu des gabarits n'est jamais copié en base.** Piliers, familles et pages système sont rendus par gabarit PHP. Modifier le fichier met à jour la page immédiatement. C'est la correction du défaut le plus coûteux constaté sur pluscestsimple, où le contenu des patterns était figé en base au premier seed et où toute correction exigeait une migration.

**2. Chaque page connaît la page qui rapporte vers laquelle elle pointe.** Ce lien est structurel, pas décoratif : il est dans le gabarit ou dans le brief éditorial, jamais laissé au hasard.

**3. Un seul appel à l'action visible par page.** Affiliation *ou* devis, jamais les deux. C'est la règle de monétisation du brief traduite en règle d'interface.

---

## 1. L'identité média — pourquoi elle passe avant le reste

Une première version de ce document traitait la couche éditoriale comme secondaire, au motif que le trafic d'articles ne convertit pas. Le raisonnement était incomplet et il est corrigé ici.

**Les articles ne convertissent pas le visiteur. Ils convertissent l'annonceur.** Un loueur qui envisage un partenariat de génération de devis, une marque qui achète un article sponsorisé, un organisme de formation qui accepte de figurer dans l'annuaire, un journaliste ou un blog qui décide de nous citer : tous jugent sur la même chose, et ce n'est ni notre outil ni notre annuaire. C'est de savoir si le site ressemble à un média établi. La vente d'espace éditorial est l'un des trois piliers de monétisation du projet ; l'apparence média en est la condition d'accès directe.

Il y a donc deux publics, et la page d'accueil doit servir les deux sans en sacrifier un :

| Public | Ce qu'il cherche | Ce qui le convainc |
|---|---|---|
| Le professionnel qui achète ou loue | une réponse, un prix, un prestataire | l'outil, les prix, l'annuaire, les guides |
| L'annonceur, le partenaire, le pair | un média crédible et vivant | une une éditoriale, des rubriques, des signatures, un rythme, des contenus originaux |

Ce qui rend un site « média » — et qui doit être visible sans cliquer :

1. **Une hiérarchie éditoriale** : une une, des rubriques identifiées, des formats distincts.
2. **Des signatures** : chaque article porte un auteur, une date de publication et une date de vérification.
3. **Un rythme** : des dates récentes, une publication régulière.
4. **Des contenus originaux** : le baromètre des prix n'est pas seulement un aimant à liens, c'est la preuve qu'on produit de la donnée que personne d'autre n'a. C'est ce qui fait citer un média.
5. **Une rédaction identifiable** : page auteur réelle, ligne éditoriale explicite, charte de transparence.

### Les rubriques éditoriales

Cinq rubriques, qui structurent la navigation et la page d'accueil :

| Rubrique | Contenu | Catégories |
|---|---|---|
| **Équipements** | le matériel, famille par famille | les 11 familles existantes |
| **Réglementation** | CACES, VGP, autorisation de conduite, obligations | `reglementation`, `securite` |
| **Coûts** | budgets, TCO, achat contre location, financement | `couts` |
| **Exploitation** | méthodes d'entrepôt, flux, maintenance, gestion de parc | `exploitation` |
| **Marché** | constructeurs, distributeurs, marques | `entreprise` + `/marque/{x}/` |

Les pages prix restent nestées sous leur famille (`/chariot-elevateur/prix/`) : la rubrique Coûts accueille les articles de budget et de financement, qui sont un contenu différent et ne les cannibalisent pas.

---

## 2. Page d'accueil

### Ce que cette page doit faire

Deux fonctions, à tenir ensemble.

**Distribuer l'autorité.** Les 167 domaines référents du domaine pointent tous vers elle, aucune page interne n'a de jus. Chaque lien sortant de l'accueil est un choix d'allocation, pas de la décoration.

**Ressembler à un média.** Un annonceur qui arrive ici doit voir une publication vivante, pas un tunnel de conversion.

La résolution tient dans un constat simple : **le bloc des rubriques fait les deux à la fois.** Il se lit comme le sommaire d'un magazine et il redistribue l'autorité vers les pages qui rapportent.

### Sections, dans l'ordre (l'ordre mobile est le même)

**1 — La une**
Le contenu phare du moment : un dossier, le baromètre, un grand guide. Titre, chapô, signature, date, une image qui informe. C'est la première chose que voit un annonceur, et c'est ce qui dit « ce média publie ».

**2 — Les essentiels**
Trois à quatre contenus de référence, ceux qu'on veut voir cités. Format cartes, avec rubrique, titre et date.

**3 — Les rubriques**
Le cœur de la page. Chaque rubrique affiche son nom, sa promesse en une ligne, et ses trois ou quatre dernières publications. Pour Équipements, la grille des familles avec **ancres descriptives** et fourchette d'entrée dès que les prix existent.

Ce bloc est à la fois le sommaire du média et le moteur de redistribution d'autorité.

**4 — Combien ça coûte**
Intention d'achat maximale, et démonstration éditoriale : des fourchettes datées, avec leur date de constat visible. C'est ce que personne d'autre ne publie.

**5 — Quel engin vous faut-il ?**
L'outil, première question posée directement dans la page.
*Tant qu'il n'existe pas, la section est simplement absente — on ne met pas de promesse à la place.*

**6 — Trouver un prestataire**
Sélecteur département puis ville, et les trois entrées d'usage : louer, faire réparer, faire contrôler.

**7 — La rédaction**
Qui écrit, avec quel parcours, selon quelle méthode. Liens vers la page auteur, la ligne éditoriale et la charte de transparence. Sur du contenu sécurité et réglementation, c'est ce bloc qui fait la différence — pour Google comme pour un préventeur.

**8 — Newsletter**
Une ligne de promesse, un champ, une case de consentement.

### Ce que l'accueil ne contient pas

Pas de carrousel. Pas de fil social. Pas de mur de logos partenaires tant qu'il n'y a pas de vrais partenaires. Pas de compteurs de visiteurs. Pas de témoignages fabriqués. Et pas de brèves d'actualité : on est un média de décision, pas un média d'actualité — ce choix reste ferme.

---

## 3. Page pilier

`/manutention/`, `/levage/`, `/stockage/` — archives de catégorie de niveau pilier.

| Bloc | Contenu |
|---|---|
| Fil d'Ariane | Accueil › Pilier |
| H1 + chapô | 2-3 phrases : ce que couvre le pilier, pour quel usage |
| Grille des familles | Liens descendants **obligatoires**, ancres exactes |
| Boucle articles | **Filtrée sur le silo du pilier**, 8-12 articles |
| FAQ | 4-6 questions, `FAQPage` |
| Liens latéraux | Les autres piliers, un lien chacun |

Recette bloquante : deux piliers ne doivent jamais afficher la même liste d'articles. C'était le défaut P1 de pluscestsimple, où `/travaux/` et `/immobilier/` sortaient les quinze mêmes articles du site.

---

## 4. Page famille

`/chariot-elevateur/`, `/gerbeur/`… — archives de catégorie de niveau famille. **C'est une page qui rapporte.**

| Bloc | Contenu |
|---|---|
| Fil d'Ariane | Accueil › Pilier › Famille |
| H1 + chapô | Réponse courte : à quoi sert ce matériel, et sa limite |
| Arbre de décision | Usage → type. Le cœur de la page. |
| Tableau comparatif | Sous-types, capacités, hauteurs, usage type |
| Bloc prix | Fourchettes issues de `arw_prix`, datées, lien vers la page prix de la famille |
| Réglementation applicable | Encadré court + lien vers la page réglementaire dédiée. On ne développe pas ici. |
| Boucle articles | **Filtrée sur cette famille uniquement** |
| Bloc annuaire | « Louer un {machine} près de chez vous » + sélecteur géo |
| FAQ | `FAQPage` |
| CTA unique | Affiliation si petit matériel, devis si gros matériel ou location |
| Familles sœurs | Liens latéraux intra-pilier |

---

## 5. Article

Le volume. Sa valeur ne se mesure pas à son trafic mais à ce qu'il **transmet** aux pages qui rapportent.

| Bloc | Contenu |
|---|---|
| Fil d'Ariane | Accueil › Pilier › Famille › Article |
| H1 | ≤ 65 caractères, sans superlatif |
| Signature | Auteur, date de publication, **date de dernière vérification** |
| Chapô | La réponse en 2-3 phrases, avant tout développement |
| Sommaire ancré | Si plus de quatre H2 |
| Corps | ≤ 400 mots par H2 |
| Encadré normatif | Fond distinct, référence citée, lien source, mention de non-conseil |
| Bloc prix | Injecté depuis `arw_prix` si pertinent. Jamais de prix en dur. |
| Lien remontant contextuel | **Dans le corps du texte**, ancre descriptive, vers la famille et le pilier. Le fil d'Ariane ne suffit pas. |
| CTA unique | Contextuel |
| À lire dans le même univers | 3-5 articles **de la même catégorie uniquement**. Requête interne, aucune dépendance à un plugin tiers. |

Les deux dernières lignes corrigent les défauts P3 et P4 de pluscestsimple : des articles sans lien remontant éditorial, et un maillage latéral qui dépendait d'un plugin non identifié.

---

## 5 bis. Structure des titres — règles contraignantes

Un plan de titres n'est pas de la mise en forme : c'est ce que Google et les modèles de langage lisent pour comprendre la page. Trois règles, sans exception.

**Un seul H1 par page**, et c'est le titre de la page. Jamais le logo, jamais un intertitre de bloc.

**Aucun saut de niveau.** Un H3 ne peut exister que sous un H2.

**Un titre décrit du contenu, pas un élément d'interface.** C'est la règle qui tranche tous les cas douteux. « Caractéristiques », « Points forts », « Points faibles », « Notre avis en 30 secondes », « Sommaire », « Aller directement à un modèle » sont des libellés : ils se répètent à l'identique dix fois dans la page et pollueraient le plan sans rien lui apprendre. Ils restent des `<p>` ou des `<span>` stylés. À l'inverse, une question de FAQ est du contenu réel et unique : elle prend un H3, ce qui la rend aussi cohérente avec le balisage `FAQPage`.

### Le plan d'une page comparatif

```
H1   Les 10 meilleurs transpalettes électriques pour PME
H2   Notre sélection en bref
H2   Tableau comparatif des 10 modèles
H2   Jungheinrich EJE 116          ← marque + modèle, un H2 par produit
H2   Pramac QX 18
H2   Comment nous avons classé
H2   Questions fréquentes
  H3   Faut-il un CACES pour un transpalette électrique ?
  H3   Lithium ou plomb pour un usage quotidien ?
```

Le titre de produit est **toujours « marque + modèle exact »**, et rien d'autre. C'est ce que l'internaute tape quand il cherche des informations sur une machine précise — « Jungheinrich EJE 116 avis », « Jungheinrich EJE 116 prix ». Un H2 « Le premier de notre sélection » ne capte rien.

Le mettre en H2 plutôt qu'en H3 n'est pas cosmétique : chaque produit devient une section de premier niveau du document, ce qui sert l'indexation par passages et les liens de saut affichés dans les résultats de recherche.

### Le plan d'une page article

```
H1   Le gerbeur Fenwick : gammes, capacités et CACES applicable
H2   Ce qu'il faut retenir
H2   « CACES Fenwick » : la confusion à lever
H2   Les gammes Fenwick-Linde et leurs capacités
  H3   (sous-sections si nécessaire)
H2   Vu en exploitation
H2   Sources
H2   À lire dans le même univers
  H3   (titre de chaque article lié)
```

Les encarts de la colonne latérale portent un H2 **à l'intérieur d'un `<aside>`**. C'est correct pour l'accessibilité — les lecteurs d'écran annoncent la zone complémentaire séparément — et sans effet sur le plan du contenu principal.

### Appels à l'action

Le libellé visible reste court (« Voir l'offre »). L'intitulé complet passe en `aria-label` : « Voir l'offre pour le Jungheinrich EJE 116 ». On garde un bouton lisible sans sacrifier l'accessibilité.

Les liens sortants commerciaux portent `rel="sponsored nofollow"`, sans exception.

### Vérification

La maquette `design/gabarits.html` embarque un bouton **« ⌗ Voir les titres »** qui affiche le niveau réel de chaque titre directement sur la page. Il sert à la recette et ne fait pas partie du thème.

---

## 6. Comparatif « les meilleurs… »

Gabarit d'affiliation pure.

Verdict en tête — le choix recommandé apparaît avant tout développement, un lecteur pressé doit l'avoir en trois secondes. Puis tableau comparatif, puis cartes produit (marque, caractéristiques structurées, prix indicatif daté, lien `/go/{slug}` en `rel="sponsored"`), puis les critères de choix expliqués, puis la FAQ. Mention de transparence affiliation obligatoire, visible, pas en pied de page microscopique.

Aucun formulaire de devis sur ce gabarit.

---

## 7. Page prix

`/{famille}/prix/`. Intention d'achat maximale, et la page la plus exposée en crédibilité.

Fourchette principale annoncée dès le chapô, avec sa date. Puis tableau par configuration (capacité, énergie, hauteur), puis les **coûts annexes** que personne ne chiffre — VGP, batterie, maintenance, formation, transport — puis la comparaison achat / location / LLD, puis la méthodologie : d'où viennent ces chiffres, combien de relevés, à quelle date.

Tous les montants viennent de `arw_prix`. Mention obligatoire sous chaque bloc. CTA devis.

---

## 8. Annuaire — page croisement géo

Bloc de contenu local unique **en premier**, avant la liste : fourchette tarifaire locale, contraintes locales pertinentes, tissu économique. C'est ce bloc qui décide si la page mérite d'exister. Puis la liste filtrable, la carte, la FAQ contextualisée, le CTA devis, et les liens vers les zones voisines et vers la famille.

Une page sans ce bloc reste en brouillon et n'est jamais publiée.

---

## 9. Annuaire — fiche établissement

Coordonnées, activités (louer / vendre / réparer / contrôler / former), machines et marques couvertes, zone d'intervention. Balisage `LocalBusiness`. CTA de demande de devis. Et le bouton **« Revendiquer cette fiche »**, qui est la mécanique d'amorçage des partenariats commerciaux.

---

## 10. Page outil

`/outils/quel-engin-de-manutention/`. L'outil en haut, puis **tout le contenu rédactionnel rendu côté serveur** : tableau comparatif des familles, arbre de décision en texte, cas d'usage, limites. Un outil seul ne se positionne pas.

Balisage `FAQPage` et `HowTo`. JavaScript vanilla, chargé sur cette page uniquement, zéro dépendance externe.

---

## 11. Navigation, en-tête, pied de page

**Navigation : les cinq rubriques.**

```
Équipements ▾   Réglementation   Coûts   Exploitation   Marché        [ Annuaire ]
```

C'est la navigation d'un média : elle donne à voir l'univers éditorial, pas un tunnel. Un seul niveau de déroulant, sous *Équipements*, listant les familles.

L'**Annuaire** est traité en bouton distinct plutôt qu'en entrée de menu — c'est un service, pas une rubrique, et le distinguer visuellement le rend plus visible qu'une sixième entrée noyée dans la liste. Les outils sont accessibles depuis l'accueil et depuis les pages concernées.

Les pages prix n'ont pas d'entrée de menu : leur accès passe par l'accueil et par chaque page famille. Leur donner une entrée recréerait le hub transverse écarté pour cannibalisation. La rubrique *Coûts* pointe vers les articles de budget et de financement, qui sont un contenu distinct.

Pas de méga-menu, pas de barre latérale. Chaque pilier ouvert plus tard s'insère dans *Équipements*, pas en entrée supplémentaire.

**Pied de page** : mentions légales, confidentialité, transparence affiliation, ligne éditoriale, contact, à propos. Rien d'autre — pas de nuage de tags, pas de plan de site déguisé.

---

## 12. Gabarit dossier

Le format long qui installe le statut de média. Un dossier n'est pas un article : c'est un ensemble de pages présentées comme un tout, avec une page de tête et une navigation interne.

Page de tête : titre, chapô, sommaire du dossier, signature, date de publication et de mise à jour. Chaque volet du dossier est un article normal, qui porte un bandeau « Ce contenu fait partie du dossier X » et la navigation vers les volets voisins.

Premiers dossiers prévus : **le baromètre des prix de la manutention** (le contenu original qui fait citer), **CACES et autorisation de conduite** (le plus gros gisement de volume), **acheter ou louer**.

Techniquement : une taxonomie non hiérarchique `dossier`, pas une catégorie — un article appartient à un seul silo mais peut entrer dans un dossier transverse sans casser la règle « un article, un silo ».

---

## 13. Direction visuelle

Registre industriel sobre. Le lecteur est un professionnel, la page doit avoir l'air d'un outil de travail.

- **Typographie** : pile système exclusivement, aucune police externe. Zéro requête réseau, texte affiché immédiatement.
- **Couleurs** : base anthracite et blanc cassé, échelle de gris neutres, **un seul accent** — un jaune sécurité réservé aux appels à l'action. C'est le code couleur du secteur, il se lit instantanément.
- **Pas d'ombres, pas de dégradés, pas de coins très arrondis.** Les bordures et les espacements font le travail.
- **Tableaux** : défilement horizontal dans leur propre conteneur, première colonne figée. Le corps de la page ne défile jamais horizontalement.
- **Images** : WebP, dimensions explicites, `loading="lazy"`. Pas d'image d'illustration décorative — soit elle informe (schéma, abaque, photo de machine), soit elle ne rentre pas.

---

## 14. Performance et sécurité

**Zéro JavaScript par défaut.** Deux exceptions, chargées uniquement sur leur page : le sélecteur d'engin et la carte de l'annuaire.

CSS critique inliné, budget total sous 50 ko. Cache objet et cache page pour l'annuaire. Cible Lighthouse 100/100/100/100, LCP sous 1,5 s en 4G simulée.

Sécurité : reprise de `inc/security.php` de pluscestsimple (CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, blocage de l'énumération d'auteurs, limitation de débit sur les formulaires publics). S'y ajoutent, propres à ce site : requêtes préparées sur toutes les tables `arw_*`, nonces et vérification de capacité sur les écrans d'import et de modération, échappement en sortie systématique des données d'annuaire (elles viennent d'un import externe), vérification par e-mail des revendications de fiche, hachage des IP et des numéros appelants. Passage `/audit-securite-wp` avant mise en production.

---

## 15. Ordre de construction

1. Socle : `theme.json`, styles de base, en-tête à cinq rubriques, pied de page, fil d'Ariane, SEO et Schema
2. **Gabarit article avec signature complète** — auteur, date de publication, date de vérification, rubrique. C'est le volume, les 63 pages à recréer en dépendent, et c'est la brique qui rend le site lisible comme un média.
3. Pages d'identité éditoriale : auteur, ligne éditoriale, charte de transparence, contact, mentions légales. Elles sont dans les premières livraisons, pas en fin de liste — sans elles, aucun annonceur ne prend le site au sérieux.
4. Gabarit famille et gabarit pilier
5. Page d'accueil
6. Gabarit prix
7. Gabarit dossier, puis premier dossier
8. Gabarit outil
9. Gabarits annuaire (croisement et fiche), avec la brique base de données
10. Gabarit comparatif affiliation
