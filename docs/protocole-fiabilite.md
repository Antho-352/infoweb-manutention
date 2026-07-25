# Protocole de fiabilité — réglementation et prix

Deux domaines où une erreur coûte cher : le réglementaire (risque juridique et de crédibilité) et les prix (risque de crédibilité et de maintenance). Ce document est contraignant pour toute production, humaine ou par agent.

---

## 1. Réglementaire

### 1.1 Le risque, concrètement

L'erreur type de la niche, présente sur la majorité des sites concurrents : **« le CACES est obligatoire »**. C'est faux. Ce qui est obligatoire, c'est l'**autorisation de conduite** délivrée par l'employeur (articles R4323-55 à R4323-57 du Code du travail). Le CACES est un dispositif d'évaluation reconnu, pas une obligation légale.

Un site qui se trompe là-dessus perd toute crédibilité auprès d'un préventeur ou d'un responsable QHSE — c'est-à-dire auprès du lecteur qu'on vise, et auprès des annonceurs qu'on veut signer.

### 1.2 Sources primaires — les seules autorisées

| Sujet | Source primaire | Usage |
|---|---|---|
| Obligations employeur, autorisation de conduite | **Légifrance**, articles R4323-55 à R4323-57 | Citation de l'article, jamais de paraphrase de mémoire |
| Vérifications générales périodiques | **Arrêté du 1er mars 2004** (Légifrance) | Périodicités exactes par type d'appareil |
| Recommandations CACES | **Assurance Maladie – Risques professionnels (ameli.fr)**, PDF officiels des recommandations R482, R483, R484, R485, R486, R487, R489, R490 | Catégories, intitulés, durées de validité |
| Questions d'application CACES | **INRS**, FAQ CACES (PDF daté, actuellement édition 6) | Cas limites, équivalences |
| Prévention, fiches techniques | **INRS** (dossiers, ED) | Bonnes pratiques |

Sont **interdits comme source** : les sites d'organismes de formation, les blogs de constructeurs, les autres médias, et la mémoire du modèle. Ils peuvent servir à repérer un sujet, jamais à établir un fait.

### 1.3 Chaîne de production obligatoire

Aucun agent n'écrit de contenu réglementaire de mémoire. La séquence est :

