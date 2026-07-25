# infoweb-manutention.fr — reconstruction

Média professionnel manutention/levage sur domaine expiré. Monétisation : affiliation petit matériel, leads devis (gros matériel + location), articles sponsorisés.

## Documents

| Fichier | Contenu |
|---|---|
| [docs/strategie-media-industrie.md](docs/strategie-media-industrie.md) | **Document directeur** — positionnement média de décision, cocon, cannibalisation, annuaire, outil unique, gabarits de thème |
| [docs/analyse-critique-brief.md](docs/analyse-critique-brief.md) | Analyse du brief initial : corrections factuelles, recherche SERP/concurrence/affiliation, désaccords, objectifs |
| [docs/roadmap.md](docs/roadmap.md) | Phasage opérationnel et décisions actées (2026-07-24) |
| [docs/arborescence.md](docs/arborescence.md) | Arborescence cible et plan de nommage d'URL |
| [docs/schema-bdd.sql](docs/schema-bdd.sql) | Schéma MySQL annuaire + leads + affiliation (tables `arw_*`) |
| [docs/protocole-fiabilite.md](docs/protocole-fiabilite.md) | **Contraignant** — vérification réglementaire (sources primaires, double agent, lint) et gestion des prix |
| [docs/persona-redacteur.md](docs/persona-redacteur.md) | Persona rédacteur (Denis Verhaeghe) — 3 zones pour l'outil de génération |
| [docs/setup-wordpress-mcp.md](docs/setup-wordpress-mcp.md) | Procédure pas à pas pour connecter le WordPress à Claude Code |

## Données

| Fichier | Contenu |
|---|---|
| `data/redirections-exactes.csv` | Plan par URL : 69 RECREATE / 71 × 301 / 4 × 410 |
| `data/plan-urls-a-traiter.csv` | Source : 144 URLs avec trafic, mots-clés, volumes |
| `data/wayback_cdx.txt` | 3 578 URLs archivées (CDX brut) |
| `data/infoweb-manutention-fr_*.csv` | Exports Ahrefs du 2026-07-24 |
| `content/wayback/` | Snapshots des pages prioritaires (matière première de réécriture — ne jamais republier tel quel) |

## Code

| Fichier | Contenu |
|---|---|
| `wp/mu-plugins/arw-legacy-redirects.php` | Mu-plugin de redirections héritées — généré par `scripts/build_redirects.py`, ne pas éditer à la main |
| `scripts/build_redirects.py` | Génère le CSV de redirections + le mu-plugin |
| `scripts/fetch_wayback.py` | Récupère les snapshots Wayback des pages à recréer |
| `scripts/lint_reglementaire.py` | Lint bloquant avant publication : erreurs réglementaires, références inventées, prix en dur |
