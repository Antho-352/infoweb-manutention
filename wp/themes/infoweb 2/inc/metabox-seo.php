<?php
/**
 * Les trois réglages SEO par page.
 *
 * Trois champs, pas trente : titre, description, indexation. Le reste est
 * déduit automatiquement (voir inc/seo.php) et n'a pas à être saisi.
 * Stockage en post_meta : les données restent lisibles même sans le thème.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

const INFOWEB_CHAMPS_SEO = [
    '_infoweb_titre_seo'       => ['Titre SEO', 60],
    '_infoweb_description_seo' => ['Méta description', 160],
];

add_action('add_meta_boxes', function () {
    add_meta_box(
        'infoweb_seo',
        'Référencement',
        'infoweb_rendre_metabox_seo',
        ['post', 'page'],
        'normal',
        'high'
    );
});

function infoweb_rendre_metabox_seo(WP_Post $post): void {
    wp_nonce_field('infoweb_seo_' . $post->ID, 'infoweb_seo_nonce');

    $titre = get_post_meta($post->ID, '_infoweb_titre_seo', true);
    $desc  = get_post_meta($post->ID, '_infoweb_description_seo', true);
    $noidx = get_post_meta($post->ID, '_infoweb_noindex', true);
    $verif = get_post_meta($post->ID, '_infoweb_verifie_le', true);

    echo '<style>
      .iw-seo label{display:block;font-weight:600;margin:14px 0 4px}
      .iw-seo input[type=text],.iw-seo textarea{width:100%}
      .iw-seo .iw-aide{color:#666;font-size:12px;margin-top:3px}
      .iw-seo .iw-cpt{font-variant-numeric:tabular-nums;font-weight:600}
      .iw-seo .iw-long{color:#b32d2e}
      .iw-apercu{background:#fff;border:1px solid #dcdcde;border-radius:6px;padding:12px;margin-top:16px}
      .iw-apercu .u{color:#5f6368;font-size:12px}
      .iw-apercu .t{color:#1a0dab;font-size:17px;line-height:1.3;margin:2px 0}
      .iw-apercu .d{color:#4d5156;font-size:13px}
    </style>';

    echo '<div class="iw-seo">';

    printf(
        '<label for="iw_titre">Titre SEO <span class="iw-cpt" id="iw_titre_cpt"></span></label>
         <input type="text" id="iw_titre" name="infoweb_titre_seo" value="%s" maxlength="120">
         <p class="iw-aide">Vide : le titre de la page suivi du nom du site. Cible 60 caractères.</p>',
        esc_attr($titre)
    );

    printf(
        '<label for="iw_desc">Méta description <span class="iw-cpt" id="iw_desc_cpt"></span></label>
         <textarea id="iw_desc" name="infoweb_description_seo" rows="3" maxlength="320">%s</textarea>
         <p class="iw-aide">Vide : le début du contenu, tronqué proprement. Cible 160 caractères.</p>',
        esc_textarea($desc)
    );

    printf(
        '<label for="iw_verif">Vérifié le</label>
         <input type="date" id="iw_verif" name="infoweb_verifie_le" value="%s">
         <p class="iw-aide">Date du dernier contrôle des faits et des sources. Affichée dans la signature. Distincte de la date de modification.</p>',
        esc_attr($verif)
    );

    printf(
        '<label><input type="checkbox" name="infoweb_noindex" value="1" %s> Exclure cette page de l\'index de Google</label>',
        checked($noidx, '1', false)
    );

    // Aperçu du rendu en résultat de recherche. Purement local, aucune
    // requête réseau, aucun script chargé côté public.
    printf(
        '<div class="iw-apercu"><div class="u">%s</div>
           <div class="t" id="iw_ap_t"></div><div class="d" id="iw_ap_d"></div></div>',
        esc_html(parse_url(home_url(), PHP_URL_HOST))
    );

    $titre_defaut = get_the_title($post) . ' — ' . get_bloginfo('name');
    ?>
    <script>
    (function(){
      const t=document.getElementById('iw_titre'), d=document.getElementById('iw_desc');
      const ct=document.getElementById('iw_titre_cpt'), cd=document.getElementById('iw_desc_cpt');
      const at=document.getElementById('iw_ap_t'), ad=document.getElementById('iw_ap_d');
      const tDefaut=<?php echo wp_json_encode($titre_defaut); ?>;
      function maj(){
        const vt=t.value||tDefaut, vd=d.value;
        ct.textContent=t.value.length+' / 60';
        cd.textContent=vd.length+' / 160';
        ct.className='iw-cpt'+(t.value.length>60?' iw-long':'');
        cd.className='iw-cpt'+(vd.length>160?' iw-long':'');
        at.textContent=vt;
        ad.textContent=vd||'(déduit du contenu)';
      }
      t.addEventListener('input',maj); d.addEventListener('input',maj); maj();
    })();
    </script>
    <?php
    echo '</div>';
}

add_action('save_post', 'infoweb_enregistrer_seo', 10, 2);
function infoweb_enregistrer_seo(int $post_id, WP_Post $post): void {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['infoweb_seo_nonce'])
        || !wp_verify_nonce($_POST['infoweb_seo_nonce'], 'infoweb_seo_' . $post_id)) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $titre = sanitize_text_field(wp_unslash($_POST['infoweb_titre_seo'] ?? ''));
    $desc  = sanitize_textarea_field(wp_unslash($_POST['infoweb_description_seo'] ?? ''));
    $verif = sanitize_text_field(wp_unslash($_POST['infoweb_verifie_le'] ?? ''));

    foreach ([
        '_infoweb_titre_seo'       => $titre,
        '_infoweb_description_seo' => $desc,
        '_infoweb_verifie_le'      => $verif,
    ] as $cle => $valeur) {
        $valeur === ''
            ? delete_post_meta($post_id, $cle)
            : update_post_meta($post_id, $cle, $valeur);
    }

    empty($_POST['infoweb_noindex'])
        ? delete_post_meta($post_id, '_infoweb_noindex')
        : update_post_meta($post_id, '_infoweb_noindex', '1');
}

/**
 * Mêmes champs sur les catégories, qui portent les pages familles et piliers.
 */
