<?php
/**
 * Type de contenu « Événement » — l'agenda des salons.
 *
 * Remplace l'ancien stockage par option : chaque salon est un contenu à part
 * entière, avec sa page (/agenda/{salon}/), éditable dans wp-admin comme un
 * article (ajout, modification, suppression). L'accueil lit automatiquement
 * les prochains à venir.
 *
 * Dates, lieu et lien officiel sont des méta exposées à REST, donc
 * renseignables aussi bien à la main qu'en masse.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

/**
 * Déclaration du type de contenu et de son archive /agenda/.
 */
add_action('init', function () {
    register_post_type('evenement', [
        'labels' => [
            'name'               => 'Événements',
            'singular_name'      => 'Événement',
            'add_new_item'       => 'Ajouter un événement',
            'edit_item'          => 'Modifier l\'événement',
            'new_item'           => 'Nouvel événement',
            'view_item'          => 'Voir l\'événement',
            'search_items'       => 'Rechercher un événement',
            'not_found'          => 'Aucun événement',
            'menu_name'          => 'Agenda',
        ],
        'public'        => true,
        'has_archive'   => 'agenda',
        'menu_icon'     => 'dashicons-calendar-alt',
        'supports'      => ['title', 'editor', 'thumbnail', 'excerpt'],
        'rewrite'       => ['slug' => 'agenda', 'with_front' => false],
        'show_in_rest'  => true,
    ]);

    $commun = [
        'type'          => 'string',
        'single'        => true,
        'show_in_rest'  => true,
        'auth_callback' => static fn () => current_user_can('edit_posts'),
    ];
    foreach (['_iw_ev_date', '_iw_ev_fin', '_iw_ev_lieu', '_iw_ev_url'] as $cle) {
        register_post_meta('evenement', $cle, $commun);
    }
});

/**
 * Un flush unique quand la version change, pour que /agenda/ réponde.
 */
add_action('init', function () {
    if (get_option('infoweb_agenda_regles') !== INFOWEB_VERSION) {
        update_option('infoweb_agenda_regles', INFOWEB_VERSION);
        add_action('shutdown', static fn () => flush_rewrite_rules(false));
    }
}, 99);

/**
 * Métabox de saisie : dates, lieu, lien officiel.
 */
add_action('add_meta_boxes', function () {
    add_meta_box('iw_evenement', 'Détails de l\'événement', 'infoweb_metabox_evenement', 'evenement', 'side', 'high');
});

function infoweb_metabox_evenement(WP_Post $post): void {
    wp_nonce_field('iw_evenement_' . $post->ID, 'iw_evenement_nonce');
    $v = fn($k) => esc_attr(get_post_meta($post->ID, $k, true));
    echo '<style>.iw-ev label{display:block;font-weight:600;margin:12px 0 3px}.iw-ev input{width:100%}</style><div class="iw-ev">';
    printf('<label>Date de début</label><input type="date" name="iw_ev_date" value="%s">', $v('_iw_ev_date'));
    printf('<label>Date de fin</label><input type="date" name="iw_ev_fin" value="%s"><p class="description">Laisser vide pour un événement d\'un jour.</p>', $v('_iw_ev_fin'));
    printf('<label>Lieu</label><input type="text" name="iw_ev_lieu" value="%s" placeholder="Paris Porte de Versailles">', $v('_iw_ev_lieu'));
    printf('<label>Site officiel</label><input type="url" name="iw_ev_url" value="%s" placeholder="https://…">', $v('_iw_ev_url'));
    echo '</div>';
}

add_action('save_post_evenement', function (int $post_id): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['iw_evenement_nonce'])
        || !wp_verify_nonce($_POST['iw_evenement_nonce'], 'iw_evenement_' . $post_id)
        || !current_user_can('edit_post', $post_id)) {
        return;
    }
    $date = sanitize_text_field(wp_unslash($_POST['iw_ev_date'] ?? ''));
    $fin  = sanitize_text_field(wp_unslash($_POST['iw_ev_fin'] ?? ''));
    // Un événement d'un jour : la fin vaut le début, pour simplifier le tri/filtre.
    if ($fin === '' && $date !== '') {
        $fin = $date;
    }
    update_post_meta($post_id, '_iw_ev_date', $date);
    update_post_meta($post_id, '_iw_ev_fin', $fin);
    update_post_meta($post_id, '_iw_ev_lieu', sanitize_text_field(wp_unslash($_POST['iw_ev_lieu'] ?? '')));
    update_post_meta($post_id, '_iw_ev_url', esc_url_raw(wp_unslash($_POST['iw_ev_url'] ?? '')));
}, 10, 1);

/**
 * Les prochains événements à venir, triés par date de début.
 * Même forme de sortie que l'ancienne fonction, pour ne rien changer à
 * l'accueil : ['date','fin','nom','lieu','url'] — 'url' pointe désormais la
 * page interne de l'événement.
 *
 * @return array<int,array<string,string>>
 */
function infoweb_evenements(int $max = 6): array {
    $aujourdhui = current_time('Y-m-d');
    $q = new WP_Query([
        'post_type'      => 'evenement',
        'posts_per_page' => $max,
        'meta_key'       => '_iw_ev_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
        'meta_query'     => [
            ['key' => '_iw_ev_fin', 'value' => $aujourdhui, 'compare' => '>=', 'type' => 'DATE'],
        ],
        'no_found_rows'  => true,
    ]);
    $events = [];
    foreach ($q->posts as $p) {
        $events[] = [
            'date' => (string) get_post_meta($p->ID, '_iw_ev_date', true),
            'fin'  => (string) get_post_meta($p->ID, '_iw_ev_fin', true),
            'nom'  => get_the_title($p),
            'lieu' => (string) get_post_meta($p->ID, '_iw_ev_lieu', true),
            'url'  => get_permalink($p),
        ];
    }
    return $events;
}

/**
 * Pastille de date : ['jour' => '30', 'mois' => 'Mars'].
 *
 * @return array{jour:string,mois:string}
 */
function infoweb_evenement_pastille(string $date): array {
    $t = strtotime($date);
    return $t
        ? ['jour' => date_i18n('d', $t), 'mois' => ucfirst(date_i18n('M', $t))]
        : ['jour' => '', 'mois' => ''];
}

/**
 * Formate une plage de dates : « 30 mars – 2 avril 2026 » ou « 6 octobre 2026 ».
 */
function infoweb_evenement_dates(string $debut, string $fin = ''): string {
    $d = strtotime($debut);
    if (!$d) {
        return '';
    }
    if (!$fin || $fin === $debut) {
        return date_i18n('j F Y', $d);
    }
    $f = strtotime($fin);
    return date('Y-m', $d) === date('Y-m', $f)
        ? date_i18n('j', $d) . ' – ' . date_i18n('j F Y', $f)
        : date_i18n('j F', $d) . ' – ' . date_i18n('j F Y', $f);
}
