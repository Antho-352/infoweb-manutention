<?php
/**
 * Tables dédiées.
 *
 * Les demandes de devis et les points de prix ne sont pas des articles : les
 * stocker en CPT ferait gonfler wp_posts et wp_postmeta pour des données qui
 * n'ont ni éditeur, ni révisions, ni permalien. Deux tables, indexées sur ce
 * qu'on interroge réellement.
 *
 * Le schéma complet du projet est documenté dans docs/schema-bdd.sql ;
 * ce fichier n'installe que ce dont le thème a besoin aujourd'hui.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

const INFOWEB_DB_VERSION = '1.0.0';

function infoweb_table(string $nom): string {
    global $wpdb;
    return $wpdb->prefix . 'arw_' . $nom;
}

/**
 * Création et migration des tables. dbDelta compare la structure existante
 * et n'applique que les écarts : la fonction est donc rejouable sans risque.
 */
add_action('after_switch_theme', 'infoweb_installer_tables');
add_action('init', function () {
    if (get_option('infoweb_db_version') !== INFOWEB_DB_VERSION) {
        infoweb_installer_tables();
    }
}, 5);

function infoweb_installer_tables(): void {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    $collate = $wpdb->get_charset_collate();

    $leads = infoweb_table('leads');
    $prix  = infoweb_table('prix');

    dbDelta("CREATE TABLE {$leads} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        type_demande VARCHAR(32) NOT NULL DEFAULT 'devis',
        machine VARCHAR(64) NULL,
        capacite VARCHAR(64) NULL,
        duree VARCHAR(64) NULL,
        departement VARCHAR(3) NULL,
        entreprise VARCHAR(191) NULL,
        nom VARCHAR(128) NOT NULL,
        email VARCHAR(191) NOT NULL,
        telephone VARCHAR(32) NULL,
        message TEXT NULL,
        page_source VARCHAR(255) NULL,
        statut VARCHAR(16) NOT NULL DEFAULT 'nouveau',
        notes TEXT NULL,
        ip_hash CHAR(64) NULL,
        consentement_at DATETIME NOT NULL,
        purge_at DATE NOT NULL,
        created_at DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_statut (statut, created_at),
        KEY idx_purge (purge_at)
    ) {$collate};");

    dbDelta("CREATE TABLE {$prix} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        slug VARCHAR(96) NOT NULL,
        libelle VARCHAR(191) NOT NULL,
        famille VARCHAR(64) NOT NULL,
        mode VARCHAR(24) NOT NULL DEFAULT 'achat',
        montant_min DECIMAL(10,2) NOT NULL,
        montant_max DECIMAL(10,2) NOT NULL,
        perimetre VARCHAR(255) NULL,
        source VARCHAR(191) NULL,
        nb_releves INT UNSIGNED NOT NULL DEFAULT 1,
        constate_le DATE NOT NULL,
        revue_prevue DATE NOT NULL,
        actif TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (id),
        UNIQUE KEY uq_slug (slug),
        KEY idx_famille (famille, mode),
        KEY idx_revue (revue_prevue, actif)
    ) {$collate};");

    update_option('infoweb_db_version', INFOWEB_DB_VERSION);
}

/**
 * Purge RGPD quotidienne. Les demandes signées sont anonymisées plutôt que
 * supprimées : on garde l'historique commercial sans garder les personnes.
 */
add_action('after_switch_theme', function () {
    if (!wp_next_scheduled('infoweb_purge_rgpd')) {
        wp_schedule_event(time() + 3600, 'daily', 'infoweb_purge_rgpd');
    }
});
add_action('switch_theme', function () {
    wp_clear_scheduled_hook('infoweb_purge_rgpd');
});

add_action('infoweb_purge_rgpd', function () {
    global $wpdb;
    $t = infoweb_table('leads');

    $wpdb->query($wpdb->prepare(
        "UPDATE {$t} SET nom='(anonymisé)', email='', telephone=NULL, ip_hash=NULL
         WHERE purge_at < %s AND statut = 'signe' AND email <> ''",
        current_time('Y-m-d')
    ));
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$t} WHERE purge_at < %s AND statut <> 'signe'",
        current_time('Y-m-d')
    ));
});
