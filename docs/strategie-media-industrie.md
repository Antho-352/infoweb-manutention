# Stratégie — média industrie, cocon, annuaire, outil, thème

Date : 2026-07-25. Répond aux questions posées après le go phase 0. Remplace la section « architecture » de `analyse-critique-brief.md` ; les constats de cette analyse (330 mots-clés vivants, docroot vide, plan de redirections) restent valides.

---

## 1. Les médias industrie existent-ils ? Recherche

### 1.1 Qui occupe le terrain

Trois familles distinctes, qui ne jouent pas le même jeu.

**La presse pro payante (inatteignable frontalement, et ce n'est pas grave)**
- **L'Usine Nouvelle** (groupe Infopro Digital, ex-Groupe Moniteur, adossé à Apax) : abonnement print+digital, ~10 événements/an, awards, Databoard de données sectorielles, jobboard, newsletters métier. Rédaction de journalistes salariés. A une rubrique « Manutention et machinisme ».
- **Techniques de l'Ingénieur** : abonnement à une base documentaire technique. Modèle documentaire, pas média.
- **Industrie & Technologies**, **L'Usine Digitale** : même écurie Infopro.

Leur monétisation repose sur l'abonnement, l'événementiel et la data. Ces trois leviers demandent une rédaction, une équipe commerciale et un fonds de commerce. Hors de portée, et hors sujet pour nous.

**Le média sectoriel monétisé par les services (le vrai modèle à copier)**
- **Actu-Environnement** est le cas d'école. 10 piliers thématiques (Énergie, Eau, Déchets, Risques, Bâtiment, Transport, Aménagement, Biodiversité, Agroécologie, Gouvernance) plus des rubriques transverses (Juridique, Techniques/Matériaux, Agenda, Formation). Monétisation empilée : paywall partiel, newsletter, **jobboard sur domaine dédié** (emploi-environnement.com), **catalogue de formations**, publicité display, **publi-reportages** (articles sponsorisés), et surtout un **annuaire de solutions et prestataires** (« Solutions & Innovations », « Matériels-Services ») où les entreprises paient leur visibilité.

C'est exactement l'empilement qu'on vise — média + annuaire + contenus sponsorisés — avec deux briques supplémentaires qu'ils exploitent et qu'on avait mises de côté : le jobboard et le catalogue de formations.

**Les acteurs de notre niche précise (nos vrais concurrents)**
- **solutions-manutention.fr** : presse spécialisée équipements d'entrepôt.
- **faq-logistique.com** : ressource logistique avec **annuaire d'équipementiers**.
- **logistique-magazine.fr**, **Voxlog** (actualité supply chain gratuite, podcasts), **PharmaCos-Média** (rubrique manutention-logistique).
- **blog-manutention.fr** : contenu de marque STILL.
- Côté catalogue : **DirectIndustry** (VirtualExpo), **Kompass**, **Europages** — annuaires/catalogues fabricants payants.
- Côté lead-gen : **Hellopro**, **Companeo**.

### 1.2 Comment ils structurent leur cocon

Constat sur Actu-Environnement, le plus abouti : **piliers thématiques permanents + rubriques transverses fonctionnelles**. La thématique (Énergie, Déchets) porte le cocon SEO ; le transverse (Juridique, Formation, Agenda, Annuaire) porte l'usage et la monétisation. Les deux se croisent : un article juridique sur les déchets vit dans le pilier Déchets et dans la rubrique Juridique.

C'est le schéma que je reprends : **piliers = familles de matériel et de métier ; transverses = réglementation, prix, annuaire, outils**.

> **Correction du 2026-07-25.** J'avais écrit ici que le trou était au niveau des pages piliers de ces médias, qui sont des flux d'actualité incapables de se positionner sur les requêtes de décision. C'est faux, et les données le montrent — voir §1.5. Le trou n'est pas au niveau pilier : il est au niveau des articles de longue traîne, de l'annuaire local et des outils.

### 1.5 Ce que les données disent des pages piliers (correction)

Mesure sur la Search Console de pluscestsimple.com, période complète de l'export :

| Périmètre | Pages | Clics | Impressions |
|---|---|---|---|
| Site entier | 627 | 3 163 | 293 344 |
| **Pages piliers et sous-piliers** | 9 | **0** | **64** (0,02 %) |

Six pages piliers, zéro clic. Elles se positionnent pourtant correctement (positions moyennes 5,5 à 11,75) — mais sur des requêtes que personne ne tape. Les 3 163 clics du site viennent intégralement d'articles de longue traîne : « muralière », « colle amiante », « plinthe invisible placo », « comment occulter un velux sans store ».

Le même constat vaut sur infoweb-manutention : les pages qui rankaient encore étaient des articles (`/gerbeur/le-gerbeur-fenwick…`, `/chariot-elevateur/norme-passage…`), pas les pages de catégorie, qui plafonnaient à 1 visite.

**Deux causes se cumulent, et il faut les distinguer.** Les défauts structurels documentés dans l'audit de pluscestsimple (boucle non filtrée, hiérarchie corrompue, aucun lien descendant) expliquent une partie du résultat. Mais la cause principale est plus simple : **le terme de tête d'un pilier — « travaux », « décoration », « manutention » — n'est pas un objectif réaliste pour un site sans autorité massive.**

**Conséquence sur notre plan.** Une page pilier n'est pas un actif de trafic, c'est de la plomberie : elle structure le silo, distribue le jus, et sert de point d'atterrissage au maillage. On la construit une fois, proprement, et on n'y réinvestit pas. Le moteur de trafic, ce sont les articles de décision, l'annuaire et les outils.

Corollaire : les requêtes de décision que je citais (« quel gerbeur choisir », « prix location nacelle ») ne sont **pas** des requêtes de page pilier. Ce sont des requêtes d'articles et de pages familles. C'est là qu'il faut mettre l'effort, et c'est là qu'est le trou réel.

### 1.3 Comment ils gagnent de l'argent — et ce qu'on peut reprendre

| Levier | Qui le fait | Reprenable ? |
|---|---|---|
| Annuaire prestataires avec visibilité payante | Actu-Environnement, faq-logistique, DirectIndustry | **Oui, cœur du plan** |
| Articles sponsorisés / publi-reportages | Actu-Environnement, tous | **Oui, déjà prévu** |
| Affiliation matériel | Personne dans la niche pro | **Oui, angle mort du marché** |
| Leads devis | Hellopro, Companeo | **Oui, déjà prévu** |
| Jobboard | Actu-Environnement (domaine dédié) | **Oui, phase 3+** — « emploi cariste » est un gros volume |
| Catalogue de formations | Actu-Environnement, OùFormer, caces.fr | Oui, mais SERP saturée — via l'annuaire plutôt qu'en marketplace |
| Abonnement / paywall | Usine Nouvelle, Techniques de l'Ingénieur | **Non** — demande une rédaction |
| Événements | Usine Nouvelle | **Non** — hors capacité |
| Data / Databoard | Usine Nouvelle | Version légère : le baromètre des prix |

### 1.4 Doit-on faire mieux ? Peut-on faire mieux ?

Réponse honnête, en deux temps.

**Sur leur terrain, non.** On ne bat pas L'Usine Nouvelle sur l'actualité industrielle : ils ont des journalistes, on n'en a pas. Le contenu d'actualité se démode, ne se positionne pas durablement, et se monétise par des leviers qu'on n'a pas. **Toute minute passée à faire de l'actualité industrielle est perdue.**

**Sur le terrain de la décision, oui, largement.** Personne ne réunit les quatre choses dont un responsable d'exploitation a besoin au moment d'acheter, louer ou se mettre en conformité :
1. des **fourchettes de prix sourcées et datées** (les médias n'en donnent pas, les vendeurs cachent les leurs) ;
2. de la **réglementation traduite en opérationnel** (CACES, VGP, autorisation de conduite) ;
3. un **annuaire local exhaustif et multi-enseignes** (PagesJaunes est générique, les loueurs ne référencent qu'eux-mêmes) ;
4. des **outils de décision** (quasi aucun dans la niche équipement).

Les deux premiers points portent un risque réel — péremption et charge de maintenance pour les prix, risque juridique pour le réglementaire. Ils ne sont tenables que sous protocole strict : **[docs/protocole-fiabilite.md](protocole-fiabilite.md)** est contraignant, et son non-respect bloque la publication. En résumé : les prix sont des données en base et jamais de la prose, plafonnés à 40-60 points de référence révisés deux fois par an ; le réglementaire n'est écrit qu'à partir de sources primaires téléchargées localement (Légifrance, ameli.fr, INRS), vérifié affirmation par affirmation par un second agent, passé au lint automatique, et relu par Anthony sur les pages sensibles.

D'où le positionnement que je recommande, et c'est une correction de cadrage importante :

> **Pas un « média industrie » (qui implique de l'actualité), mais un média de décision de l'exploitation industrielle.** Contenu evergreen, orienté achat / location / conformité / exploitation.

Cette distinction n'est pas cosmétique : elle détermine qu'on ne publie jamais de brève, qu'on ne court pas après l'actualité produit, et que chaque page vise une requête de décision durable.

---

## 2. Le pivot : d'accord sur le principe, avec deux corrections

Ta proposition — média Industrie, manutention comme premier pilier, élargissement progressif vers énergie, logistique, automatisation/IA, puis secteurs d'activité — est la bonne direction. L'argument décisif est le tien : concevoir l'architecture pour N piliers dès maintenant évite de tout restructurer dans 18 mois. Je la retiens.

Deux corrections, argumentées.

### Correction 1 — élargir par lecteur, pas par secteur

Le fil conducteur ne doit pas être « l'industrie » (un domaine) mais **le lecteur : celui qui fait tourner un site industriel ou logistique** — responsable logistique, chef d'entrepôt, responsable maintenance, directeur de site, préventeur, acheteur.

Tout ce qui sert ce lecteur est dans le périmètre, et l'élargissement que tu proposes passe le test sans exception :
- manutention et levage → son quotidien
- stockage et intralogistique → son entrepôt
- sécurité et réglementation → sa responsabilité pénale
- énergie et utilités (air comprimé, batteries, ZFE) → ses coûts
- automatisation et robotique d'entrepôt (AGV, AMR, WMS) → ses projets
- maintenance industrielle → son budget

Ce cadrage garde une cohérence sémantique forte autour de la manutention — donc le nom de domaine reste juste — tout en autorisant exactement l'expansion que tu décris.

### Correction 2 — les secteurs sont un second axe du cocon, pas des piliers concurrents

Agroalimentaire, chimie/pharma, aéronautique, automobile, métallurgie, textile : **ils font partie du plan, et ils sont importants.** La question n'est pas s'il faut les traiter, mais où les brancher.

Ce qui est validé par la recherche : la demande existe et elle est monétisable. « Chariot élévateur agroalimentaire », matériel inox 304/316, engins frigorifiques jusqu'à −20 °C, résistance au lavage haute pression — VMAX a déjà une page « Chariot élévateur agroalimentaire et pharmaceutique », Experlift a une catégorie inox complète, Axess Industries et Mecamontage vendent la gamme. C'est du trafic qualifié qui tombe directement sur du matériel affiliable.

**Le secteur est donc un second axe du cocon**, croisé avec l'axe matériel :

```
axe MATÉRIEL   :  /chariot-elevateur/   /gerbeur/   /rayonnage/ …
axe SECTEUR    :  /industries/agroalimentaire/   /industries/pharmacie-chimie/ …
croisements    :  /industries/agroalimentaire/chariot-elevateur/
```

Fonctionnement :
- La **page secteur** traite les contraintes du secteur, toutes familles confondues : en agroalimentaire, inox et nettoyabilité, froid positif et négatif, HACCP, séparation des flux, sols lavables. Elle lie vers les familles concernées.
- Les **croisements secteur × famille** traitent une famille sous l'angle du secteur, et ne visent que des requêtes qualifiées par le secteur.
- La **famille** ne parle jamais d'un secteur en particulier ; elle lie vers les pages secteur.

Règle anti-cannibalisation, appliquée strictement : une page secteur ou un croisement ne vise **que** des requêtes contenant le qualificatif sectoriel. « Chariot élévateur agroalimentaire » appartient au croisement ; « chariot élévateur » appartient à la famille. Jamais l'inverse.

Ce qui reste hors périmètre, en revanche, c'est **l'actualité sectorielle** : « les résultats du secteur agroalimentaire », « la conjoncture de la chimie ». Autre lecteur, autre média, aucune monétisation pour nous. La règle générale reste : **un contenu qui ne sert pas le responsable d'exploitation ne rentre pas, quel que soit son volume de recherche** — c'est ce qui a tué la version précédente du site (remorques auto, outillage Parkside, rénovation BTP) et ce qui plombe pluscestsimple (les ~15 articles hors-sujet du diagnostic P5).

Ouverture : les secteurs viennent après que le pilier Manutention a ses familles complètes. Premier secteur ouvert : **agroalimentaire** (le plus gros gisement, le mieux monétisable via l'inox).

### Ce que ça donne comme périmètre

| Statut | Contenu |
|---|---|
| **Pilier 1 (ouvert maintenant)** | Manutention & levage |
| **Piliers 2-3 (phase 2)** | Stockage & intralogistique · Sécurité & réglementation |
| **Piliers 4-6 (phase 3+)** | Énergie & utilités · Automatisation & robotique · Maintenance industrielle |
| **Axe secteur (à partir de la phase 2)** | Agroalimentaire d'abord, puis pharmacie-chimie, automobile-transport, métallurgie, aéronautique, textile |
| **Transverses permanents** | Annuaire · Outils · Marques (les prix sont nestés sous chaque famille, cf. §3.2) |
| **Hors périmètre définitif** | Actualité industrielle et sectorielle, usinage/machine-outil, BTP, grand public, occasion |

---

## 3. Architecture et cocon sémantique

### 3.1 Les trois niveaux (loi de maillage)

Reprise directe de la structure validée sur pluscestsimple, corrigée de ses défauts constatés :

```
PILIER (intent large, /pilier/)
   ▲ remonte                          ▼ distribue
FAMILLE (intent moyen, /famille/)
   ▲ remonte                          ▼ distribue
ARTICLE (longue traîne, /famille/slug/)
```

| Page | Lie vers le bas | Lie vers le haut | Latéral |
|---|---|---|---|
| **Pilier** | ses familles + ses 6-12 meilleurs articles, via une boucle filtrée sur **son** silo | — | les autres piliers, 1 lien chacun |
| **Famille** | ses articles (boucle filtrée sur la famille) | son pilier, lien contextuel en intro **et** fil d'Ariane | les familles sœurs du même pilier |
| **Article** | — | sa famille **et** son pilier, liens contextuels dans le corps | 3-5 articles **du même silo** uniquement |

Principes non négociables (chacun corrige un défaut réel observé) :
1. **Un article = un silo.** Catégorie principale unique. Pas de multi-catégorisation cross-silo.
2. **Le latéral reste intra-silo.** Les articles liés d'un article chariot pointent vers du chariot, pas vers du rayonnage.
3. **Les liens remontants sont contextuels**, dans le texte, ancre descriptive — le fil d'Ariane ne suffit pas.
4. **Le pilier filtre réellement son silo.** Sur pluscestsimple, `/travaux/` et `/immobilier/` affichaient les 15 mêmes articles : zéro signal de silo. À vérifier en recette, pilier par pilier.
5. **Le fil d'Ariane est la vérité hiérarchique.** Sur pluscestsimple, `immobilier-cat` était imbriqué sous `architecture-cat` : fils d'Ariane faux, `BreadcrumbList` faux. Taxonomie construite propre, parents forcés.

### 3.2 Plan d'URL — préserver l'héritage sans le subir

Contrainte : les articles qui rankent encore vivent sous des préfixes racine (`/chariot-elevateur/{slug}/`, `/gerbeur/{slug}/`, `/transpalette/{slug}/`…). On ne les déplace pas.

Solution : **les familles occupent ces préfixes racine hérités, les piliers ont leur propre URL racine.** Le niveau 2→3 est nesté (propre), le niveau 1→2 est porté par le maillage et le fil d'Ariane (comme sur pluscestsimple, mais avec un maillage qui fonctionne).

```
/                                   accueil
/manutention/                       PILIER 1
  /chariot-elevateur/               famille (préfixe hérité, articles déjà nestés dessous)
  /levage/                          famille (hérité)
  /nacelle/                         famille (hérité — fusion de /nacelle/ + /nacelles-elevatrices/)
  /transpalette/                    famille (hérité)
  /gerbeur/                         famille (hérité)
  /pont-roulant/                    famille (hérité)
  /table-elevatrice/                famille (hérité)
  /diable-chariot/                  famille (hérité)
  /treuil-palonnier/                famille (hérité)
/stockage/                          PILIER 2 (hérité, phase 2)
  /rayonnage/                       famille (hérité)
/securite/                          PILIER 3 (hérité, phase 2)

PRIX — nestés sous leur famille, pas de hub racine
  /chariot-elevateur/prix/          « Prix d'un chariot élévateur »
  /nacelle/prix-location/           « Prix de location d'une nacelle »
  …une page prix par famille, jamais ailleurs

TRANSVERSES
/reglementation/                    hub — CACES, VGP, autorisation de conduite, obligations
/marque/{marque}/                   hub marques
/annuaire/                          hub annuaire
/location-manutention/{geo}/        croisements annuaire (voir §5)
/entreprise/{ville}-{slug}/         fiches établissement (pattern hérité)
/outils/{outil}/                    outil unique (voir §6)
/industries/{secteur}/              axe secteur
/industries/{secteur}/{famille}/    croisements secteur × famille
```

**Sur les prix.** Le hub racine `/prix/` est abandonné. Deux raisons, et la seconde est la vraie.

D'abord la lisibilité : `/chariot-elevateur/prix/` se lit naturellement, et le fil d'Ariane devient « Manutention › Chariot élévateur › Prix ».

Ensuite, et surtout : un hub `/prix/` transverse **crée mécaniquement la cannibalisation qu'on cherche à éviter**. Il devient une troisième page en concurrence avec la famille et avec le comparatif sur le même champ sémantique, hébergée en dehors du silo, donc sans lien hiérarchique pour arbitrer. En nestant la page prix sous sa famille, elle devient un enfant du silo : Google comprend la hiérarchie, la famille lui transmet son autorité, et la question « laquelle des deux doit ranker » ne se pose plus.

Le contenu, lui, est conservé : les requêtes prix sont les plus fortes en intention d'achat de toute la niche, et la SERP les récompense (Hellopro, VMAX et PagesJaunes maintiennent tous une page prix dédiée). C'est la maintenance qui était le vrai risque, et elle est traitée par la mécanique en base décrite dans le protocole de fiabilité.

Les piliers 4-6 (`/energie/`, `/automatisation/`, `/maintenance/`) s'ajoutent sans rien casser : le gabarit pilier existe, la taxonomie est prévue pour N piliers, la navigation a de la place.

### 3.3 Correction au plan de redirections

La SERP live du 2026-07-25 sur « meilleur chariot élévateur 2026 comparatif marques » fait remonter **notre propre page `/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/`** en page 1, aux côtés d'Experlift, louerchariotelevateur, Hellopro et chariotelevateur.net. Elle porte aussi 78 mots-clés historiques contre 33 pour `/chariot-elevateur/classement-des-marques-chariot-elevateur/`.

Le plan de redirections actuel fait l'inverse (il redirige la forte vers la faible). **À corriger : la page canonique du cluster marques devient `/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/`**, et `/chariot-elevateur/classement-des-marques-chariot-elevateur/` + `/fabricants-chariots-elevateurs/` redirigent vers elle. L'anomalie de préfixe (`/entreprise/` sert aussi aux fiches annuaire, en deux segments) est cosmétique et sans effet SEO ; le routage distingue `/entreprise/{slug}/` de `/entreprise/{ville}-{slug}/`.

---

## 4. Cannibalisation

Tu as raison de soulever le cas prix / meilleur. Vérification en SERP réelle :

| Requête | Top 10 constaté |
|---|---|
| **prix chariot élévateur** | Hellopro `/combien-coute-un-chariot-elevateur`, chariotelevateur.fr (VMAX, prix + TCO), Experlift guide d'achat, Mitsubishi |
| **meilleur chariot élévateur / comparatif marques** | Experlift guide d'achat, **infoweb-manutention (nous)**, louerchariotelevateur `/7-meilleures-marques`, Hellopro `/top-10-des-fournisseurs`, chariotelevateur.net |

**Verdict : deux intentions distinctes.** Les URLs gagnantes diffèrent, et l'acteur le plus mature de la SERP (Hellopro) maintient délibérément **deux pages séparées**. Un seul chevauchement : le guide d'achat d'Experlift ranke sur les deux — c'est une page hybride, et elle n'est première sur aucune des deux.

**Règle retenue : une page par intention, jamais de page hybride.**

| Intention | Page | Angle obligatoire | Interdit |
|---|---|---|---|
| Combien ça coûte | `/prix/chariot-elevateur/` | fourchettes par capacité/énergie, TCO, coûts annexes (VGP, batterie, maintenance) | classement de marques |
| Quelle marque/modèle | `/entreprise/le-classement-marques.../` | critères de choix, marques comparées, réseau SAV, disponibilité pièces | grille tarifaire détaillée |
| Quel type pour mon usage | `/chariot-elevateur/` (famille) | arbre de décision usage → type | ni prix détaillé ni classement |

Contrôle mensuel : sur chaque cluster, vérifier en GSC qu'une seule URL du site prend les impressions de la requête pivot. Deux URLs qui alternent = cannibalisation, on fusionne ou on désoptimise la plus faible. Ce contrôle vaut aussi pour les triplets famille / prix / annuaire (« location chariot élévateur » côté famille vs côté annuaire géo) : la famille traite le **choix**, l'annuaire traite le **où**.

---

## 5. L'annuaire — un seul, bien fait

### 5.1 Lequel

Quatre candidats, arbitrés sur volume × faiblesse SERP × valeur du lead × disponibilité des données.

| Candidat | Volume local | SERP | Lead | Verdict |
|---|---|---|---|---|
| **Loueurs de matériel** | Élevé | Kiloutou/Loxam (pages agence), PagesJaunes, petits agrégateurs (louerchariotelevateur.com, Maxiloc) | Élevé, récurrent | ✅ **Retenu** |
| Formation CACES | Le plus élevé | **Saturée** : caces.fr, OùFormer, MaFormation, JeTrouveMaFormation, ma-formation-manutention.fr | Bon | ❌ Trop tard |
| SAV / VGP | Faible | **Vide** (que des prestataires isolés) | Moyen | Phase 2, même base |
| Concessionnaires par marque | Moyen | Peu structurée | Très élevé | Phase 2, même base |

**L'annuaire n°1 est celui des loueurs de matériel de manutention et de levage.** Volume local réel, agrégateurs faibles et battables, valeur de lead la plus haute et récurrente, données GMB faciles à collecter, et surtout : c'est le seul qui alimente directement le pilier de monétisation « leads devis ».

Point de conception décisif : **on collecte les entités une seule fois, avec toutes leurs activités.** Un concessionnaire Fenwick fait généralement vente + location + SAV + VGP. La base stocke les quatre activités dès la vague 1 (le schéma `arw_etablissement_types` le prévoit déjà). Les croisements SAV/VGP et concessionnaires de la phase 2 se génèrent alors **sans nouvelle collecte** — et ils atterrissent sur la SERP VGP qui est vide.

### 5.2 Granularité — tu as raison sur les villes

Ma recommandation initiale « département par défaut » était calibrée sur la winnabilité, pas sur l'intention. L'intention est locale : on fait les trois niveaux.

Le raisonnement qui tranche : les grandes enseignes ne créent une page que là où elles ont une agence. Sur les villes moyennes industrielles sans agence Kiloutou/Loxam, la SERP est vide de tout acteur structuré. C'est là que les pages ville gagnent, et c'est là qu'elles sont les plus utiles.

### 5.3 Dimensionnement (cible : ~1 400 pages)

| Niveau | Calcul | Pages |
|---|---|---|
| Régions × 3 familles | 13 × 3 | 39 |
| Départements × 3 familles | 101 × 3 | 303 |
| Villes × 3 familles | ~120 villes retenues × 3, seuil ≥ 3 établissements | ~200 publiées |
| **Sous-total croisements** | | **~540** |
| Fiches établissements | seulement celles avec données complètes | ~850 |
| **Total annuaire** | | **~1 390** |

Les 3 familles : chariot élévateur · nacelle · transpalette-gerbeur. Pas plus — au-delà, le contenu local devient impossible à différencier.

Sélection des villes : densité industrielle et logistique, pas seulement la population. Priorité aux villes moyennes sans agence de grande enseigne. Une ville n'ouvre que si elle compte ≥ 3 établissements réels ; sinon la requête redirige vers le département.

Règles anti-contenu pauvre (inchangées, elles sont bonnes) : seuil de 3 établissements, bloc local unique et non templatisé (fourchette tarifaire locale, contraintes ZFE, tissu économique), FAQ contextualisée, sitemaps segmentés, suivi du ratio indexées/publiées avec seuil d'alerte.

### 5.4 Publication par lots de 100

Aucune mise en ligne massive. Les pages partent par lots de 100, et le lot suivant n'est lancé qu'après mesure du précédent.

Mécanique, implémentée dans `arw_croisements` et `arw_lots_publication` :

1. **Anti-doublon garanti par la base.** La clé unique `(type, cle, niveau, region_slug, departement, ville_slug)` rend structurellement impossible la création de deux pages pour la même ville et la même famille. Un ré-import fait un `UPDATE`, jamais un `INSERT` — quelle que soit la vague, quel que soit le nombre de passages.
2. **Éligibilité avant publication.** Une page ne passe en `pret` que si elle compte au moins 3 établissements et possède son bloc de contenu local. Les autres restent en `brouillon` et ne sont ni publiées ni indexées.
3. **Attribution du lot.** L'écran d'administration sélectionne les 100 pages `pret` les plus prioritaires (densité d'établissements décroissante), leur assigne un numéro de lot, les passe en `publie` et les inscrit au sitemap.
4. **Mesure à J+30.** On relève le nombre de pages du lot réellement indexées et on l'inscrit dans `arw_lots_publication.indexees_j30`. En dessous de 70 % d'indexation, on ne lance pas le lot suivant : on corrige d'abord la qualité des blocs locaux.

Ce rythme est ce qui distingue un annuaire qui s'indexe d'un annuaire qui se fait ignorer en bloc.

---

## 6. L'outil unique — sélecteur d'engin de manutention

### 6.1 Correction : le sélecteur CACES est écarté

Ma première recommandation était un sélecteur CACES. Vérification faite, elle était mauvaise pour trois raisons.

**Il existe déjà.** `travail-industrie.com/outils/decisions/quel-caces-pour-mon-engin` — « Quel CACES pour mon engin ? R482 R486 R489 [Guide 2026] ». Le site est édité par LSEA SAS (Lille), avec une équipe HSE/ATEX, une publication quotidienne, un jobboard, des simulateurs de paie, des quiz CACES et des pages habilitation. C'est un acteur installé, et le CACES est son cœur de métier, pas un projet annexe.

**Ce n'est pas notre lecteur.** travail-industrie.com s'adresse aux salariés, aux RH et aux QHSE. Notre lecteur est celui qui achète et exploite le matériel. Aller les affronter sur le CACES, c'est se battre sur leur terrain avec leur audience.

**C'est notre contenu le plus risqué juridiquement.** Un outil qui affirme « il vous faut le CACES R489 catégorie 3 » engage bien plus qu'un article. Le premier outil du site ne doit pas être celui qui concentre le risque maximal.

Le contenu CACES reste évidemment au programme — c'est notre plus gros cluster de volume — mais sous forme d'articles réglementaires encadrés par le protocole de fiabilité, pas d'outil.

### 6.2 L'outil retenu, et pourquoi

Vérification des candidats en SERP réelle :

| Candidat | Existe déjà ? | Notre lecteur ? | Risque | Verdict |
|---|---|---|---|---|
| Sélecteur CACES | Oui (travail-industrie.com) | Non (RH/QHSE) | Juridique élevé | ❌ |
| Capacité résiduelle | Oui (Toyota, Jungheinrich, KAUP, CAM attachments) | Oui | **Sécurité physique** — le calcul générique reste une approximation, seul le constructeur donne la valeur exacte | ❌ |
| TCO achat/location | Oui (VMAX/chariotelevateur.fr) | Oui | Faible | Module de conversion, pas outil phare |
| **Sélecteur d'engin** | **Non — que des articles** | **Oui, exactement** | **Faible** | ✅ **Retenu** |

La SERP de « quel engin choisir / transpalette ou gerbeur ou chariot élévateur » est pleine : TAWI, Mecalux, Experlift, Logismarket, Chariotech, plus des blogs généralistes. Beaucoup d'articles signifie beaucoup de demande. **Et pas un seul outil interactif.** Tout le monde explique la différence en prose ; personne ne pose les trois questions qui donnent la réponse.

C'est aussi le seul candidat qui coche les quatre critères à la fois : il sert notre lecteur exactement au moment de la décision, il n'existe pas, il ne porte aucun risque réglementaire ou sécuritaire sérieux (il recommande une catégorie de matériel, pas une obligation légale ni une limite de charge), et il alimente les deux voies de monétisation — petit matériel vers l'affiliation, gros matériel et location vers le devis.

### 6.3 Spécification de la page

**URL** : `/outils/quel-engin-de-manutention/`
**Title** : `Quel engin de manutention choisir ? Transpalette, gerbeur ou chariot élévateur`
**H1** : `Quel engin de manutention vous faut-il ?`
**Meta description** : `Répondez à 4 questions — charge, hauteur, fréquence, environnement — et obtenez le type d'engin adapté, avec les fourchettes de prix et les alternatives.`

Quatre questions, pas une de plus : charge maximale · hauteur de levée · fréquence d'usage · environnement (intérieur/extérieur, sol, température, agroalimentaire). Le résultat donne le type d'engin recommandé, la ou les alternatives à considérer, la fourchette de prix indicative issue de `arw_prix`, la contrainte réglementaire associée (recommandation CACES applicable, en renvoyant à l'article dédié plutôt qu'en tranchant), et le CTA adapté au résultat.

Exigences SEO — l'outil seul ne se positionne pas, c'est le contenu rendu côté serveur qui porte la page :
- Contenu rédactionnel complet en HTML server-side : tableau comparatif des familles (charge, hauteur, prix, usage type, CACES applicable), arbre de décision en texte, cas d'usage détaillés, limites de chaque type.
- `Schema.org` : `FAQPage`, `HowTo`, `BreadcrumbList`.
- URLs de résultat partageables en paramètres, `canonical` vers la page mère.
- JS vanilla, zéro dépendance externe, chargé uniquement sur cette page, aucun impact LCP.

Conversion, selon le résultat : transpalette, gerbeur manuel ou diable → liens affiliés ; chariot élévateur, nacelle ou gros gerbeur → formulaire de devis, avec pré-remplissage des critères saisis ; toute réponse → lien vers l'annuaire des loueurs du département si l'usage déclaré est ponctuel.

Maillage : lié depuis l'accueil, depuis `/manutention/` et depuis chaque page famille. Actif à liens : c'est le type d'outil que les blogs logistique, les sites d'aménagement d'entrepôt et les forums pro citent volontiers.

Les deux autres outils (capacité résiduelle, TCO) restent en réserve, sans date.

---

## 7. Le thème

### 7.1 Ce qu'on reprend de pluscestsimple

Le thème pluscestsimple 2.24.1 contient des briques mûres, directement portables :

| Brique | Fichier source | Usage ici |
|---|---|---|
| Headers de sécurité, anti-énumération d'auteurs, rate-limit formulaires | `inc/security.php` | Tel quel |
| Performance, images WebP/lazy | `inc/performance.php`, `inc/image.php` | Tel quel |
| SEO (title, meta, robots) + Schema | `inc/seo.php`, `inc/schema.php`, `inc/structured-data.php` | Adapté (Product → LocalBusiness pour l'annuaire) |
| Fil d'Ariane | `inc/breadcrumbs.php` | Tel quel |
| Liens affiliés | `inc/affiliate.php` | Étendu : table centralisée + `/go/{slug}` |
| Formulaires | `inc/form.php` | Étendu : brique leads |
| Consentement cookies | `inc/cookie-consent.php` | Tel quel |
| Gabarit produit affilié | `single-pcs_produit.php`, `inc/produits.php` | Adapté |
| Partenariats / visibilité payante | `inc/partners.php` | Adapté : fiches premium |
| Temps de lecture, newsletter | `inc/reading-time.php`, patterns newsletter | Tel quel |
| Carte produit (note, prix, marque) | patterns | Adapté au matériel pro |
| Outil dans la navigation | pattern Compatibilimètre | Modèle pour le sélecteur CACES |

### 7.2 Ce qu'on élimine — les six fautes documentées

L'audit du cocon de pluscestsimple (juin 2026) liste des défauts structurels précis. Ils deviennent des contraintes de conception.

| Défaut constaté | Correction imposée ici |
|---|---|
| **Contenu des patterns copié en base au premier seed** → modifier le fichier ne met plus à jour la page ; toute correction demande une migration | **Les pages piliers et familles sont rendues par gabarit PHP, jamais seedées en base.** Le fichier est la source de vérité, l'édition prend effet immédiatement. Le contenu éditable en back-office reste dans des champs dédiés, pas dans du markup de blocs. |
| **Boucle des piliers non filtrée** → tous les piliers affichaient les mêmes 15 derniers articles du site | Boucle filtrée par catégorie du silo, en dur dans le gabarit. Recette obligatoire : deux piliers ne doivent jamais afficher la même liste. |
| **Hiérarchie de catégories corrompue** par un renommage de taxonomie héritée | Taxonomie créée propre, parents forcés, contrôle d'intégrité au chargement. |
| **Piliers ne liant que latéralement** vers les autres piliers | Le gabarit pilier impose les liens descendants : familles + articles phares du silo. |
| **Articles sans lien remontant éditorial**, maillage latéral dépendant d'un plugin fantôme | Le gabarit article impose « À lire dans le même univers » (même silo) + lien contextuel vers famille et pilier. Zéro dépendance plugin. |
| **Articles hors-sujet diluant l'autorité** | Règle éditoriale du §2 appliquée à l'entrée. |

Éliminé également : les URLs d'articles à plat (ici tout est nesté sous la famille), et la multiplication des entrées de menu.

### 7.3 Les gabarits

Neuf gabarits couvrent tout le site. C'est le maximum acceptable — au-delà, on ne les maintient plus.

| # | Gabarit | Rôle | Blocs signature |
|---|---|---|---|
| 1 | **Accueil** | Orientation, pas magazine | Hero pilier actif · accès direct annuaire · accès outil · 6-8 articles de référence · newsletter |
| 2 | **Pilier** | Autorité thématique | Intro · grille des familles · boucle filtrée sur le silo · FAQ · liens latéraux piliers |
| 3 | **Famille** | Guide d'achat, monétisation | Arbre de décision usage → type · tableau comparatif · fourchettes de prix · boucle articles de la famille · **un seul CTA** (affiliation *ou* devis, jamais les deux) |
| 4 | **Article** | Longue traîne | Sommaire ancré · corps · encadré normatif · liens remontants contextuels · « À lire dans le même univers » (même silo) |
| 5 | **Comparatif « les meilleurs… »** | Affiliation | Verdict en tête · tableau comparatif · cartes produit (note, prix daté, marque, `/go/`) · critères de choix · mention affiliation |
| 6 | **Fiche produit affilié** | Affiliation | Caractéristiques structurées · prix indicatif daté · alternatives · CTA marchand |
| 7 | **Prix** | Intention d'achat | Fourchettes datées par configuration · tableau TCO · coûts annexes · CTA devis |
| 8 | **Annuaire — croisement géo** | Trafic local + leads | Liste filtrable · carte · **bloc local unique** · FAQ contextualisée · CTA devis |
| 9 | **Annuaire — fiche établissement** | Leads + revendication | Coordonnées · activités · zone · `LocalBusiness` · CTA devis · « revendiquer cette fiche » |

Plus les pages système (mentions légales, contact, à propos/auteur, transparence, confidentialité) sur un gabarit sobre commun.

### 7.4 Mobile-first, simplicité, sécurité, performance

**Mobile-first, réellement.** Conception à 360 px d'abord, élargissement ensuite. Cibles concrètes : typographie fluide sans média-queries de police, tableaux comparatifs en `overflow-x` avec première colonne figée (jamais de tableau qui casse la page), formulaires de devis à un champ par ligne et clavier adapté par type de champ, zones tactiles ≥ 44 px, filtres d'annuaire en tiroir plein écran sur mobile plutôt qu'en barre latérale compressée.

**KISS.** Navigation à **5 entrées** au lancement : Manutention · Réglementation · Prix · Annuaire · Sélecteur CACES. Un seul niveau de déroulant, uniquement sous Manutention pour lister les familles. Pas de méga-menu, pas de barre latérale, pas de widgets. Chaque pilier ajouté prend une entrée ; à 7 entrées, on regroupe. Un seul CTA visible par page — c'est la règle de monétisation du brief traduite en règle d'interface.

**Sécurité.** Reprise de `inc/security.php` (CSP, HSTS, `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, blocage de l'énumération d'auteurs, neutralisation des messages d'erreur de connexion, rate-limit des formulaires publics). S'ajoutent, spécifiques à ce site : requêtes préparées systématiques sur les tables `arw_*` (l'annuaire manipule des entrées utilisateur en filtres), nonces et vérification de capacité sur toutes les actions d'administration d'import et de modération, échappement en sortie sur toutes les données d'annuaire (elles viennent d'un import externe), validation stricte et vérification par e-mail des revendications de fiche, et hachage des IP et numéros appelants côté leads. Un passage `/audit-securite-wp` avant mise en production.

**Performance.** Zéro JavaScript par défaut ; le sélecteur CACES et la carte de l'annuaire sont les deux seules exceptions, chargées uniquement sur leur page. CSS critique inliné, budget CSS < 50 ko. Aucune police externe. Images WebP, `loading="lazy"`, dimensions explicites. Cache objet et cache page pour l'annuaire. Cible Lighthouse 100/100/100/100, LCP < 1,5 s en 4G simulée.

---

## 8. Ce que ça change au plan de travail

1. **Plan de redirections** — inverser le sens du cluster marques (§3.3). Fait, régénéré.
2. **Phase 0 inchangée** — les 69 pages à recréer sont toutes dans le pilier Manutention. Le pivot ne retarde rien.
3. **Effort éditorial** — réallouer du pilier vers l'article. Les pages piliers sont construites une fois, proprement, et ne sont plus retouchées (§1.5). Le budget de production va aux articles de décision, à l'annuaire et à l'outil.
4. **Thème** — spécifié par les 9 gabarits ; construit après la remise en ligne, sur un WordPress déjà en production.
5. **Annuaire** — un seul, loueurs, ~1 390 pages, publication par lots de 100 avec mesure d'indexation à J+30, collecte GMB en séance commune, entités multi-activités dès la vague 1.
6. **Outil** — sélecteur d'engin de manutention. CACES écarté (concurrent installé, risque juridique), capacité résiduelle et TCO en réserve.
7. **Prix** — pages nestées sous chaque famille, données en base plafonnées à 40-60 points, deux révisions par an.
8. **Fiabilité** — `protocole-fiabilite.md` contraignant, `scripts/lint_reglementaire.py` bloquant avant publication.
9. **Secteurs** — second axe du cocon, ouvert en phase 2, agroalimentaire en premier.
10. **Piliers 2-6** — ouverts un par un, jamais avant que le précédent ait ses familles complètes et son maillage vérifié.
