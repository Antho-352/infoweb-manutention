# Obtenir la clé API Places — pas à pas

Compte 15 minutes. Une carte bancaire est demandée à l'ouverture du compte, mais l'étape 6 pose un plafond qui rend tout prélèvement impossible.

---

## Étape 1 — Ouvrir la console

Va sur **console.cloud.google.com** et connecte-toi avec un compte Google. Prends de préférence un compte dédié au projet plutôt que ton compte personnel.

## Étape 2 — Créer un projet

1. En haut à gauche, à côté du logo Google Cloud, clique sur le sélecteur de projet
2. **Nouveau projet**
3. Nom : `infoweb-manutention`
4. **Créer**, puis attends que le projet soit sélectionné en haut de l'écran

## Étape 3 — Activer la facturation

C'est obligatoire même pour rester dans le gratuit : Google exige une carte pour vérifier que tu n'es pas un robot, mais ne prélève rien tant que tu es sous les quotas.

1. Menu de gauche → **Facturation**
2. **Associer un compte de facturation** → **Créer un compte de facturation**
3. Renseigne tes informations et ta carte
4. Valide

## Étape 4 — Activer l'API Places

1. Menu de gauche → **API et services** → **Bibliothèque**
2. Cherche **Places API (New)** — attention, prends bien celle avec la mention *(New)*, pas l'ancienne
3. Clique dessus, puis **Activer**

## Étape 5 — Créer la clé

1. **API et services** → **Identifiants**
2. **Créer des identifiants** → **Clé API**
3. La clé s'affiche. Copie-la.
4. Clique sur **Modifier la clé API** juste en dessous
5. Section **Restrictions relatives aux API** → coche **Restreindre la clé** → sélectionne uniquement **Places API (New)**
6. **Enregistrer**

Cette restriction fait que la clé ne peut rien faire d'autre que ce à quoi elle sert. Si elle fuite un jour, elle est inutilisable ailleurs.

## Étape 6 — Poser le plafond qui protège

C'est l'étape qui rend le dépassement impossible. Le palier Enterprise donne 1 000 appels gratuits par mois, soit environ 33 par jour.

1. **API et services** → **Places API (New)** → onglet **Quotas et limites du système**
2. Cherche la ligne de requêtes par jour
3. Clique sur l'icône de modification et fixe la limite à **30**
4. **Enregistrer**

Au-delà de 30 appels dans une journée, Google refuse la requête au lieu de la facturer. Tu ne peux structurellement pas dépasser le gratuit.

Ajoute une alerte par sécurité : **Facturation** → **Budgets et alertes** → **Créer un budget** → montant **1 €** → alerte à 100 %. Tu seras prévenu au premier centime, s'il devait y en avoir un.

## Étape 7 — Déclarer la clé

Dans le Terminal, en remplaçant par ta clé :

```bash
echo 'export GOOGLE_PLACES_API_KEY="ta_cle_ici"' >> ~/.zshrc && source ~/.zshrc
```

Vérifie qu'elle est bien prise en compte :

```bash
echo $GOOGLE_PLACES_API_KEY
```

Si la clé s'affiche, c'est bon. Dis-le-moi et je lance l'enrichissement sur la Moselle.

---

## Si ça bloque

| Symptôme | Cause | Correction |
|---|---|---|
| `REQUEST_DENIED` | L'API n'est pas activée sur le projet | Refaire l'étape 4 |
| `API key not valid` | La clé est restreinte à une autre API | Étape 5, point 5 |
| `RESOURCE_EXHAUSTED` | Plafond quotidien atteint | Normal : reprendre demain, le script garde son cache |
| Facturation refusée | Carte non acceptée | Google accepte mal certaines cartes virtuelles ; essayer une carte classique |
