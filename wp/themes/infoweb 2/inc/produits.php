<?php
/**
 * Produits et comparatifs d'affiliation.
 *
 * Un produit est un contenu à part entière : il apparaît dans plusieurs
 * comparatifs, son prix évolue, ses caractéristiques se corrigent. Le
 * stocker dans le texte de chaque article obligerait à répéter la même
 * fiche et à la corriger partout — c'est exactement la dette qu'on évite
 * pour les prix.
 *
 * D'où un type de contenu dédié, non public : il a un écran d'édition mais
 * pas de page à lui, pour ne pas semer des pages maigres dans l'index.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('init', function () {
    register_post_type('iw_produit', [
        'labels' => [
            'name'          => 'Produits',
            'singular_name' => 'Produit',
            'add_new_item'  => 'Ajouter un produit',
            'edit_item'     => 'Modifier le produit',
            'search_items'  => 'Rechercher un produit',
            'not_found'     => 'Aucun produit. Ajoutez-en un pour le citer dans un comparatif.',
        ],
        'public'              => false,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'menu_icon'           => 'dashicons-cart',
        'menu_position'       => 27,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'has_archive'         => false,
        'rewrite'             => false,
        'supports'            => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ]);
});

const INFOWEB_CHAMPS_PRODUIT = [
    'marque'         => ['Marque', 'text', 'Jungheinrich'],
    'modele'         => ['Modèle', 'text', 'EJE 116'],
    'prix_min'       => ['Prix à partir de (€ HT)', 'number', '4200'],
    'prix_date'      => ['Prix relevé le', 'date', ''],
    'url_affiliee'   => ['Lien marchand', 'url', 'https://…'],
    'marchand'       => ['Marchand', 'text', 'Manutan'],
    'specs'          => ['Caractéristiques', 'area', "Capacité de charge | 1 600 kg\nBatterie | Li-ion 24 V\nAutonomie constatée | ≈ 5 h"],
    'points_forts'   => ['Points forts', 'area', "Une ligne par point"],
    'points_faibles' => ['Points faibles', 'area', "Une ligne par point"],
    'avis'           => ['Notre avis en 30 secondes', 'area', "Terminez par le cas où il faut écarter le produit."],
];

add_action('add_meta_boxes', function () {
    add_meta_box('iw_produit_champs', 'Fiche produit', 'infoweb_metabox_produit', 'iw_produit', 'normal', 'high');
});

function infoweb_metabox_produit(WP_Post $post): void {
    wp_nonce_field('infoweb_produit_' . $post->ID, 'infoweb_produit_nonce');
    echo '<style>.iwp label{display:block;font-weight:600;margin:13px 0 4px}
          .iwp input,.iwp textarea{width:100%}.iwp .a{color:#666;font-size:12px;margin-top:3px}</style>';
    echo '<div class="iwp">';
    foreach (INFOWEB_CHAMPS_PRODUIT as $cle => [$libelle, $type, $exemple]) {
        $v = get_post_meta($post->ID, '_iw_' . $cle, true);
        printf('<label for="iw_%s">%s</label>', esc_attr($cle), esc_html($libelle));
        if ($type === 'area') {
            printf('<textarea id="iw_%s" name="iw_%s" rows="4" placeholder="%s">%s</textarea>',
                esc_attr($cle), esc_attr($cle), esc_attr($exemple), esc_textarea($v));
            if ($cle === 'specs') {
                echo '<p class="a">Une caractéristique par ligne, au format <code>Libellé | Valeur</code>.</p>';
            } else {
                echo '<p class="a">Une ligne par élément.</p>';
            }
        } else {
            printf('<input type="%s" id="iw_%s" name="iw_%s" value="%s" placeholder="%s">',
                esc_attr($type), esc_attr($cle), esc_attr($cle), esc_attr($v), esc_attr($exemple));
        }
    }
    echo '</div>';
}

add_action('save_post_iw_produit', function (int $post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (empty($_POST['infoweb_produit_nonce'])
        || !wp_verify_nonce($_POST['infoweb_produit_nonce'], 'infoweb_produit_' . $post_id)) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }

    foreach (INFOWEB_CHAMPS_PRODUIT as $cle => [$libelle, $type]) {
        $brut = wp_unslash($_POST['iw_' . $cle] ?? '');
        $v = match ($type) {
            'url'    => esc_url_raw($brut),
            'number' => (string) (float) str_replace(',', '.', $brut),
            'area'   => sanitize_textarea_field($brut),
            default  => sanitize_text_field($brut),
        };
        $v === '' ? delete_post_meta($post_id, '_iw_' . $cle) : update_post_meta($post_id, '_iw_' . $cle, $v);
    }
});

/**
 * Colonnes utiles dans la liste des produits : sans le prix ni sa date,
 * la liste ne sert qu'à retrouver un titre.
 */
