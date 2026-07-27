<?php
/**
 * Données et traitements propres à la page d'accueil.
 *
 * Trois briques que le média réclame et que le contenu seul ne fournit pas :
 *   - « Par application » : l'axe sectoriel du cocon, en dur mais filtrable,
 *     car ces pages structurent la navigation avant même d'exister ;
 *   - l'agenda des salons : piloté par une option, jamais inventé — vide tant
 *     que la rédaction ne l'a pas renseigné (protocole de fiabilité) ;
 *   - la capture newsletter : réutilise la table des leads et sa purge RGPD,
 *     plutôt qu'un second stockage à sécuriser.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

/**
 * Secteurs d'application — le second axe éditorial du média.
 *
 * Depuis la 0.5.3, ce sont de vraies catégories WordPress (gérées dans
 * wp-admin), et non plus une taxonomie séparée : elles apparaissent dans le
 * menu « Par application » et reçoivent des articles comme n'importe quelle
 * catégorie. La liste ci-dessous ne sert qu'à l'affichage (libellé court des
 * contraintes) et à l'ordre sur l'accueil ; les slugs correspondent aux
 * catégories créées.
 *
 * @return array<int,array{slug:string,nom:string,note:string}>
 */
function infoweb_secteurs(): array {
    return apply_filters('infoweb_secteurs', [
        ['slug' => 'agroalimentaire',        'nom' => 'Agroalimentaire',          'note' => 'Inox · froid · HACCP'],
        ['slug' => 'sante',                  'nom' => 'Santé',                    'note' => 'Salle propre · traçabilité'],
        ['slug' => 'automobile',             'nom' => 'Automobile',               'note' => 'Cadence · ergonomie'],
        ['slug' => 'aeronautique-spatial',   'nom' => 'Aéronautique & spatial',   'note' => 'Charges longues · précision'],
        ['slug' => 'telecoms',               'nom' => 'Télécoms',                 'note' => 'Travail en hauteur'],
        ['slug' => 'environnement',          'nom' => 'Environnement',            'note' => 'Déchets · tri'],
        ['slug' => 'electronique',           'nom' => 'Électronique',             'note' => 'ESD · petits contenants'],
        ['slug' => 'metallurgie-siderurgie', 'nom' => 'Métallurgie & sidérurgie', 'note' => 'Charges lourdes · chaleur'],
    ]);
}

/**
 * Lien vers l'archive de catégorie d'un secteur.
 */
function infoweb_secteur_lien(string $slug): string {
    $cat = get_category_by_slug($slug);
    return $cat ? get_category_link($cat->term_id) : home_url('/' . $slug . '/');
}

// L'agenda (infoweb_evenements / infoweb_evenement_pastille) vit désormais
// dans inc/evenements.php : chaque salon est un contenu « Événement » avec sa
// propre page, éditable dans wp-admin.

/**
 * Capture newsletter. Même socle de sécurité que les demandes de devis
 * (nonce, champ piège, limitation de débit, hachage d'IP, purge RGPD) et
 * même table : un abonné est un lead de type « newsletter ».
 */
add_action('admin_post_nopriv_infoweb_newsletter', 'infoweb_traiter_newsletter');
add_action('admin_post_infoweb_newsletter', 'infoweb_traiter_newsletter');

function infoweb_traiter_newsletter(): void {
    $retour = wp_get_referer() ?: home_url('/');

    if (empty($_POST['infoweb_news_nonce'])
        || !wp_verify_nonce($_POST['infoweb_news_nonce'], 'infoweb_newsletter')) {
        wp_safe_redirect(add_query_arg('news', 'erreur', $retour) . '#newsletter');
        exit;
    }
    // Champ piège rempli : on feint le succès sans rien enregistrer.
    if (!empty($_POST['site_internet'])) {
        wp_safe_redirect(add_query_arg('news', 'ok', $retour) . '#newsletter');
        exit;
    }
    if (function_exists('infoweb_debit_autorise') && !infoweb_debit_autorise('newsletter', 5, 900)) {
        wp_safe_redirect(add_query_arg('news', 'trop', $retour) . '#newsletter');
        exit;
    }

    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    if (!is_email($email)) {
        wp_safe_redirect(add_query_arg('news', 'erreur', $retour) . '#newsletter');
        exit;
    }

    global $wpdb;
    $table = infoweb_table('leads');
    // Idempotent : une adresse déjà inscrite ne crée pas de doublon.
    $existe = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table} WHERE email = %s AND type_demande = 'newsletter' LIMIT 1",
        $email
    ));
    if (!$existe) {
        $maintenant = current_time('mysql');
        $mois = defined('INFOWEB_CONSERVATION_MOIS') ? INFOWEB_CONSERVATION_MOIS : 36;
        $wpdb->insert($table, [
            'type_demande'    => 'newsletter',
            'email'           => $email,
            'nom'             => '',
            'statut'          => 'nouveau',
            'page_source'     => esc_url_raw(wp_unslash($_POST['page_source'] ?? '')),
            'ip_hash'         => hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . wp_salt()),
            'consentement_at' => $maintenant,
            'purge_at'        => date('Y-m-d', strtotime("+{$mois} months")),
            'created_at'      => $maintenant,
        ]);
    }

    wp_safe_redirect(add_query_arg('news', 'ok', $retour) . '#newsletter');
    exit;
}

/**
 * Message de confirmation ou d'erreur après soumission newsletter.
 */
function infoweb_newsletter_message(): string {
    $etat = sanitize_key($_GET['news'] ?? '');
    return match ($etat) {
        'ok'     => 'Inscription enregistrée. Vous recevrez la prochaine lettre.',
        'trop'   => 'Trop de tentatives. Réessayez dans quelques minutes.',
        'erreur' => 'Adresse invalide. Vérifiez votre e-mail.',
        default  => '',
    };
}
