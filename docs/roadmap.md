# Roadmap — infoweb-manutention.fr

Décisions actées le 2026-07-24 : phase 0 immédiate ; base GMB à collecter ensemble plus tard (agent de collecte à créer à ce moment-là) ; netlinking et liens réseau gérés par Anthony ; pipeline de rédaction par agents validé ; mentions légales standard ; pas d'occasion (ferme).

## Phase 0 — Remise en ligne (en cours)

Objectif : stopper l'hémorragie des ~330 positions résiduelles. Tout est prêt côté fichiers ; il manque une instance WordPress.

| # | Action | Qui | Statut |
|---|---|---|---|
| 0.1 | Installer un WordPress nu sur le compte cPanel du domaine (WP Toolkit ou Softaculous), permaliens `/%postname%/`, HTTPS + www forcés | Anthony | à faire |
| 0.2 | Me donner la main : au choix (a) plugin `wordpress-mcp` (Automattic) comme sur pluscestsimple.com — je publie ensuite sans mot de passe en main, ou (b) Application Password WP | Anthony | à faire |
| 0.3 | Déposer `wp/mu-plugins/arw-legacy-redirects.php` (71 × 301, 4 × 410, routeur `.html` ère marketplace) | Claude | prêt à déployer |
| 0.4 | Créer les pages cibles des 301 (univers + familles de l'arborescence) en version courte mais réelle | Claude | après 0.2 |
| 0.5 | Recréer les 69 pages RECREATE par batchs — batch 1 : les 15 mieux positionnées. Pipeline : snapshot Wayback (matière première) → réécriture complète voix Denis Verhaeghe → review anti-hallucination (réglementaire vérifié sur source) | Claude + agents | matière prête (content/wayback/) |
| 0.6 | robots.txt (Disallow /go/), sitemap XML, soumission Search Console | Claude (GSC : Anthony valide la propriété) | après 0.4 |
| 0.7 | Récupération de la liste des backlinks perdus depuis l'expiration (export Ahrefs « Lost ») pour re-contact ultérieur | Anthony (export) | à faire |

Capacité de production : batchs de 10-15 pages/jour en croisière (cohérent avec les 20-30 pages/jour annoncées, la review réglementaire est le facteur limitant).

## Phase 1 — Fondations média + annuaire

- Thème custom. Base recommandée : core ARW Pulse, archetype **Industrial** (prévu dans la roadmap ARW Pulse, jamais construit — ce site est le candidat naturel). Sinon thème dédié.
- Le reste des 69 pages RECREATE + familles chariots/levage complètes.
- Silo `/reglementation/` (CACES par recommandation, VGP, autorisation de conduite, obligations employeur).
- Annuaire : implémentation `docs/schema-bdd.sql` + admin import/modération + fiches `/entreprise/{slug}/` + fonction « revendiquer sa fiche ».
- Brique leads : table + formulaires + notification + kanban admin + export CSV + purge RGPD.
- Table liens affiliés `/go/` + inscriptions Effinity (Manutan), Awin (ManoMano, Contorion), Amazon Partenaires.
- **Collecte GMB (séance commune)** : construction de l'agent de collecte (périmètre : loueurs, concessionnaires, SAV/VGP, OF CACES par département), import par vagues. Vague 1 d'exploitation : SAV/VGP × département.

## Phase 2 — Différenciation

- Outils : calculateur capacité résiduelle, sélecteur CACES, comparateur TCO (conversion).
- Silo `/prix/` complet + premiers relevés de prix structurés (fondation du baromètre).
- Annuaire vague 2 (location × département) + villes moyennes ciblées.
- Univers nacelles + stockage.
- Baromètre des prix v1 (asset linkable).

## Phase 3 — Monétisation active

- Démarchage partenaires devis à partir des revendications de fiches + annuaire DLR.
- Fiches premium. Articles sponsorisés (page « à propos » + trafic démontrable requis).
- Newsletter B2B sponsorisable.

## Suivi

- Ratio pages indexées / publiées (alerte < 70 % sur l'annuaire).
- Positions du socle résiduel (les 330 mots-clés de `data/`) à re-crawler à M+1 de la remise en ligne.
