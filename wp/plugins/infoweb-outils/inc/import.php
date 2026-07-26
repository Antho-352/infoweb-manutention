<?php
/**
 * Import des contenus à recréer.
 *
 * Les 63 pages qui portaient encore des positions doivent être remises en
 * ligne à leur URL exacte. Les créer une par une depuis l'éditeur serait
 * long et source d'erreurs sur les slugs — or un slug faux, c'est la
 * position perdue.
 *
 * L'import lit un fichier JSON produit hors ligne et crée les articles avec
 * leur slug, leur catégorie, leur chapô et leurs champs de référencement.
 * Il est idempotent : un article dont le slug existe déjà est mis à jour,
 * jamais dupliqué.
 *
 * @package infoweb-outils
 */

defined('ABSPATH') || exit;

function infoweb_ecran_import(): void {
    if (!empty($_POST['infoweb_import_nonce'])
        && wp_verify_nonce($_POST['infoweb_import_nonce'], 'infoweb_import')
        && current_user_can('publish_posts')) {
        infoweb_traiter_import();
    }
    ?>
    <h2>Importer des contenus</h2>
    <p>Dépose le fichier <code>contenus.json</code> produit hors ligne. Chaque entrée crée
       ou met à jour un article à son URL exacte. Un slug déjà présent est mis à jour,
       jamais dupliqué : l'import est rejouable sans risque.</p>

    <form method="post" enctype="multipart/form-data">
      <?php wp_nonce_field('infoweb_import', 'infoweb_import_nonce'); ?>
      <table class="form-table">
        <tr><th><label for="fichier">Fichier JSON</label></th>
            <td><input type="file" id="fichier" name="fichier" accept=".json" required></td></tr>
        <tr><th>Statut à la création</th>
            <td><label><input type="radio" name="statut" value="draft" checked> Brouillon —
                  à relire avant publication</label><br>
                <label><input type="radio" name="statut" value="publish"> Publié immédiatement</label></td></tr>
        <tr><th>Simulation</th>
            <td><label><input type="checkbox" name="simuler" value="1" checked>
                  Afficher ce qui serait fait, sans rien écrire</label>
                <p class="description">Laisse coché au premier passage pour vérifier les slugs et les catégories.</p></td></tr>
      </table>
      <?php submit_button('Lancer l\'import'); ?>
    </form>

    <h3>Format attendu</h3>
    <pre style="background:#fff;border:1px solid #dcdcde;padding:12px;overflow:auto;max-width:900px">[
  {
    "slug": "le-gerbeur-fenwick-tout-savoir-sur-cet-appareil",
    "categorie": "gerbeur",
    "titre": "Le gerbeur Fenwick : gammes, capacités et CACES applicable",
    "chapo": "Il n'existe pas de « CACES Fenwick »…",
    "contenu": "&lt;!-- wp:paragraph --&gt;&lt;p&gt;…&lt;/p&gt;&lt;!-- /wp:paragraph --&gt;",
    "titre_seo": "facultatif",
    "description_seo": "facultatif",
    "verifie_le": "2026-07-27"
  }
]</pre>
    <p class="description">L'URL finale est <code>/{categorie}/{slug}/</code>. La catégorie doit
       exister au préalable — l'import ne crée pas de catégorie, pour éviter d'en semer
       par faute de frappe.</p>
    <?php
}

function infoweb_traiter_import(): void {
    if (empty($_FILES['fichier']['tmp_name']) || !is_uploaded_file($_FILES['fichier']['tmp_name'])) {
        echo '<div class="notice notice-error"><p>Aucun fichier reçu.</p></div>';
        return;
    }

    $brut = file_get_contents($_FILES['fichier']['tmp_name']);
    $entrees = json_decode($brut, true);
    if (!is_array($entrees)) {
        printf('<div class="notice notice-error"><p>JSON illisible : %s</p></div>',
            esc_html(json_last_error_msg()));
        return;
    }

    $simuler = !empty($_POST['simuler']);
    $statut  = ($_POST['statut'] ?? 'draft') === 'publish' ? 'publish' : 'draft';
    $rapport = ['crees' => 0, 'maj' => 0, 'ignores' => 0, 'lignes' => []];

    foreach ($entrees as $i => $e) {
        $slug = sanitize_title($e['slug'] ?? '');
        $titre = sanitize_text_field($e['titre'] ?? '');
        $cat_slug = sanitize_title($e['categorie'] ?? '');

        if ($slug === '' || $titre === '') {
            $rapport['ignores']++;
            $rapport['lignes'][] = ['?', 'ignoré', 'slug ou titre manquant (entrée ' . ((int) $i + 1) . ')'];
            continue;
        }

        $cat = $cat_slug !== '' ? get_category_by_slug($cat_slug) : null;
        if ($cat_slug !== '' && !$cat) {
            $rapport['ignores']++;
            $rapport['lignes'][] = [$slug, 'ignoré', "catégorie « {$cat_slug} » inexistante"];
            continue;
        }

        $existant = get_page_by_path($slug, OBJECT, 'post');
        $action = $existant ? 'mis à jour' : 'créé';

        if (!$simuler) {
            $donnees = [
                'post_type'      => 'post',
                'post_name'      => $slug,
                'post_title'     => $titre,
                'post_excerpt'   => sanitize_textarea_field($e['chapo'] ?? ''),
                'post_content'   => wp_kses_post($e['contenu'] ?? ''),
                'comment_status' => 'closed',
                'ping_status'    => 'closed',
            ];
            if ($existant) {
                $donnees['ID'] = $existant->ID;
                // On ne repasse pas un article publié en brouillon.
                $id = wp_update_post($donnees, true);
            } else {
                $donnees['post_status'] = $statut;
                $id = wp_insert_post($donnees, true);
            }

            if (is_wp_error($id)) {
                $rapport['ignores']++;
                $rapport['lignes'][] = [$slug, 'erreur', $id->get_error_message()];
                continue;
            }

            if ($cat) {
                wp_set_post_categories($id, [$cat->term_id], false);
            }
            foreach ([
                '_infoweb_titre_seo'       => sanitize_text_field($e['titre_seo'] ?? ''),
                '_infoweb_description_seo' => sanitize_textarea_field($e['description_seo'] ?? ''),
                '_infoweb_verifie_le'      => sanitize_text_field($e['verifie_le'] ?? ''),
            ] as $cle => $valeur) {
                $valeur === '' ? delete_post_meta($id, $cle) : update_post_meta($id, $cle, $valeur);
            }
        }

        $existant ? $rapport['maj']++ : $rapport['crees']++;
        $rapport['lignes'][] = [
            ($cat_slug ? '/' . $cat_slug : '') . '/' . $slug . '/',
            $action,
            $cat ? $cat->name : 'sans catégorie',
        ];
    }

    printf(
        '<div class="notice notice-%s"><p><strong>%s</strong> — %d à créer, %d à mettre à jour, %d ignoré(s).</p></div>',
        $rapport['ignores'] ? 'warning' : 'success',
        $simuler ? 'Simulation, rien n\'a été écrit' : 'Import terminé',
        $rapport['crees'], $rapport['maj'], $rapport['ignores']
    );

    echo '<table class="wp-list-table widefat striped" style="max-width:900px"><thead><tr>'
       . '<th>URL</th><th>Action</th><th>Détail</th></tr></thead><tbody>';
    foreach ($rapport['lignes'] as [$url, $action, $detail]) {
        printf('<tr><td><code>%s</code></td><td>%s</td><td>%s</td></tr>',
            esc_html($url), esc_html($action), esc_html($detail));
    }
    echo '</tbody></table>';
}
