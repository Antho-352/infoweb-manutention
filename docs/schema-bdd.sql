-- Schéma BDD infoweb-manutention.fr — annuaire, leads, affiliation
-- MySQL/MariaDB, InnoDB, utf8mb4. Tables custom hors wp_posts (décision actée : pas de CPT pour l'annuaire).
-- Préfixe arw_. Les tables vivent dans la même base que WordPress (backup/migration unifiés).

-- =====================================================================
-- RÉFÉRENTIEL GÉO
-- =====================================================================
CREATE TABLE arw_departements (
  code        VARCHAR(3)   NOT NULL PRIMARY KEY,   -- '57', '2A'
  slug        VARCHAR(64)  NOT NULL UNIQUE,        -- 'moselle-57'
  nom         VARCHAR(64)  NOT NULL,
  region_slug VARCHAR(64)  NOT NULL,
  region_nom  VARCHAR(64)  NOT NULL,
  KEY idx_region (region_slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- ANNUAIRE
-- =====================================================================
CREATE TABLE arw_etablissements (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug           VARCHAR(191) NOT NULL UNIQUE,       -- '{ville}-{slug-entreprise}' → /entreprise/{slug}/
  nom            VARCHAR(191) NOT NULL,
  type_principal ENUM('loueur','concessionnaire','sav','controle_vgp','formation') NOT NULL,
  adresse        VARCHAR(255) NULL,
  code_postal    VARCHAR(10)  NULL,
  ville          VARCHAR(96)  NULL,
  ville_slug     VARCHAR(96)  NULL,
  departement    VARCHAR(3)   NULL,                  -- FK arw_departements.code
  lat            DECIMAL(9,6) NULL,
  lng            DECIMAL(9,6) NULL,
  telephone      VARCHAR(32)  NULL,
  site_web       VARCHAR(255) NULL,
  email          VARCHAR(191) NULL,
  siret          VARCHAR(14)  NULL,
  description    TEXT         NULL,                  -- bloc éditorial unique (anti-thin)
  horaires       JSON         NULL,
  gmb_place_id   VARCHAR(128) NULL UNIQUE,           -- clé d'import/dédup GMB
  gmb_rating     DECIMAL(2,1) NULL,
  gmb_reviews    INT UNSIGNED NULL,
  source         VARCHAR(32)  NOT NULL DEFAULT 'gmb',-- gmb | dlr | manuel | revendication
  source_raw     JSON         NULL,                  -- payload d'import brut (schéma source évolutif — décision brief)
  statut         ENUM('brouillon','publie','ferme','rejete') NOT NULL DEFAULT 'brouillon',
  premium        TINYINT(1)   NOT NULL DEFAULT 0,
  premium_until  DATE         NULL,
  revendique     TINYINT(1)   NOT NULL DEFAULT 0,
  imported_at    DATETIME     NULL,
  updated_at     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_dept_type   (departement, type_principal, statut),
  KEY idx_ville       (ville_slug),
  KEY idx_statut      (statut),
  CONSTRAINT fk_etab_dept FOREIGN KEY (departement) REFERENCES arw_departements(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Un établissement peut cumuler plusieurs activités (loueur + SAV + concessionnaire)
CREATE TABLE arw_etablissement_types (
  etablissement_id BIGINT UNSIGNED NOT NULL,
  type             ENUM('loueur','concessionnaire','sav','controle_vgp','formation') NOT NULL,
  PRIMARY KEY (etablissement_id, type),
  CONSTRAINT fk_et_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Familles de machines couvertes → alimente /location|sav/{machine}/{dept}/
CREATE TABLE arw_etablissement_machines (
  etablissement_id BIGINT UNSIGNED NOT NULL,
  machine_slug     VARCHAR(64) NOT NULL,             -- 'chariot-elevateur', 'nacelle', 'transpalette'…
  PRIMARY KEY (etablissement_id, machine_slug),
  KEY idx_machine (machine_slug),
  CONSTRAINT fk_em_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Marques distribuées → alimente /concessionnaire/{marque}/{dept}/
CREATE TABLE arw_etablissement_marques (
  etablissement_id BIGINT UNSIGNED NOT NULL,
  marque_slug      VARCHAR(64) NOT NULL,             -- 'fenwick', 'manitou'…
  PRIMARY KEY (etablissement_id, marque_slug),
  KEY idx_marque (marque_slug),
  CONSTRAINT fk_emq_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Revendication de fiche (mécanisme d'amorçage des partenariats — point stratégique du brief)
CREATE TABLE arw_revendications (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  etablissement_id BIGINT UNSIGNED NOT NULL,
  nom              VARCHAR(128) NOT NULL,
  fonction         VARCHAR(128) NULL,
  email            VARCHAR(191) NOT NULL,
  telephone        VARCHAR(32)  NULL,
  message          TEXT         NULL,
  preuve           VARCHAR(255) NULL,                -- email pro / SIRET / justificatif déclaré
  statut           ENUM('nouvelle','verifiee','acceptee','refusee') NOT NULL DEFAULT 'nouvelle',
  token_verif      CHAR(64)     NULL,                -- lien de vérification email
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  traite_at        DATETIME     NULL,
  KEY idx_statut (statut),
  CONSTRAINT fk_rev_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Pages croisement : contenu local unique + pilotage indexation (règles anti-thin du brief)
-- + publication par lots de 100 (décision 2026-07-25) : la clé unique garantit l'absence
-- de doublon ville/département, et `statut` + `lot` pilotent la mise en ligne progressive.
CREATE TABLE arw_croisements (
  id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type           ENUM('location','sav','concessionnaire','formation') NOT NULL,
  cle            VARCHAR(64) NOT NULL,               -- machine_slug | marque_slug | recommandation ('r489')
  niveau         ENUM('region','departement','ville') NOT NULL,
  region_slug    VARCHAR(64) NULL,
  departement    VARCHAR(3)  NULL,
  ville_slug     VARCHAR(96) NOT NULL DEFAULT '',    -- '' pour région/département : permet l'unicité
  nb_etabs       INT UNSIGNED NOT NULL DEFAULT 0,    -- cache, recalculé à l'import
  contenu_local  MEDIUMTEXT  NULL,                   -- bloc unique : fourchette tarifaire, ZFE, tissu éco local
  faq            JSON        NULL,                   -- FAQ contextualisée (schema.org FAQPage)
  indexable      TINYINT(1)  NOT NULL DEFAULT 0,     -- 1 seulement si nb_etabs >= 3 ET contenu_local non NULL
  statut         ENUM('brouillon','pret','publie','retire') NOT NULL DEFAULT 'brouillon',
  lot            INT UNSIGNED NULL,                  -- numéro de lot de publication (100 pages par lot)
  published_at   DATETIME    NULL,
  updated_at     DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  -- Garantit qu'une même page (type + machine + géo) ne peut jamais exister en double,
  -- quelle que soit la vague d'import. Un ré-import fait un UPDATE, pas un INSERT.
  UNIQUE KEY uq_croisement (type, cle, niveau, region_slug, departement, ville_slug),
  KEY idx_index  (indexable),
  KEY idx_lot    (statut, lot),
  KEY idx_eligible (statut, indexable, nb_etabs),
  CONSTRAINT fk_cr_dept FOREIGN KEY (departement) REFERENCES arw_departements(code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Journal des lots de publication : traçabilité de ce qui est parti en ligne et quand.
CREATE TABLE arw_lots_publication (
  lot          INT UNSIGNED NOT NULL PRIMARY KEY,
  type         ENUM('location','sav','concessionnaire','formation') NOT NULL,
  nb_pages     INT UNSIGNED NOT NULL,
  publie_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  -- relevé d'indexation à J+30, pour décider si on lance le lot suivant
  indexees_j30 INT UNSIGNED NULL,
  notes        TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- PRIX — données, jamais de la prose (cf. docs/protocole-fiabilite.md §2)
-- Un prix révisé ici met à jour toutes les pages qui l'affichent.
-- Volume cible : 40 à 60 points de référence maximum.
-- =====================================================================
CREATE TABLE arw_prix (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug          VARCHAR(96)  NOT NULL UNIQUE,        -- 'chariot-electrique-2t-achat-neuf'
  libelle       VARCHAR(191) NOT NULL,               -- 'Chariot élévateur électrique 2 t, neuf'
  famille_slug  VARCHAR(64)  NOT NULL,
  mode          ENUM('achat','location_jour','location_semaine','location_mois','lld_mois','prestation') NOT NULL,
  montant_min   DECIMAL(10,2) NOT NULL,
  montant_max   DECIMAL(10,2) NOT NULL,
  unite         VARCHAR(24)  NOT NULL DEFAULT 'EUR_HT',
  perimetre     VARCHAR(255) NULL,                   -- 'hors options, transport et mise en service'
  source_type   ENUM('marchand','loueur','devis_agrege','constructeur') NOT NULL,
  source_detail VARCHAR(255) NULL,                   -- nom du marchand, ou 'agrégat de N devis'
  nb_releves    INT UNSIGNED NOT NULL DEFAULT 1,     -- minimum 3 pour source_type='devis_agrege'
  constate_le   DATE NOT NULL,                       -- date affichée publiquement
  revue_prevue  DATE NOT NULL,                       -- alerte de péremption au tableau de bord
  actif         TINYINT(1) NOT NULL DEFAULT 1,
  KEY idx_famille (famille_slug, mode),
  KEY idx_revue   (revue_prevue, actif)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- VÉRIFICATIONS RÉGLEMENTAIRES (cf. docs/protocole-fiabilite.md §1.4)
-- Une ligne par affirmation réglementaire publiée, avec sa source.
-- =====================================================================
CREATE TABLE arw_verifications (
  id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page_url     VARCHAR(255) NOT NULL,
  affirmation  TEXT         NOT NULL,
  source_ref   VARCHAR(255) NOT NULL,                -- 'Arrêté du 1er mars 2004, art. 23'
  source_url   VARCHAR(255) NULL,
  fichier_local VARCHAR(255) NULL,                   -- sources/reglementaire/...
  verifie_le   DATE         NOT NULL,
  verifie_par  VARCHAR(64)  NOT NULL,                -- 'agent-reviewer' | 'anthony'
  revue_prevue DATE         NOT NULL,
  KEY idx_page  (page_url),
  KEY idx_revue (revue_prevue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- LEADS (brique interne — pas de CRM externe, décision brief)
-- =====================================================================
CREATE TABLE arw_partenaires (
  id                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nom                VARCHAR(128) NOT NULL,
  etablissement_id   BIGINT UNSIGNED NULL,           -- lien fiche annuaire si revendiquée
  contact_nom        VARCHAR(128) NULL,
  contact_email      VARCHAR(191) NULL,
  contact_tel        VARCHAR(32)  NULL,
  familles           JSON         NULL,              -- machines couvertes
  departements       JSON         NULL,              -- zone d'exclusivité (argument commercial du brief)
  modele             VARCHAR(128) NULL,              -- 'fixe 60€/lead + 2% closing'…
  numero_tracke      VARCHAR(32)  NULL UNIQUE,       -- numéro dédié affiché sur ses pages
  actif              TINYINT(1)   NOT NULL DEFAULT 1,
  notes              TEXT         NULL,
  created_at         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_part_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE arw_leads (
  id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  type_demande     ENUM('devis_achat','devis_location','devis_sav','devis_formation','contact') NOT NULL,
  machine_slug     VARCHAR(64)  NULL,
  marque_slug      VARCHAR(64)  NULL,
  departement      VARCHAR(3)   NULL,
  ville            VARCHAR(96)  NULL,
  entreprise       VARCHAR(191) NULL,
  nom              VARCHAR(128) NOT NULL,
  email            VARCHAR(191) NOT NULL,
  telephone        VARCHAR(32)  NULL,
  message          TEXT         NULL,
  besoin           JSON         NULL,                -- champs structurés du formulaire (durée location, tonnage…)
  page_source      VARCHAR(255) NOT NULL,            -- URL d'origine (attribution)
  campagne         VARCHAR(96)  NULL,
  partenaire_id    BIGINT UNSIGNED NULL,             -- partenaire visé/transmis
  etablissement_id BIGINT UNSIGNED NULL,             -- fiche annuaire d'origine si applicable
  statut           ENUM('nouveau','transmis','qualifie','signe','perdu','spam') NOT NULL DEFAULT 'nouveau',
  montant_estime   DECIMAL(10,2) NULL,
  transmis_at      DATETIME     NULL,
  consentement_at  DATETIME     NOT NULL,            -- RGPD : horodatage du consentement
  purge_at         DATE         NOT NULL,            -- RGPD : date de purge planifiée (consentement + durée de conservation)
  ip_hash          CHAR(64)     NULL,                -- jamais l'IP en clair
  created_at       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notes            TEXT         NULL,
  KEY idx_statut   (statut, created_at),
  KEY idx_partner  (partenaire_id, statut),
  KEY idx_purge    (purge_at),
  CONSTRAINT fk_lead_part FOREIGN KEY (partenaire_id)    REFERENCES arw_partenaires(id)    ON DELETE SET NULL,
  CONSTRAINT fk_lead_etab FOREIGN KEY (etablissement_id) REFERENCES arw_etablissements(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Journal des appels sur numéros trackés (webhook du provider de call tracking)
CREATE TABLE arw_appels (
  id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  partenaire_id BIGINT UNSIGNED NOT NULL,
  numero        VARCHAR(32) NOT NULL,
  appelant_hash CHAR(64)    NULL,                    -- numéro appelant hashé (RGPD)
  duree_sec     INT UNSIGNED NULL,
  created_at    DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_part_date (partenaire_id, created_at),
  CONSTRAINT fk_appel_part FOREIGN KEY (partenaire_id) REFERENCES arw_partenaires(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- AFFILIATION (table centralisée — un changement de marchand = un UPDATE, pas 100 articles)
-- =====================================================================
CREATE TABLE arw_marchands (
  id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nom        VARCHAR(96) NOT NULL UNIQUE,            -- 'manutan', 'manomano', 'contorion', 'amazon'
  plateforme VARCHAR(96) NULL,                       -- 'effinity', 'awin', 'amazon-partenaires'
  taux       VARCHAR(64) NULL,                       -- indicatif : '5%', '7%'
  actif      TINYINT(1)  NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE arw_liens_affilies (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  slug        VARCHAR(96) NOT NULL UNIQUE,           -- /go/{slug}
  marchand_id BIGINT UNSIGNED NOT NULL,
  url_cible   TEXT        NOT NULL,                  -- deeplink complet avec tracking
  label       VARCHAR(191) NULL,                     -- 'Transpalette manuel 2,5t Manutan'
  prix_indic  DECIMAL(10,2) NULL,                    -- prix constaté (affiché "prix indicatif au JJ/MM")
  prix_maj_at DATE        NULL,
  actif       TINYINT(1)  NOT NULL DEFAULT 1,
  created_at  DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_marchand (marchand_id, actif),
  CONSTRAINT fk_lien_marchand FOREIGN KEY (marchand_id) REFERENCES arw_marchands(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE arw_clics_affilies (
  id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  lien_id     BIGINT UNSIGNED NOT NULL,
  page_source VARCHAR(255) NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_lien_date (lien_id, created_at),
  CONSTRAINT fk_clic_lien FOREIGN KEY (lien_id) REFERENCES arw_liens_affilies(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =====================================================================
-- Notes d'implémentation
-- - Import annuaire : upsert sur gmb_place_id ; source_raw conserve le payload brut (schéma source évolutif).
-- - Recalcul arw_croisements.nb_etabs + indexable à chaque import (trigger applicatif, pas SQL).
-- - Sitemaps segmentés générés par type depuis arw_croisements WHERE indexable=1 et arw_etablissements WHERE statut='publie'.
-- - /go/{slug} : 302 + header X-Robots-Tag noindex ; Disallow: /go/ dans robots.txt ; rel="sponsored" côté HTML.
-- - Purge RGPD : cron quotidien DELETE FROM arw_leads WHERE purge_at < CURDATE() (sauf statut 'signe' → anonymisation).
-- - Interfaces admin WP : pages dédiées (import CSV/JSON, modération fiches, kanban leads, gestion partenaires, liens affiliés).
-- =====================================================================
