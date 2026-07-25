# Gabarits du thème — infoweb-manutention.fr

Spécification des modèles de page avant écriture du code. Découle de `strategie-media-industrie.md` (§1.6 hiérarchie par valeur business, §7 gabarits) et de l'audit du cocon de pluscestsimple, dont les six défauts deviennent ici des contraintes.

---

## 0. Les trois contraintes qui gouvernent tout

**1. Le contenu des gabarits n'est jamais copié en base.** Piliers, familles et pages système sont rendus par gabarit PHP. Modifier le fichier met à jour la page immédiatement. C'est la correction du défaut le plus coûteux constaté sur pluscestsimple, où le contenu des patterns était figé en base au premier seed et où toute correction exigeait une migration.

**2. Chaque page connaît la page qui rapporte vers laquelle elle pointe.** Ce lien est structurel, pas décoratif : il est dans le gabarit ou dans le brief éditorial, jamais laissé au hasard.

**3. Un seul appel à l'action visible par page.** Affiliation *ou* devis, jamais les deux. C'est la règle de monétisation du brief traduite en règle d'interface.

---

## 1. Page d'accueil

### Ce que cette page doit faire

Elle ne fera pas de trafic de recherche au-delà des requêtes de marque, et ce n'est pas grave. Son rôle est ailleurs, et il est décisif :

**Les 167 domaines référents du domaine pointent tous vers elle.** Aucune page interne n'a de jus. La fonction première de l'accueil est donc de **redistribuer cette autorité vers les pages qui rapportent** — familles, prix, annuaire, outil. Chaque lien sortant de l'accueil est un choix d'allocation d'autorité, pas un élément de décoration.

Second rôle : c'est la page que regardera un annonceur avant d'acheter un article sponsorisé, et un loueur avant d'accepter un partenariat. Elle doit avoir l'air d'un média sérieux tenu par quelqu'un de compétent.

### Sections, dans l'ordre (l'ordre mobile est le même)

