<?php
/**
 * Performance : on retire ce que WordPress émet par défaut et dont ce site
 * n'a pas l'usage. Chaque ligne supprimée est une requête ou des octets en
 * moins sur chaque page.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

// Emoji : deux fichiers et un script inline sur chaque page, pour remplacer
// des caractères que toutes les polices système rendent déjà.
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
add_filter('emoji_svg_url', '__return_false');

// Métadonnées d'en-tête sans usage ici, dont certaines exposent la version.
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

// Embeds oEmbed : un fichier JS chargé partout pour une fonction qu'on
// n'utilise pas.
add_action('wp_footer', function () {
    wp_deregister_script('wp-embed');
});
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

// La feuille de styles des blocs est chargée en entier par défaut ;
// en mode « par bloc », seuls les blocs réellement présents pèsent.
add_filter('should_load_separate_core_block_assets', '__return_true');

// Styles globaux de WordPress non utilisés : le thème pose les siens.
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('global-styles');
}, 20);

/**
 * Chargement différé et dimensions explicites sur les images du contenu :
 * les dimensions évitent le décalage de mise en page au chargement, qui est
 * la première cause de mauvais score de stabilité visuelle.
 */
add_filter('wp_get_attachment_image_attributes', function (array $attr) {
    $attr['decoding'] = 'async';
    return $attr;
});

/**
 * Suppression des tailles intermédiaires générées par WordPress et jamais
 * utilisées par le thème : autant de fichiers en moins à chaque envoi.
 */
add_filter('intermediate_image_sizes_advanced', function (array $tailles) {
    unset($tailles['medium_large'], $tailles['1536x1536'], $tailles['2048x2048']);
    return $tailles;
});

/**
 * Préconnexion inutile : aucune ressource externe n'est chargée.
 * On retire le hint DNS que WordPress ajoute pour s.w.org.
 */
add_filter('wp_resource_hints', function (array $hints, string $relation) {
    if ($relation === 'dns-prefetch') {
        $hints = array_diff($hints, ['//s.w.org']);
    }
    return $hints;
}, 10, 2);