add_action('category_edit_form_fields', function (WP_Term $terme) {
    $titre = get_term_meta($terme->term_id, '_infoweb_titre_seo', true);
    $desc  = get_term_meta($terme->term_id, '_infoweb_description_seo', true);
    wp_nonce_field('infoweb_seo_term_' . $terme->term_id, 'infoweb_seo_term_nonce');
    printf(
        '<tr class="form-field"><th><label for="iw_t_titre">Titre SEO</label></th>
         <td><input type="text" id="iw_t_titre" name="infoweb_titre_seo" value="%s" class="regular-text">
         <p class="description">Vide : le nom de la catégorie suivi du nom du site.</p></td></tr>
         <tr class="form-field"><th><label for="iw_t_desc">Méta description</label></th>
         <td><textarea id="iw_t_desc" name="infoweb_description_seo" rows="3" class="large-text">%s</textarea>
         <p class="description">Vide : la description de la catégorie, tronquée à 160 caractères.</p></td></tr>',
        esc_attr($titre), esc_textarea($desc)
    );
});

add_action('edited_category', function (int $term_id) {
    if (!isset($_POST['infoweb_seo_term_nonce'])
        || !wp_verify_nonce($_POST['infoweb_seo_term_nonce'], 'infoweb_seo_term_' . $term_id)) {
        return;
    }
    if (!current_user_can('manage_categories')) {
        return;
    }
    foreach ([
        '_infoweb_titre_seo'       => sanitize_text_field(wp_unslash($_POST['infoweb_titre_seo'] ?? '')),
        '_infoweb_description_seo' => sanitize_textarea_field(wp_unslash($_POST['infoweb_description_seo'] ?? '')),
    ] as $cle => $valeur) {
        $valeur === ''
            ? delete_term_meta($term_id, $cle)
            : update_term_meta($term_id, $cle, $valeur);
    }
});
