<?php
/**
 * Structure d'URL : suppression de la base « category ».
 *
 * Les pages familles doivent vivre à la racine (/chariot-elevateur/) et les
 * articles sous leur famille (/chariot-elevateur/mon-article/), pour
 * reproduire les URLs héritées qui portent encore des positions.
 *
 * Requiert la structure de permaliens /%category%/%postname%/.
 * Les catégories doivent rester de premier niveau : une catégorie enfant
 * ferait apparaître le chemin complet de ses ancêtres dans l'URL des
 * articles, ce qui casserait la correspondance.
 *
 * @package infoweb-outils
 */

defined('ABSPATH') || exit;

add_filter('category_link', 'infoweb_retirer_base_categorie', 10, 2);
function infoweb_retirer_base_categorie(string $lien, int $term_id): string {
    $base = trim(get_option('category_base') ?: 'category', '/');
    if ($base === '') {
        return $lien;
    }
    return preg_replace('#/' . preg_quote($base, '#') . '/#', '/', $lien, 1);
}

/**
 * Règles d'archive placées AVANT la règle attrape-tout des articles :
 * sans cela, /chariot-elevateur/ serait interprété comme un article et
 * renverrait une 404.
 */
add_filter('rewrite_rules_array', 'infoweb_regles_categories');
function infoweb_regles_categories(array $regles): array {
    $categories = get_categories(['hide_empty' => false, 'parent' => 0]);
    if (is_wp_error($categories) || !$categories) {
        return $regles;
    }
    $nouvelles = [];
    foreach ($categories as $c) {
        $q = 'index.php?category_name=' . $c->slug;
        $nouvelles['^' . $c->slug . '/feed/(feed|rdf|rss|rss2|atom)/?$'] = $q . '&feed=$matches[1]';
        $nouvelles['^' . $c->slug . '/page/([0-9]{1,})/?$']              = $q . '&paged=$matches[1]';
        $nouvelles['^' . $c->slug . '/?$']                               = $q;
    }
    return $nouvelles + $regles;
}

/**
 * Régénération des règles quand la liste des catégories change, ou après
 * une mise à jour du plugin.
 */
add_action('created_category', 'infoweb_outils_vider_regles');
add_action('edited_category',  'infoweb_outils_vider_regles');
add_action('delete_category',  'infoweb_outils_vider_regles');

add_action('init', function () {
    if (get_option('infoweb_outils_version') !== INFOWEB_OUTILS_VERSION) {
        infoweb_outils_vider_regles();
        update_option('infoweb_outils_version', INFOWEB_OUTILS_VERSION);
    }
}, 99);

function infoweb_outils_vider_regles(): void {
    // Différé : les règles doivent être régénérées une fois toutes les
    // catégories et tous les filtres chargés.
    add_action('shutdown', function () {
        flush_rewrite_rules(false);
    });
}

/**
 * Avertissement si la structure de permaliens ne permet pas de reproduire
 * les URLs héritées. Silencieux tant que tout va bien.
 */
add_action('admin_notices', function () {
    if (!current_user_can('manage_options')) {
        return;
    }
    $structure = get_option('permalink_structure');
    if (str_contains($structure, '%category%')) {
        return;
    }
    printf(
        '<div class="notice notice-warning"><p><strong>Infoweb Outils</strong> — la structure de
         permaliens est <code>%s</code>. Les URLs héritées ne peuvent pas être reproduites.
         Choisissez une structure personnalisée <code>/%%category%%/%%postname%%/</code> dans
         <a href="%s">Réglages → Permaliens</a>.</p></div>',
        esc_html($structure ?: 'Simple'),
        esc_url(admin_url('options-permalink.php'))
    );
});
