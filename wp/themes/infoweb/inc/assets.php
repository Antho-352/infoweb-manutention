<?php
/**
 * Chargement des styles.
 *
 * Le CSS est inliné dans le <head> plutôt que servi en fichier : il pèse
 * moins que le surcoût d'une requête supplémentaire, et il supprime le
 * rendu bloquant. Aucun JavaScript n'est chargé par défaut ; les deux
 * exceptions prévues (sélecteur d'engin, carte de l'annuaire) seront
 * enfilées uniquement sur leur propre gabarit.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    // Feuille déclarée pour que les extensions puissent s'y accrocher,
    // mais le contenu part inline (voir ci-dessous).
    wp_register_style('infoweb', false, [], INFOWEB_VERSION);
    wp_enqueue_style('infoweb');

    $css = @file_get_contents(get_theme_file_path('assets/css/main.css'));
    if ($css !== false) {
        wp_add_inline_style('infoweb', infoweb_compacter_css($css));
    }
});

/**
 * Compactage minimal : commentaires, espaces de début de ligne et sauts.
 * On reste conservateur — pas de réécriture de sélecteurs, pas de fusion
 * de règles : un compacteur agressif casse silencieusement du CSS valide.
 */
function infoweb_compacter_css(string $css): string {
    $css = preg_replace('#/\*(?!!).*?\*/#s', '', $css);
    $css = preg_replace('/\s*\n\s*/', "\n", $css);
    $css = preg_replace('/;\s*}/', '}', $css);
    return trim($css);
}

/**
 * Styles de l'éditeur : la rédaction voit la même typographie et les mêmes
 * largeurs qu'en front, sinon les décisions de mise en forme sont prises à
 * l'aveugle.
 */
add_action('after_setup_theme', function () {
    add_editor_style('assets/css/main.css');
});
