<?php
/**
 * Point d'entrée du thème.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

const INFOWEB_VERSION = '0.5.0';

/**
 * Chargement des modules. Chacun est autonome et n'expose que des hooks —
 * aucun ne dépend de l'ordre de chargement des autres.
 */
foreach ([
    'setup',        // supports du thème, menus, tailles d'image
    'db',           // tables dédiées : demandes de devis, points de prix
    'assets',       // CSS critique inliné, aucun JS par défaut
    'rubriques',    // carte rubrique éditoriale -> familles
    'breadcrumbs',  // fil d'Ariane + BreadcrumbList
    'seo',          // title, meta description, canonique, Open Graph
    'schema',       // Article, FAQPage, LocalBusiness
    'metabox-seo',  // les trois champs par page
    'security',     // en-têtes HTTP, durcissement
    'performance',  // nettoyage des sorties inutiles de WordPress
    'prix',         // points de prix + code court [prix]
    'leads',        // formulaire de devis, écran d'administration, export
    'accueil',      // secteurs, agenda des salons, capture newsletter
    'produits',     // fiches produit, code court [comparatif], redirections /go/
    'provisioning', // pages système et menus créés à l'activation
] as $module) {
    require_once get_theme_file_path("inc/{$module}.php");
}
