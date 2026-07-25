<?php
/**
 * Plugin Name: ARW URL Structure
 * Description: Supprime la base « category » des URLs de catégorie, pour que les pages familles vivent à la racine (/chariot-elevateur/) et les articles sous leur famille (/chariot-elevateur/mon-article/). Requiert la structure de permaliens /%category%/%postname%/.
 * Version: 1.0.0
 *
 * Structure cible (docs/strategie-media-industrie.md §3.2) :
 *   /chariot-elevateur/                    archive de catégorie = page famille
 *   /chariot-elevateur/norme-passage/      article de la famille
 *
 * Note : les catégories doivent rester de premier niveau. Une catégorie enfant
 * ferait apparaître le chemin complet de ses ancêtres dans l'URL des articles,
 * ce qui casserait la correspondance avec les URLs héritées.
 */

defined('ABSPATH') || exit;

const ARW_URL_STRUCTURE_VERSION = '1.0.0';

/**
 * Retire le préfixe de base des permaliens de catégorie.
 */
add_filter('category_link', 'arw_strip_category_base', 10, 2);
function arw_strip_category_base($link, $term_id) {
    $base = trim(get_option('category_base') ?: 'category', '/');
    if ($base === '') {
        return $link;
    }
    return preg_replace('#/' . preg_quote($base, '#') . '/#', '/', $link, 1);
}

/**
 * Génère les règles de réécriture des archives de catégorie à la racine.
 *
 * Elles sont placées AVANT la règle attrape-tout des articles générée par
 * /%category%/%postname%/, sans quoi une archive serait interprétée comme un
 * article et renverrait une 404.
 */
add_filter('rewrite_rules_array', 'arw_prepend_category_rules');
function arw_prepend_category_rules($rules) {
    $categories = get_categories([
        'hide_empty' => false,
        'parent'     => 0,
    ]);
    if (is_wp_error($categories) || empty($categories)) {
        return $rules;
    }

    $new = [];
    foreach ($categories as $category) {
        $slug = $category->slug;
        $q    = 'index.php?category_name=' . $slug;

        $new['^' . $slug . '/feed/(feed|rdf|rss|rss2|atom)/?$'] = $q . '&feed=$matches[1]';
        $new['^' . $slug . '/page/([0-9]{1,})/?$']              = $q . '&paged=$matches[1]';
        $new['^' . $slug . '/?$']                               = $q;
    }

    return $new + $rules;
}

/**
 * Vide les règles de réécriture quand la liste des catégories change,
 * ou après une mise à jour de version de ce plugin.
 */
add_action('created_category', 'arw_flush_url_structure');
add_action('edited_category',  'arw_flush_url_structure');
add_action('delete_category',  'arw_flush_url_structure');

add_action('init', function () {
    if (get_option('arw_url_structure_version') !== ARW_URL_STRUCTURE_VERSION) {
        arw_flush_url_structure();
        update_option('arw_url_structure_version', ARW_URL_STRUCTURE_VERSION);
    }
}, 99);

function arw_flush_url_structure() {
    // Différé : les règles doivent être régénérées après que toutes les
    // catégories et tous les filtres sont chargés.
    add_action('shutdown', function () {
        flush_rewrite_rules(false);
    });
}
