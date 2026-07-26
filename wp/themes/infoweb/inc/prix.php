<?php
/**
 * Points de prix.
 *
 * Aucun prix n'est écrit en dur dans un article : tous vivent ici, et sont
 * injectés par le code court [prix]. Une révision met donc à jour toutes les
 * pages qui citent ce prix, en une écriture. Sans cette mécanique, réviser
 * les prix voudrait dire rouvrir cent articles — et la promesse de fourchettes
 * datées deviendrait une dette.
 *
 * Voir docs/protocole-fiabilite.md §2.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

function infoweb_prix_get(string $slug): ?array {
    global $wpdb;
    $t = infoweb_table('prix');
    $ligne = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM {$t} WHERE slug = %s AND actif = 1", $slug),
        ARRAY_A
    );
    return $ligne ?: null;
}

function infoweb_prix_famille(string $famille, string $mode = ''): array {
    global $wpdb;
    $t = infoweb_table('prix');
    $sql = "SELECT * FROM {$t} WHERE famille = %s AND actif = 1";
    $args = [$famille];
    if ($mode !== '') {
        $sql .= ' AND mode = %s';
        $args[] = $mode;
    }
    $sql .= ' ORDER BY montant_min ASC';
    return $wpdb->get_results($wpdb->prepare($sql, ...$args), ARRAY_A) ?: [];
}

function infoweb_prix_format(float $v): string {
    return number_format($v, 0, ',', ' ') . ' €';
}

const INFOWEB_MODES = [
    'achat'            => '',
    'location_jour'    => ' / jour',
    'location_semaine' => ' / semaine',
    'location_mois'    => ' / mois',
    'lld_mois'         => ' / mois en LLD',
    'prestation'       => '',
];

/**
 * [prix slug="chariot-electrique-2t-achat"]
 * [prix famille="chariot-elevateur" mode="location_jour" titre="Tarifs de location"]
 *
 * Une fourchette non datée est inexploitable : la date de constat est donc
 * rendue systématiquement, et n'est pas optionnelle.
 */