**1 — Proposition**
Une phrase factuelle, pas un slogan : ce qu'est le site et pour qui. Suivie de deux actions primaires, et pas plus : *Quel engin me faut-il ?* (l'outil) et *Trouver un prestataire près de chez moi* (l'annuaire). Aucune image d'illustration décorative, aucun carrousel.

**2 — Sélecteur d'engin**
L'outil, avec sa première question posée directement dans la page. Un utilisateur doit pouvoir commencer sans cliquer. C'est le différenciateur du site et l'entrée des deux voies de monétisation.
*Au lancement, tant que l'outil n'existe pas, cette section est occupée par le bloc « Combien ça coûte » qui remonte en position 2.*

**3 — Le matériel, famille par famille**
Le moteur de distribution d'autorité. Une grille de cartes, une par famille, avec **ancre descriptive** (« Chariots élévateurs », pas « En savoir plus ») et une ligne de contexte. Dès que les prix existent, chaque carte porte sa fourchette d'entrée — c'est ce qui transforme une grille de navigation en grille de décision.

**4 — Combien ça coûte**
Intention d'achat maximale. Quatre à six cartes vers les pages prix, chacune avec sa fourchette et sa date de constat visible. C'est le bloc qui prouve immédiatement que le site donne ce que les autres cachent.

**5 — Trouver un prestataire**
Sélecteur département puis ville, plus les trois entrées d'usage : louer, faire réparer, faire contrôler. Entrée directe vers les pages annuaire.

**6 — Obligations et réglementation**
CACES, VGP, autorisation de conduite. C'est le plus gros gisement de volume de la niche et le bloc qui installe la crédibilité auprès d'un préventeur.

**7 — Derniers guides**
Six articles, format sobre. Volontairement modeste : ce n'est pas un magazine, et le trafic d'articles n'est pas l'objectif.

**8 — Qui écrit ici**
Photo, nom, parcours en trois lignes, lien vers la page auteur. Indispensable pour l'E-E-A-T sur du contenu sécurité et réglementation, et indispensable pour vendre de l'espace éditorial.

**9 — Newsletter**
Capture B2B, une ligne de promesse, un champ, une case de consentement.

### Ce que l'accueil ne contient pas

Pas de carrousel. Pas d'actualités. Pas de fil social. Pas de mur de logos partenaires tant qu'il n'y a pas de vrais partenaires. Pas de compteurs (« 10 000 visiteurs ! »). Pas de témoignages fabriqués.

---

## 2. Page pilier

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

## 3. Page famille

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

## 4. Article

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

## 5. Comparatif « les meilleurs… »

Gabarit d'affiliation pure.

Verdict en tête — le choix recommandé apparaît avant tout développement, un lecteur pressé doit l'avoir en trois secondes. Puis tableau comparatif, puis cartes produit (marque, caractéristiques structurées, prix indicatif daté, lien `/go/{slug}` en `rel="sponsored"`), puis les critères de choix expliqués, puis la FAQ. Mention de transparence affiliation obligatoire, visible, pas en pied de page microscopique.

Aucun formulaire de devis sur ce gabarit.

---

## 6. Page prix

`/{famille}/prix/`. Intention d'achat maximale, et la page la plus exposée en crédibilité.

Fourchette principale annoncée dès le chapô, avec sa date. Puis tableau par configuration (capacité, énergie, hauteur), puis les **coûts annexes** que personne ne chiffre — VGP, batterie, maintenance, formation, transport — puis la comparaison achat / location / LLD, puis la méthodologie : d'où viennent ces chiffres, combien de relevés, à quelle date.

Tous les montants viennent de `arw_prix`. Mention obligatoire sous chaque bloc. CTA devis.

---

## 7. Annuaire — page croisement géo

Bloc de contenu local unique **en premier**, avant la liste : fourchette tarifaire locale, contraintes locales pertinentes, tissu économique. C'est ce bloc qui décide si la page mérite d'exister. Puis la liste filtrable, la carte, la FAQ contextualisée, le CTA devis, et les liens vers les zones voisines et vers la famille.

Une page sans ce bloc reste en brouillon et n'est jamais publiée.

---

## 8. Annuaire — fiche établissement

Coordonnées, activités (louer / vendre / réparer / contrôler / former), machines et marques couvertes, zone d'intervention. Balisage `LocalBusiness`. CTA de demande de devis. Et le bouton **« Revendiquer cette fiche »**, qui est la mécanique d'amorçage des partenariats commerciaux.

---

## 9. Page outil

`/outils/quel-engin-de-manutention/`. L'outil en haut, puis **tout le contenu rédactionnel rendu côté serveur** : tableau comparatif des familles, arbre de décision en texte, cas d'usage, limites. Un outil seul ne se positionne pas.

Balisage `FAQPage` et `HowTo`. JavaScript vanilla, chargé sur cette page uniquement, zéro dépendance externe.

---

## 10. Navigation, en-tête, pied de page

**Navigation : quatre entrées.**

```
Matériel ▾   Réglementation   Annuaire   Quel engin ?
```

Un seul niveau de déroulant, sous *Matériel*, listant les familles. Pas de méga-menu, pas de barre latérale. Les pages prix sont atteintes depuis l'accueil et depuis chaque page famille — elles n'ont pas besoin d'une entrée de menu, et lui en donner une recréerait le hub transverse qu'on a écarté.

Chaque pilier ouvert plus tard prend une entrée. À sept entrées, on regroupe.

**Pied de page** : mentions légales, confidentialité, transparence affiliation, contact, à propos. Rien d'autre — pas de nuage de tags, pas de plan de site déguisé.

---

## 11. Direction visuelle

Registre industriel sobre. Le lecteur est un professionnel, la page doit avoir l'air d'un outil de travail.

- **Typographie** : pile système exclusivement, aucune police externe. Zéro requête réseau, texte affiché immédiatement.
- **Couleurs** : base anthracite et blanc cassé, échelle de gris neutres, **un seul accent** — un jaune sécurité réservé aux appels à l'action. C'est le code couleur du secteur, il se lit instantanément.
- **Pas d'ombres, pas de dégradés, pas de coins très arrondis.** Les bordures et les espacements font le travail.
- **Tableaux** : défilement horizontal dans leur propre conteneur, première colonne figée. Le corps de la page ne défile jamais horizontalement.
- **Images** : WebP, dimensions explicites, `loading="lazy"`. Pas d'image d'illustration décorative — soit elle informe (schéma, abaque, photo de machine), soit elle ne rentre pas.

---

## 12. Performance et sécurité

**Zéro JavaScript par défaut.** Deux exceptions, chargées uniquement sur leur page : le sélecteur d'engin et la carte de l'annuaire.

CSS critique inliné, budget total sous 50 ko. Cache objet et cache page pour l'annuaire. Cible Lighthouse 100/100/100/100, LCP sous 1,5 s en 4G simulée.

Sécurité : reprise de `inc/security.php` de pluscestsimple (CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, blocage de l'énumération d'auteurs, limitation de débit sur les formulaires publics). S'y ajoutent, propres à ce site : requêtes préparées sur toutes les tables `arw_*`, nonces et vérification de capacité sur les écrans d'import et de modération, échappement en sortie systématique des données d'annuaire (elles viennent d'un import externe), vérification par e-mail des revendications de fiche, hachage des IP et des numéros appelants. Passage `/audit-securite-wp` avant mise en production.

---

## 13. Ordre de construction

1. Socle : `theme.json`, styles de base, en-tête, pied de page, fil d'Ariane, SEO et Schema
2. Gabarit article — c'est le volume, et les 63 pages à recréer en dépendent
3. Gabarit famille et gabarit pilier
4. Page d'accueil
5. Gabarit prix
6. Gabarit outil
7. Gabarits annuaire (croisement et fiche), avec la brique base de données
8. Gabarit comparatif affiliation
