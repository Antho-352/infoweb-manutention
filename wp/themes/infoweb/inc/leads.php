<?php
/**
 * Demandes de devis : formulaire, enregistrement, administration, export.
 *
 * Brique interne volontairement minimale — pas de CRM, pas de dépendance.
 * Une table, un écran, un export.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

const INFOWEB_STATUTS = [
    'nouveau'  => 'Nouveau',
    'transmis' => 'Transmis',
    'qualifie' => 'Qualifié',
    'signe'    => 'Signé',
    'perdu'    => 'Perdu',
    'spam'     => 'Spam',
];

// Durée de conservation des demandes non signées.
const INFOWEB_CONSERVATION_MOIS = 24;

/**
 * Rendu du formulaire. [devis machine="chariot-elevateur" titre="..."]
 */
add_shortcode('devis', function ($atts) {
    $a = shortcode_atts([
        'machine' => '',
        'titre'   => 'Recevoir 3 devis',
        'soustitre' => 'Gratuit, sans engagement, réponse sous 48 h',
    ], $atts, 'devis');

    $envoye = isset($_GET['devis']) && $_GET['devis'] === 'ok';
    ob_start(); ?>
    <div class="form-devis" id="devis">
      <div class="fd-h">
        <span class="t"><?php echo esc_html($a['titre']); ?></span>
        <p><?php echo esc_html($a['soustitre']); ?></p>
      </div>

      <?php if ($envoye) : ?>
        <div class="fd-b">
          <p class="fd-ok"><strong>Votre demande est enregistrée.</strong>
             Vous serez rappelé sous 24 à 48 heures ouvrées.</p>
        </div>
      <?php else : ?>
        <form class="fd-b" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="infoweb_devis">
          <input type="hidden" name="page_source" value="<?php echo esc_attr(get_permalink()); ?>">
          <input type="hidden" name="machine" value="<?php echo esc_attr($a['machine']); ?>">
          <?php wp_nonce_field('infoweb_devis', 'infoweb_devis_nonce'); ?>

          <?php // Piège à robots : un champ que seul un automate remplit. ?>
          <div class="fd-piege" aria-hidden="true">
            <label>Ne pas remplir<input type="text" name="site_internet" tabindex="-1" autocomplete="off"></label>
          </div>

          <label class="fd-l"><span>Votre besoin</span>
            <select name="type_demande">
              <option value="devis_location">Louer du matériel</option>
              <option value="devis_achat">Acheter du matériel</option>
              <option value="devis_sav">Réparation ou maintenance</option>
              <option value="devis_vgp">Contrôle VGP</option>
            </select></label>

          <label class="fd-l"><span>Capacité de charge</span>
            <input name="capacite" placeholder="Ex. 2,5 t"></label>

          <label class="fd-l"><span>Durée</span>
            <select name="duree">
              <option>Journée</option><option>Semaine</option>
              <option>Mois et plus</option><option>Achat</option>
            </select></label>

          <label class="fd-l"><span>Département</span>
            <input name="departement" placeholder="Ex. 57" maxlength="3" required></label>

          <label class="fd-l"><span>Entreprise</span>
            <input name="entreprise" placeholder="Raison sociale"></label>

          <label class="fd-l"><span>Nom</span>
            <input name="nom" required autocomplete="name"></label>

          <label class="fd-l"><span>E-mail professionnel</span>
            <input name="email" type="email" required autocomplete="email"></label>

          <label class="fd-l"><span>Téléphone</span>
            <input name="telephone" placeholder="Pour être rappelé" autocomplete="tel"></label>

          <label class="fd-l"><span>Précisions</span>
            <textarea name="message" rows="3" placeholder="Hauteur de levée, environnement, contraintes…"></textarea></label>

          <label class="fd-c"><input type="checkbox" name="consentement" value="1" required>
            <span>J'accepte que mes coordonnées soient transmises aux prestataires
                  sélectionnés pour répondre à cette demande.</span></label>

          <button type="submit" class="fd-sub">Envoyer la demande</button>

          <p class="fd-rgpd">Vos données sont conservées <?php echo (int) INFOWEB_CONSERVATION_MOIS; ?> mois
            et ne sont jamais revendues. Suppression sur simple demande à
            <?php echo esc_html(get_option('admin_email')); ?>.</p>
        </form>
      <?php endif; ?>

      <div class="fd-trust">
        <div>Trois prestataires contactés au maximum</div>
        <div>Aucune revente de vos données à des tiers</div>
        <div>Média indépendant : aucun loueur au capital</div>
      </div>
    </div>
    <?php
    return ob_get_clean();
});

/**
 * Traitement de l'envoi. Aucun rendu ici : on redirige toujours, pour qu'un
 * rafraîchissement ne renvoie pas le formulaire.
 */
add_action('admin_post_nopriv_infoweb_devis', 'infoweb_traiter_devis');
add_action('admin_post_infoweb_devis', 'infoweb_traiter_devis');