add_filter('manage_iw_produit_posts_columns', function (array $c) {
    return ['cb' => $c['cb'], 'title' => 'Produit', 'marque' => 'Marque',
            'prix' => 'Prix', 'date_prix' => 'Prix relevé le', 'date' => $c['date']];
});
add_action('manage_iw_produit_posts_custom_column', function (string $col, int $id) {
    $v = fn($k) => get_post_meta($id, '_iw_' . $k, true);
    match ($col) {
        'marque'    => print(esc_html($v('marque'))),
        'prix'      => print($v('prix_min') ? esc_html(number_format((float) $v('prix_min'), 0, ',', ' ') . ' €') : '—'),
        'date_prix' => print(esc_html($v('prix_date') ?: '—')),
        default     => null,
    };
}, 10, 2);

/**
 * Lignes « Libellé | Valeur » -> tableau. Une ligne mal formée est ignorée
 * plutôt que rendue de travers.
 */
function infoweb_produit_specs(int $id): array {
    $brut = (string) get_post_meta($id, '_iw_specs', true);
    $out = [];
    foreach (preg_split('/\R/', $brut) as $ligne) {
        if (!str_contains($ligne, '|')) { continue; }
        [$k, $v] = array_map('trim', explode('|', $ligne, 2));
        if ($k !== '' && $v !== '') { $out[$k] = $v; }
    }
    return $out;
}

function infoweb_produit_lignes(int $id, string $champ): array {
    $brut = (string) get_post_meta($id, '_iw_' . $champ, true);
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $brut))));
}

/**
 * [comparatif produits="12,18,25"] — identifiants ou slugs, dans l'ordre
 * du classement.
 *
 * Le nom de produit sort en H2 « marque + modèle » : c'est ce que
 * l'internaute tape, et cela fait de chaque produit une section de premier
 * niveau du document.
 */
