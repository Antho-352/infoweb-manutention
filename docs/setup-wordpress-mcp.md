# Connecter le WordPress d'infoweb-manutention.fr à Claude Code

Objectif : me permettre de créer et modifier les pages du site directement, sans que tu aies à copier-coller du contenu. Le mot de passe n'est jamais visible dans la conversation.

Compte 10 minutes. Tu peux t'arrêter entre deux étapes et reprendre plus tard.

---

## Étape 1 — Vérifier que le plugin est actif

1. Va sur `https://infoweb-manutention.fr/wp-admin`
2. Menu de gauche → **Extensions** → **Extensions installées**
3. Cherche **WordPress MCP** (l'extension d'Automattic que tu as installée)
4. Si le lien sous son nom dit « Activer », clique dessus. S'il dit « Désactiver », c'est déjà bon.

---

## Étape 2 — Activer le MCP dans les réglages du plugin

1. Dans le menu de gauche du tableau de bord, ouvre les réglages du plugin (selon la version : **Réglages → WordPress MCP**, ou une entrée **MCP** directement dans le menu)
2. Coche la case qui active le serveur MCP — elle s'appelle en général **Enable MCP functionality** ou **Activer le MCP**
3. Coche aussi l'autorisation d'écriture — libellée **Enable create/update tools** ou équivalent. Sans elle je peux lire le site mais pas créer de page.
4. Clique sur **Enregistrer**

---

## Étape 3 — Créer le mot de passe d'application

C'est un mot de passe spécial, différent du tien, qui ne sert qu'à cette connexion et que tu peux révoquer à tout moment sans changer ton vrai mot de passe.

1. Menu de gauche → **Comptes** (ou **Utilisateurs**) → **Profil**
2. Descends tout en bas jusqu'à la section **Mots de passe d'application**
3. Dans le champ **Nom du nouveau mot de passe d'application**, tape : `claude-code`
4. Clique sur **Ajouter un nouveau mot de passe d'application**
5. WordPress affiche un mot de passe du type `abcd EFGH 1234 ijkl MNOP 5678`

⚠️ **Il ne s'affiche qu'une seule fois.** Copie-le tout de suite. Les espaces font partie du mot de passe, garde-les.

Si tu quittes la page sans le copier, ce n'est pas grave : supprime la ligne `claude-code` et refais l'étape 3.

---

## Étape 4 — Déclarer le serveur dans Claude Code

C'est ici que le mot de passe est stocké. Il reste dans un fichier de config sur ton Mac. **Ne le colle pas dans la conversation.**

Ouvre le Terminal et lance cette commande, en remplaçant les deux valeurs entre chevrons :

```bash
claude mcp add-json -s user infoweb-wp '{"command":"npx","args":["-y","@automattic/mcp-wordpress-remote@latest"],"env":{"WP_API_URL":"https://infoweb-manutention.fr/","WP_API_USERNAME":"<TON_IDENTIFIANT_WP>","WP_API_PASSWORD":"<LE_MOT_DE_PASSE_DE_LETAPE_3>"}}'
```

⚠️ **Le `-s user` n'est pas optionnel.** Sans lui, le serveur est enregistré en portée « locale », c'est-à-dire **rattaché au dossier depuis lequel tu lances la commande**. Si tu es dans `~` et que la session Claude tourne dans `~/manutention`, elle ne le verra jamais — et `claude mcp list` affichera « already exists » d'un côté et rien de l'autre. Avec `-s user`, le serveur est visible depuis tous les dossiers.

- `<TON_IDENTIFIANT_WP>` : ton nom d'utilisateur de connexion à WordPress (pas ton email, sauf si tu te connectes avec)
- `<LE_MOT_DE_PASSE_DE_LETAPE_3>` : le mot de passe d'application, espaces compris, entre les guillemets

Le nom `infoweb-wp` est l'étiquette du serveur. Garde-le tel quel, c'est celui que j'utiliserai.

---

## Étape 5 — Vérifier

Toujours dans le Terminal :

```bash
claude mcp list
```

Tu dois voir `infoweb-wp` dans la liste. S'il apparaît avec un statut de connexion réussie, c'est terminé.

---

## Étape 6 — Redémarrer la session

Les outils d'un nouveau serveur MCP ne se chargent qu'au démarrage d'une session. Ferme la session Claude Code en cours et relance-la.

Dis-moi simplement « le MCP est en place » et j'enchaîne : dépôt du mu-plugin de redirections, création des pages cibles, puis recréation des 69 pages par lots.

---

## En cas de problème

| Symptôme | Cause probable | Correction |
|---|---|---|
| `infoweb-wp` absent de `claude mcp list`, mais « already exists » quand tu l'ajoutes | Il a été enregistré en portée locale, rattaché à un autre dossier | Le rebasculer en portée utilisateur : `claude mcp add-json -s user infoweb-wp '{...}'` |
| `infoweb-wp` absent de `claude mcp list` | La commande de l'étape 4 a échoué | Relance-la, vérifie que les guillemets simples encadrent bien tout le bloc JSON |
| `/wp-json/` renvoie 404 alors que le site répond | Permaliens en mode « Simple » | Réglages → Permaliens → **Nom de l'article** → Enregistrer |
| Erreur d'authentification | Identifiant ou mot de passe incorrect | Refais l'étape 3 avec un nouveau mot de passe, puis `claude mcp remove infoweb-wp` et refais l'étape 4 |
| Connexion OK mais je ne peux rien créer | L'autorisation d'écriture n'est pas cochée | Retourne à l'étape 2, point 3 |
| Erreur 401 alors que tout semble bon | Certains hébergeurs filtrent l'en-tête d'authentification | Dis-le-moi, on passe par une autre méthode (SFTP ou WP-CLI) |

**Pour révoquer l'accès à tout moment** : Comptes → Profil → Mots de passe d'application → supprimer la ligne `claude-code`. La connexion est coupée immédiatement.