function infoweb_traiter_devis(): void {
    $retour = wp_get_referer() ?: home_url('/');

    if (empty($_POST['infoweb_devis_nonce'])
        || !wp_verify_nonce($_POST['infoweb_devis_nonce'], 'infoweb_devis')) {
        wp_safe_redirect(add_query_arg('devis', 'erreur', $retour));
        exit;
    }
    // Champ piège rempli : automate. On feint le succès pour ne pas
    // renseigner le robot sur la détection.
    if (!empty($_POST['site_internet'])) {
        wp_safe_redirect(add_query_arg('devis', 'ok', $retour) . '#devis');
        exit;
    }
    if (!infoweb_debit_autorise('devis', 5, 900)) {
        wp_safe_redirect(add_query_arg('devis', 'trop', $retour));
        exit;
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $nom   = sanitize_text_field(wp_unslash($_POST['nom'] ?? ''));
    if (!is_email($email) || $nom === '' || empty($_POST['consentement'])) {
        wp_safe_redirect(add_query_arg('devis', 'erreur', $retour));
        exit;
    }

    global $wpdb;
    $maintenant = current_time('mysql');
    $wpdb->insert(infoweb_table('leads'), [
        'type_demande'    => sanitize_key(wp_unslash($_POST['type_demande'] ?? 'devis')),
        'machine'         => sanitize_text_field(wp_unslash($_POST['machine'] ?? '')),
        'capacite'        => sanitize_text_field(wp_unslash($_POST['capacite'] ?? '')),
        'duree'           => sanitize_text_field(wp_unslash($_POST['duree'] ?? '')),
        'departement'     => substr(sanitize_text_field(wp_unslash($_POST['departement'] ?? '')), 0, 3),
        'entreprise'      => sanitize_text_field(wp_unslash($_POST['entreprise'] ?? '')),
        'nom'             => $nom,
        'email'           => $email,
        'telephone'       => sanitize_text_field(wp_unslash($_POST['telephone'] ?? '')),
        'message'         => sanitize_textarea_field(wp_unslash($_POST['message'] ?? '')),
        'page_source'     => esc_url_raw(wp_unslash($_POST['page_source'] ?? '')),
        'statut'          => 'nouveau',
        // l'adresse n'est jamais conservée en clair
        'ip_hash'         => hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . wp_salt()),
        'consentement_at' => $maintenant,
        'purge_at'        => date('Y-m-d', strtotime("+" . INFOWEB_CONSERVATION_MOIS . " months")),
        'created_at'      => $maintenant,
    ]);

    $sujet = sprintf('[%s] Nouvelle demande de devis — %s', get_bloginfo('name'), $nom);
    $corps = "Nom : {$nom}\nE-mail : {$email}\n"
           . 'Téléphone : ' . sanitize_text_field(wp_unslash($_POST['telephone'] ?? '')) . "\n"
           . 'Entreprise : ' . sanitize_text_field(wp_unslash($_POST['entreprise'] ?? '')) . "\n"
           . 'Département : ' . sanitize_text_field(wp_unslash($_POST['departement'] ?? '')) . "\n"
           . 'Machine : ' . sanitize_text_field(wp_unslash($_POST['machine'] ?? '')) . "\n\n"
           . sanitize_textarea_field(wp_unslash($_POST['message'] ?? '')) . "\n\n"
           . 'Page : ' . esc_url_raw(wp_unslash($_POST['page_source'] ?? '')) . "\n"
           . admin_url('admin.php?page=infoweb-leads');
    wp_mail(get_option('admin_email'), $sujet, $corps);

    wp_safe_redirect(add_query_arg('devis', 'ok', $retour) . '#devis');
    exit;
}

/**
 * Écran des demandes.
 */
add_action('admin_menu', function () {
    $n = infoweb_leads_nouveaux();
    add_menu_page(
        'Demandes de devis',
        $n ? sprintf('Demandes <span class="update-plugin-count">%d</span>', $n) : 'Demandes',
        'edit_posts', 'infoweb-leads', 'infoweb_ecran_leads', 'dashicons-email-alt', 26
    );
});

function infoweb_leads_nouveaux(): int {
    global $wpdb;
    $t = infoweb_table('leads');
    return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$t} WHERE statut = 'nouveau'");
}

