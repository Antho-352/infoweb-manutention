<?php
/**
 * Point d'entrée du thème.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

const INFOWEB_VERSION = '0.1.0';

/**
 * Chargement des modules. Chacun est autonome et n'expose que des hooks —
 * aucun ne dépend de l'ordre de chargement des autres.
 */
foreach ([
    'setup',        // supports du thème, menus, tailles d'image
    'assets',       // CSS critique inliné, aucun JS par défaut
    'rubriques',    // carte rubrique éditoriale -> familles
    'breadcrumbs',  // fil d'Ariane + BreadcrumbList
    'seo',          // title, meta description, canonique, Open Graph
    'schema',       // Article, FAQPage, LocalBusiness
    'metabox-seo',  // les trois champs par page
    'security',     // en-têtes HTTP, durcissement
    'performance',  // nettoyage des sorties inutiles de WordPress
] as $module) {
    require_once get_theme_file_path("inc/{$module}.php");
}
