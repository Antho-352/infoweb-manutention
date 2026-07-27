<?php
/**
 * Supports du thème, menus et tailles d'image.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('after_setup_theme', 'infoweb_setup');
function infoweb_setup(): void {
    load_theme_textdomain('infoweb', get_theme_file_path('languages'));

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', [
        'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script',
    ]);
    add_theme_support('custom-logo', [
        'height'      => 48,
        'width'       => 260,
        'flex-width'  => true,
        'flex-height' => true,
    ]);
    // On refuse la personnalisation typographique et chromatique depuis
    // l'administration : ces choix sont arbitrés dans le CSS, les rouvrir
    // à chaque publication ferait dériver la mise en page.
    add_theme_support('editor-styles');
    remove_theme_support('core-block-patterns');

    register_nav_menus([
        'principal' => __('Navigation principale', 'infoweb'),
        'pied'      => __('Pied de page', 'infoweb'),
    ]);

    // Formats d'image : un seul par usage, pour ne pas multiplier les
    // fichiers générés à chaque envoi.
    add_image_size('infoweb-carte', 640, 420, true);   // cartes de liste
    add_image_size('infoweb-une', 1200, 675, true);    // une et partage social
}

/**
 * Les commentaires n'ont pas de place sur un média de décision B2B :
 * aucune modération à assurer, aucune surface de spam ouverte.
 */
add_filter('comments_open', '__return_false', 20);
add_filter('pings_open', '__return_false', 20);
add_action('admin_menu', function () {
    remove_menu_page('edit-comments.php');
});

/**
 * Longueur et suite des extraits automatiques.
 */
add_filter('excerpt_length', fn() => 32, 20);
add_filter('excerpt_more', fn() => '…');

/**
 * Temps de lecture estimé, affiché dans la signature des articles.
 * 220 mots par minute, arrondi au supérieur, minimum une minute.
 */
function infoweb_temps_lecture(?int $post_id = null): int {
    $contenu = get_post_field('post_content', $post_id ?: get_the_ID());
    $mots = str_word_count(wp_strip_all_tags($contenu));
    return max(1, (int) ceil($mots / 220));
}

/**
 * Date de dernière vérification du contenu, distincte de la date de
 * modification : elle atteste d'un contrôle des faits, pas d'une retouche
 * de mise en forme. Voir docs/protocole-fiabilite.md.
 */
function infoweb_date_verification(?int $post_id = null): string {
    $d = get_post_meta($post_id ?: get_the_ID(), '_infoweb_verifie_le', true);
    return $d ? date_i18n('d/m/Y', strtotime($d)) : '';
}