function infoweb_ecran_leads(): void {
    if (!current_user_can('edit_posts')) {
        wp_die('Accès refusé.');
    }
    global $wpdb;
    $t = infoweb_table('leads');

    // changement de statut
    if (!empty($_POST['infoweb_leads_nonce'])
        && wp_verify_nonce($_POST['infoweb_leads_nonce'], 'infoweb_leads')
        && !empty($_POST['id'])) {
        $statut = sanitize_key(wp_unslash($_POST['statut'] ?? ''));
        if (isset(INFOWEB_STATUTS[$statut])) {
            $wpdb->update($t, ['statut' => $statut], ['id' => (int) $_POST['id']]);
            echo '<div class="notice notice-success"><p>Statut mis à jour.</p></div>';
        }
    }

    $filtre = sanitize_key($_GET['statut'] ?? '');
    $where = isset(INFOWEB_STATUTS[$filtre]) ? $wpdb->prepare('WHERE statut = %s', $filtre) : '';
    $lignes = $wpdb->get_results("SELECT * FROM {$t} {$where} ORDER BY created_at DESC LIMIT 300", ARRAY_A) ?: [];
    ?>
    <div class="wrap">
      <h1>Demandes de devis</h1>

      <ul class="subsubsub">
        <li><a href="<?php echo esc_url(admin_url('admin.php?page=infoweb-leads')); ?>">Toutes</a> |</li>
        <?php foreach (INFOWEB_STATUTS as $cle => $lib) : ?>
          <li><a href="<?php echo esc_url(admin_url('admin.php?page=infoweb-leads&statut=' . $cle)); ?>"><?php echo esc_html($lib); ?></a> |</li>
        <?php endforeach; ?>
      </ul>

      <p><a class="button" href="<?php echo esc_url(wp_nonce_url(
            admin_url('admin-post.php?action=infoweb_export_leads' . ($filtre ? '&statut=' . $filtre : '')),
            'infoweb_export')); ?>">Exporter en CSV</a></p>

      <table class="wp-list-table widefat striped">
        <thead><tr><th>Date</th><th>Contact</th><th>Demande</th><th>Zone</th><th>Statut</th></tr></thead>
        <tbody>
        <?php if (!$lignes) : ?><tr><td colspan="5">Aucune demande.</td></tr><?php endif; ?>
        <?php foreach ($lignes as $l) : ?>
          <tr>
            <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($l['created_at']))); ?></td>
            <td><strong><?php echo esc_html($l['nom']); ?></strong><br>
                <?php if ($l['entreprise']) : ?><?php echo esc_html($l['entreprise']); ?><br><?php endif; ?>
                <a href="mailto:<?php echo esc_attr($l['email']); ?>"><?php echo esc_html($l['email']); ?></a>
                <?php if ($l['telephone']) : ?><br><?php echo esc_html($l['telephone']); ?><?php endif; ?></td>
            <td><?php echo esc_html($l['type_demande']); ?>
                <?php if ($l['machine']) : ?><br><?php echo esc_html($l['machine']); ?><?php endif; ?>
                <?php if ($l['capacite'] || $l['duree']) : ?><br><small><?php echo esc_html(trim($l['capacite'] . ' ' . $l['duree'])); ?></small><?php endif; ?>
                <?php if ($l['message']) : ?><br><small><?php echo esc_html(wp_trim_words($l['message'], 18, '…')); ?></small><?php endif; ?></td>
            <td><?php echo esc_html($l['departement']); ?></td>
            <td>
              <form method="post" style="display:flex;gap:5px">
                <?php wp_nonce_field('infoweb_leads', 'infoweb_leads_nonce'); ?>
                <input type="hidden" name="id" value="<?php echo (int) $l['id']; ?>">
                <select name="statut">
                  <?php foreach (INFOWEB_STATUTS as $cle => $lib) : ?>
                    <option value="<?php echo esc_attr($cle); ?>" <?php selected($l['statut'], $cle); ?>><?php echo esc_html($lib); ?></option>
                  <?php endforeach; ?>
                </select>
                <button class="button button-small">OK</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
}

/**
 * Export CSV : point-virgule et BOM UTF-8, pour qu'Excel en français ouvre
 * le fichier correctement sans passer par l'assistant d'importation.
 */
add_action('admin_post_infoweb_export_leads', function () {
    if (!current_user_can('edit_posts') || !wp_verify_nonce($_GET['_wpnonce'] ?? '', 'infoweb_export')) {
        wp_die('Accès refusé.');
    }
    global $wpdb;
    $t = infoweb_table('leads');
    $filtre = sanitize_key($_GET['statut'] ?? '');
    $where = isset(INFOWEB_STATUTS[$filtre]) ? $wpdb->prepare('WHERE statut = %s', $filtre) : '';
    $lignes = $wpdb->get_results("SELECT * FROM {$t} {$where} ORDER BY created_at DESC", ARRAY_A) ?: [];

    nocache_headers();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename=demandes-' . date('Y-m-d') . '.csv');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");
    $colonnes = ['created_at','statut','type_demande','machine','capacite','duree',
                 'departement','entreprise','nom','email','telephone','message','page_source'];
    fputcsv($out, $colonnes, ';');
    foreach ($lignes as $l) {
        fputcsv($out, array_map(fn($c) => $l[$c] ?? '', $colonnes), ';');
    }
    fclose($out);
    exit;
});