add_shortcode('prix', function ($atts) {
    $a = shortcode_atts([
        'slug'    => '',
        'famille' => '',
        'mode'    => '',
        'titre'   => 'Repères de prix',
    ], $atts, 'prix');

    $lignes = $a['slug'] !== ''
        ? array_filter([infoweb_prix_get($a['slug'])])
        : ($a['famille'] !== '' ? infoweb_prix_famille($a['famille'], $a['mode']) : []);

    if (!$lignes) {
        // Silencieux côté public : un code court orphelin ne doit pas
        // afficher d'erreur au visiteur. Visible seulement en édition.
        return current_user_can('edit_posts')
            ? '<p class="norme"><span class="t">Bloc prix</span>Aucun point de prix ne correspond à ce code court.</p>'
            : '';
    }

    $date_max = max(array_column($lignes, 'constate_le'));
    $perimetres = array_filter(array_unique(array_column($lignes, 'perimetre')));

    ob_start(); ?>
    <div class="bloc-prix">
      <div class="bp-h">
        <span><?php echo esc_html($a['titre']); ?></span>
        <span><?php echo esc_html(date_i18n('m · Y', strtotime($date_max))); ?></span>
      </div>
      <?php foreach ($lignes as $l) : ?>
        <div class="bp-r">
          <span><?php echo esc_html($l['libelle']); ?>
            <?php if ($l['perimetre']) : ?><small><?php echo esc_html($l['perimetre']); ?></small><?php endif; ?>
          </span>
          <span class="bp-v"><?php
            echo esc_html(infoweb_prix_format((float) $l['montant_min'])
               . ' – ' . infoweb_prix_format((float) $l['montant_max'])
               . (INFOWEB_MODES[$l['mode']] ?? ''));
          ?></span>
        </div>
      <?php endforeach; ?>
      <div class="bp-f">
        Fourchettes indicatives HT constatées le <?php echo esc_html(date_i18n('j F Y', strtotime($date_max))); ?><?php
        if ($perimetres) { echo ', ' . esc_html(strtolower(implode(', ', $perimetres))); }
        ?>. Ne constituent pas une offre commerciale.
      </div>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Écran d'administration : liste, édition et alerte de péremption.
 * Un point non révisé depuis plus de six mois est signalé ; au-delà de
 * douze, il ne doit plus être publié tel quel.
 */
add_action('admin_menu', function () {
    add_menu_page(
        'Points de prix', 'Prix', 'manage_options', 'infoweb-prix',
        'infoweb_ecran_prix', 'dashicons-tag', 58
    );
});

function infoweb_ecran_prix(): void {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé.');
    }
    global $wpdb;
    $t = infoweb_table('prix');

    if (!empty($_POST['infoweb_prix_nonce'])
        && wp_verify_nonce($_POST['infoweb_prix_nonce'], 'infoweb_prix')) {

        $slug = sanitize_title(wp_unslash($_POST['slug'] ?? ''));
        if ($slug !== '') {
            $donnees = [
                'slug'         => $slug,
                'libelle'      => sanitize_text_field(wp_unslash($_POST['libelle'] ?? '')),
                'famille'      => sanitize_title(wp_unslash($_POST['famille'] ?? '')),
                'mode'         => sanitize_key(wp_unslash($_POST['mode'] ?? 'achat')),
                'montant_min'  => (float) str_replace(',', '.', wp_unslash($_POST['montant_min'] ?? 0)),
                'montant_max'  => (float) str_replace(',', '.', wp_unslash($_POST['montant_max'] ?? 0)),
                'perimetre'    => sanitize_text_field(wp_unslash($_POST['perimetre'] ?? '')),
                'source'       => sanitize_text_field(wp_unslash($_POST['source'] ?? '')),
                'nb_releves'   => max(1, (int) ($_POST['nb_releves'] ?? 1)),
                'constate_le'  => sanitize_text_field(wp_unslash($_POST['constate_le'] ?? current_time('Y-m-d'))),
                'revue_prevue' => sanitize_text_field(wp_unslash($_POST['revue_prevue'] ?? '')),
                'actif'        => empty($_POST['actif']) ? 0 : 1,
            ];
            if ($donnees['revue_prevue'] === '') {
                $donnees['revue_prevue'] = date('Y-m-d', strtotime($donnees['constate_le'] . ' +6 months'));
            }
            // upsert sur le slug : ré-enregistrer ne duplique jamais
            $wpdb->replace($t, $donnees);
            echo '<div class="notice notice-success"><p>Point de prix enregistré.</p></div>';
        }
    }

    $lignes = $wpdb->get_results("SELECT * FROM {$t} ORDER BY famille, montant_min", ARRAY_A) ?: [];
    $aujourdhui = current_time('Y-m-d');
    ?>
    <div class="wrap">
      <h1>Points de prix</h1>
      <p>Tous les prix affichés sur le site viennent d'ici. Une révision met à jour
         toutes les pages concernées. Cible : 40 à 60 points, revus deux fois par an.</p>

      <h2>Ajouter ou modifier</h2>
      <form method="post">
        <?php wp_nonce_field('infoweb_prix', 'infoweb_prix_nonce'); ?>
        <table class="form-table">
          <tr><th><label for="p_slug">Identifiant</label></th>
              <td><input id="p_slug" name="slug" class="regular-text" required
                         placeholder="chariot-electrique-2t-achat">
                  <p class="description">Sert au code court. Réutiliser un identifiant existant le met à jour.</p></td></tr>
          <tr><th><label for="p_lib">Libellé</label></th>
              <td><input id="p_lib" name="libelle" class="regular-text" required
                         placeholder="Chariot élévateur électrique 2 t, neuf"></td></tr>
          <tr><th><label for="p_fam">Famille</label></th>
              <td><input id="p_fam" name="famille" class="regular-text" required placeholder="chariot-elevateur"></td></tr>
          <tr><th><label for="p_mode">Mode</label></th>
              <td><select id="p_mode" name="mode">
                <?php foreach (array_keys(INFOWEB_MODES) as $m) : ?>
                  <option value="<?php echo esc_attr($m); ?>"><?php echo esc_html($m); ?></option>
                <?php endforeach; ?>
              </select></td></tr>
          <tr><th>Fourchette (€ HT)</th>
              <td><input name="montant_min" type="number" step="0.01" required placeholder="min"> —
                  <input name="montant_max" type="number" step="0.01" required placeholder="max"></td></tr>
          <tr><th><label for="p_per">Périmètre</label></th>
              <td><input id="p_per" name="perimetre" class="large-text"
                         placeholder="hors options, transport et mise en service"></td></tr>
          <tr><th><label for="p_src">Source</label></th>
              <td><input id="p_src" name="source" class="large-text" placeholder="Manutan, ManoMano — relevé marchand">
                  <input name="nb_releves" type="number" min="1" value="1" style="width:70px">
                  <span class="description">nombre de relevés — minimum 3 si la source est un agrégat de devis</span></td></tr>
          <tr><th><label for="p_date">Constaté le</label></th>
              <td><input id="p_date" name="constate_le" type="date" value="<?php echo esc_attr($aujourdhui); ?>" required>
                  <label style="margin-left:14px"><input type="checkbox" name="actif" value="1" checked> Actif</label></td></tr>
        </table>
        <?php submit_button('Enregistrer'); ?>
      </form>

      <h2>Points enregistrés (<?php echo count($lignes); ?>)</h2>
      <table class="wp-list-table widefat striped">
        <thead><tr><th>Identifiant</th><th>Libellé</th><th>Famille</th><th>Mode</th>
                   <th>Fourchette</th><th>Constaté</th><th>Revue</th></tr></thead>
        <tbody>
        <?php if (!$lignes) : ?>
          <tr><td colspan="7">Aucun point de prix. Le code court [prix] restera silencieux tant qu'il n'y en a pas.</td></tr>
        <?php endif; ?>
        <?php foreach ($lignes as $l) :
          $perime = $l['revue_prevue'] < $aujourdhui; ?>
          <tr<?php echo $perime ? ' style="background:#fcf0ef"' : ''; ?>>
            <td><code><?php echo esc_html($l['slug']); ?></code></td>
            <td><?php echo esc_html($l['libelle']); ?></td>
            <td><?php echo esc_html($l['famille']); ?></td>
            <td><?php echo esc_html($l['mode']); ?></td>
            <td><?php echo esc_html(infoweb_prix_format((float) $l['montant_min']) . ' – ' . infoweb_prix_format((float) $l['montant_max'])); ?></td>
            <td><?php echo esc_html($l['constate_le']); ?></td>
            <td><?php echo esc_html($l['revue_prevue']); ?><?php echo $perime ? ' <strong>à réviser</strong>' : ''; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}

/**
 * Points de prix les plus récemment constatés, pour le bloc de l'accueil.
 */
function infoweb_prix_recents(int $n = 6): array {
    global $wpdb;
    $t = infoweb_table('prix');
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$t} WHERE actif = 1 ORDER BY constate_le DESC, id DESC LIMIT %d",
            $n
        ),
        ARRAY_A
    ) ?: [];
}