1. **Récupération** de la source primaire (fetch du PDF ou de l'article Légifrance) et stockage local dans `sources/reglementaire/{sujet}-{date}.{pdf,txt}`.
2. **Extraction** des affirmations : chaque fait candidat est écrit avec sa référence exacte (article, arrêté, recommandation, page).
3. **Rédaction** à partir de l'extraction uniquement, jamais à partir d'une recherche web.
4. **Vérification indépendante** : un second agent, qui n'a pas rédigé, reprend chaque affirmation réglementaire et la confronte à la source locale. Il ne valide pas « globalement » : il produit une ligne par affirmation avec verdict.
5. **Lint automatique** (`scripts/lint_reglementaire.py`) : détecte les motifs réglementaires sans source adjacente, et les formulations interdites.
6. **Relecture humaine** par Anthony sur les pages à risque maximal avant publication : CACES, autorisation de conduite, VGP, obligations employeur.

### 1.4 Fiche de vérification

Chaque page réglementaire porte, en base et non dans le texte visible, une fiche :

```
page: /reglementation/vgp/
affirmations:
  - claim: "VGP semestrielle pour les appareils de levage mobiles"
    source: "Arrêté du 1er mars 2004, art. 23"
    fichier: sources/reglementaire/arrete-2004-03-01-2026-07.pdf
    verifie_le: 2026-07-25
    verifie_par: agent-reviewer
  - ...
revue_prevue: 2027-07-25
```

### 1.5 Formulations interdites / imposées

| Interdit | Imposé |
|---|---|
| « le CACES est obligatoire » | « l'autorisation de conduite est obligatoire (R4323-56) ; le CACES est le moyen le plus courant d'évaluer les connaissances requises » |
| « environ tous les 6 mois » | la périodicité exacte avec sa référence |
| « la loi impose… » sans référence | « l'article R4323-XX impose… » |
| « nous vous conseillons juridiquement » | « à vérifier avec votre préventeur / l'inspection du travail » |
| Toute affirmation réglementaire sans référence | Référence systématique |

### 1.6 Mentions obligatoires

Sur toute page réglementaire, en pied de contenu :

> Information à caractère général, à jour au {date}. Elle ne constitue pas un conseil juridique et n'engage pas la responsabilité de l'éditeur. Les obligations applicables dépendent de votre situation : rapprochez-vous de votre service prévention, de votre organisme de contrôle ou de l'inspection du travail.

Et la date de dernière vérification, visible, avec la source citée en lien.

### 1.7 Cadence de revue

Revue annuelle systématique. Revue immédiate en cas de publication d'une nouvelle recommandation ou d'une modification du Code du travail. Le tableau de bord d'administration liste les pages dont la `revue_prevue` est dépassée.

---

## 2. Prix

### 2.1 Le problème posé

Des prix réels et datés sont notre meilleur différenciateur — c'est ce que personne ne publie et ce que tout acheteur cherche. Mais un prix se périme, et un prix faux décrédibilise autant qu'une erreur réglementaire. Sans mécanique de maintenance, cette promesse devient une dette.

### 2.2 La réponse : les prix sont des données, jamais de la prose

**Aucun prix n'est écrit en dur dans un article.** Tous les prix vivent dans la table `arw_prix` et sont injectés dans les pages par un bloc dédié. Conséquence : une révision met à jour toutes les pages qui citent ce prix, en une écriture.

C'est ce qui rend la promesse tenable. Sans ça, réviser les prix voudrait dire rouvrir cent articles.

### 2.3 Volume maîtrisé

**40 à 60 points de référence maximum**, pas davantage. Un point de référence = une famille × une configuration (exemple : « chariot élévateur électrique 2 t, neuf, achat » ou « nacelle ciseaux électrique 10 m, location journée »).

À raison de deux révisions par an, c'est 80 à 120 vérifications annuelles, soit environ deux heures par mois. C'est tenable. Trois cents points ne le seraient pas.

### 2.4 Ce qu'on publie et ce qu'on ne publie pas

| On publie | On ne publie pas |
|---|---|
| Des **fourchettes** (de X à Y € HT) | Un prix unique présenté comme LE prix |
| La **date de constat** visible | Un prix non daté |
| La **source** du relevé | Un prix sans origine |
| Le périmètre (hors options, hors transport, hors mise en service) | Un prix « tout compris » |
| Des ordres de grandeur de coûts annexes (VGP, batterie, maintenance) | Des devis nominatifs de partenaires |

### 2.5 Sources de relevé, par ordre de préférence

1. Tarifs publics de marchands en ligne (Manutan, ManoMano, Experlift, Contorion) — vérifiables, réutilisables comme lien affilié.
2. Grilles de location publiées par les loueurs (Kiloutou, Loxam, Newloc, Locmachine).
3. Devis réellement reçus via nos formulaires, anonymisés et agrégés — **jamais un devis isolé**, minimum trois pour publier une fourchette. C'est cette source qui alimentera le baromètre.
4. Publications constructeurs.

### 2.6 Mention obligatoire

Sous chaque bloc de prix :

> Fourchette indicative HT constatée le {date}, hors options, transport et mise en service. Ne constitue pas une offre commerciale. Les prix varient selon la configuration, le volume et le fournisseur.

### 2.7 Alerte de péremption

Le tableau de bord d'administration affiche les points de prix non révisés depuis plus de 6 mois. Au-delà de 12 mois sans révision, le bloc affiche automatiquement un avertissement de fraîcheur côté public, ou masque le prix — le choix se règle par point.

---

## 3. Encadrement des agents

Les agents hallucinent, en particulier sur des références réglementaires qui « ressemblent » à des vraies (un numéro d'article plausible mais faux est le mode de défaillance le plus dangereux, parce qu'il est crédible).

Règles de brief, à recopier dans chaque mission d'agent touchant au réglementaire ou au prix :

1. **Interdiction d'écrire un chiffre ou une référence qui n'est pas présent dans un fichier source local fourni.** Pas de recherche web pour établir un fait.
2. **Obligation de marquer `[À VÉRIFIER]`** plutôt que de combler un trou. Une page ne part jamais en ligne avec un `[À VÉRIFIER]` restant.
3. **Le rédacteur et le vérificateur sont deux agents distincts**, et le vérificateur reçoit les sources, pas le brouillon seul.
4. **Le vérificateur rend un verdict par affirmation**, jamais un avis global. « Le contenu semble correct » est un rapport rejeté.
5. **Modèle** : Sonnet minimum pour rédaction et vérification réglementaire. Haiku est réservé à la collecte de données structurées.
6. **Lint avant publication**, sans exception.

Cohérent avec les règles déjà établies sur les autres projets : vérifier avant de livrer, ne jamais inventer de fait, choisir le modèle selon la phase.