add_shortcode('comparatif', function ($atts) {
    $a = shortcode_atts(['produits' => '', 'tableau' => 'oui'], $atts, 'comparatif');
    $refs = array_filter(array_map('trim', explode(',', $a['produits'])));
    if (!$refs) { return ''; }

    $produits = [];
    foreach ($refs as $ref) {
        $p = ctype_digit($ref) ? get_post((int) $ref) : get_page_by_path($ref, OBJECT, 'iw_produit');
        if ($p && $p->post_type === 'iw_produit' && $p->post_status === 'publish') {
            $produits[] = $p;
        }
    }
    if (!$produits) {
        return current_user_can('edit_posts')
            ? '<p class="norme"><span class="t">Comparatif</span>Aucun produit publié ne correspond à ces références.</p>'
            : '';
    }

    $m = fn($id, $k) => get_post_meta($id, '_iw_' . $k, true);
    ob_start(); ?>

    <?php if ($a['tableau'] === 'oui') :
      // colonnes = union des caractéristiques, en gardant l'ordre du premier produit
      $colonnes = [];
      foreach ($produits as $p) {
          foreach (array_keys(infoweb_produit_specs($p->ID)) as $k) {
              if (!in_array($k, $colonnes, true)) { $colonnes[] = $k; }
          }
      }
      $colonnes = array_slice($colonnes, 0, 5); ?>
      <div class="cmp-tab">
        <table>
          <thead><tr><th>Modèle</th>
            <?php foreach ($colonnes as $c) : ?><th><?php echo esc_html($c); ?></th><?php endforeach; ?>
            <th>Prix</th></tr></thead>
          <tbody>
          <?php foreach ($produits as $p) : $s = infoweb_produit_specs($p->ID); ?>
            <tr>
              <td><a href="#p<?php echo (int) $p->ID; ?>"><?php echo esc_html(get_the_title($p)); ?></a></td>
              <?php foreach ($colonnes as $c) : ?><td><?php echo esc_html($s[$c] ?? '—'); ?></td><?php endforeach; ?>
              <td><?php echo $m($p->ID, 'prix_min')
                    ? esc_html(number_format((float) $m($p->ID, 'prix_min'), 0, ',', ' ') . ' €') : '—'; ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>

    <?php foreach ($produits as $i => $p) :
      $nom = get_the_title($p);
      $forts = infoweb_produit_lignes($p->ID, 'points_forts');
      $faibles = infoweb_produit_lignes($p->ID, 'points_faibles');
      $specs = infoweb_produit_specs($p->ID);
      $prix = $m($p->ID, 'prix_min');
      $url = $m($p->ID, 'url_affiliee');
      $lien = $url ? home_url('/go/' . $p->post_name . '/') : ''; ?>

      <article class="pfiche" id="p<?php echo (int) $p->ID; ?>">
        <div class="pf-h">
          <?php if (has_post_thumbnail($p)) : ?>
            <div class="pf-im"><?php echo get_the_post_thumbnail($p, 'infoweb-carte', ['loading' => 'lazy']); ?></div>
          <?php endif; ?>
          <div>
            <span class="pf-rang">N° <?php echo (int) ($i + 1); ?></span>
            <h2><?php echo esc_html($nom); ?></h2>
            <div class="pf-desc"><?php echo wp_kses_post(apply_filters('the_content', $p->post_content)); ?></div>
          </div>
          <?php if ($prix) : ?>
            <div class="pf-buy">
              <span class="p"><?php echo esc_html(number_format((float) $prix, 0, ',', ' ') . ' €'); ?></span>
              <span class="d">à partir de, HT<?php echo $m($p->ID, 'prix_date')
                ? ' · relevé le ' . esc_html(date_i18n('d/m/Y', strtotime($m($p->ID, 'prix_date')))) : ''; ?></span>
              <?php if ($lien) : ?>
                <a href="<?php echo esc_url($lien); ?>" rel="sponsored nofollow"
                   aria-label="<?php echo esc_attr('Voir l\'offre pour le ' . $nom); ?>">Voir l'offre ↗</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($specs) : ?>
          <div class="pf-specs">
            <h3>Caractéristiques<span class="vh"> du <?php echo esc_html($nom); ?></span></h3>
            <dl>
              <?php foreach ($specs as $k => $v) : ?>
                <div><dt><?php echo esc_html($k); ?></dt><dd><?php echo esc_html($v); ?></dd></div>
              <?php endforeach; ?>
            </dl>
          </div>
        <?php endif; ?>

        <?php if ($forts || $faibles) : ?>
          <div class="pf-pm">
            <?php if ($forts) : ?>
              <div class="pm up"><h3>Points forts<span class="vh"> du <?php echo esc_html($nom); ?></span></h3>
                <ul><?php foreach ($forts as $l) : ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <?php if ($faibles) : ?>
              <div class="pm dn"><h3>Points faibles<span class="vh"> du <?php echo esc_html($nom); ?></span></h3>
                <ul><?php foreach ($faibles as $l) : ?><li><?php echo esc_html($l); ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
          </div>
        <?php endif; ?>

        <?php $avis = $m($p->ID, 'avis'); if ($avis) : ?>
          <div class="pf-avis"><h3>Notre avis en 30 secondes<span class="vh"> sur le <?php echo esc_html($nom); ?></span></h3>
            <p><?php echo esc_html($avis); ?></p></div>
        <?php endif; ?>

        <?php if ($lien && $prix) : ?>
          <div class="pf-pied">
            <span class="p"><?php echo esc_html(number_format((float) $prix, 0, ',', ' ') . ' €'); ?></span>
            <span class="d">à partir de, HT</span>
            <a href="<?php echo esc_url($lien); ?>" rel="sponsored nofollow"
               aria-label="<?php echo esc_attr('Voir l\'offre pour le ' . $nom); ?>">Voir l'offre ↗</a>
          </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>

    <p class="pf-transparence"><strong>Transparence.</strong> Les liens « Voir l'offre » sont des liens
      commerciaux : si vous achetez, nous percevons une commission, sans surcoût pour vous.
      Le classement ne s'achète pas — les critères de sélection sont détaillés dans cet article.</p>
    <?php
    return ob_get_clean();
});

/**
 * Redirection tracée /go/{slug} vers le marchand.
 *
 * Le lien affiché sur la page reste interne : changer de marchand se fait
 * dans la fiche produit, sans rouvrir les articles qui la citent. La règle
 * est exclue de l'indexation par robots.txt et par l'en-tête.
 */
add_action('init', function () {
    add_rewrite_rule('^go/([^/]+)/?$', 'index.php?iw_go=$matches[1]', 'top');
});
add_filter('query_vars', function (array $v) { $v[] = 'iw_go'; return $v; });

add_action('template_redirect', function () {
    $slug = get_query_var('iw_go');
    if (!$slug) { return; }

    $p = get_page_by_path($slug, OBJECT, 'iw_produit');
    $url = $p ? get_post_meta($p->ID, '_iw_url_affiliee', true) : '';
    if (!$url) {
        wp_safe_redirect(home_url('/'), 302);
        exit;
    }
    header('X-Robots-Tag: noindex, nofollow', true);
    wp_redirect(esc_url_raw($url), 302);
    exit;
});

add_filter('robots_txt', function (string $txt) {
    return $txt . "Disallow: /go/\n";
}, 10);
