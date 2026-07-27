<?php
/**
 * Durcissement.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('send_headers', function () {
    if (is_admin()) {
        return;
    }
    $entetes = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options'        => 'SAMEORIGIN',
        'Referrer-Policy'        => 'strict-origin-when-cross-origin',
        'Permissions-Policy'     => 'geolocation=(self), camera=(), microphone=(), payment=()',
    ];
    if (is_ssl()) {
        $entetes['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
    }
    foreach (apply_filters('infoweb_entetes_securite', $entetes) as $cle => $valeur) {
        header("{$cle}: {$valeur}");
    }
});

/**
 * Énumération d'auteurs par /?author=N : elle sert à découvrir les
 * identifiants de connexion valides avant une attaque par force brute.
 */
add_action('template_redirect', function () {
    if (!is_admin() && isset($_GET['author']) && !is_user_logged_in()) {
        wp_safe_redirect(home_url('/'), 301);
        exit;
    }
});

/**
 * Messages d'erreur de connexion : le message natif indique si l'identifiant
 * existe, ce qui confirme une cible à l'attaquant.
 */
add_filter('login_errors', fn() => 'Identifiants incorrects.');

/**
 * L'API REST reste ouverte pour l'administration et les outils connectés,
 * mais les listes d'utilisateurs ne sont pas publiques.
 */
add_filter('rest_endpoints', function (array $routes) {
    if (!is_user_logged_in()) {
        unset($routes['/wp/v2/users'], $routes['/wp/v2/users/(?P<id>[\d]+)']);
    }
    return $routes;
});

/**
 * Édition de fichiers depuis l'administration : une élévation de privilège
 * sur un compte éditeur permettrait sinon d'exécuter du code arbitraire.
 */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/**
 * Limitation de débit sur les formulaires publics, par empreinte d'IP hachée.
 * Le hachage évite de conserver des adresses en clair (RGPD) tout en
 * permettant de compter les tentatives.
 */
function infoweb_debit_autorise(string $action, int $max = 5, int $fenetre = 600): bool {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $cle = 'iw_debit_' . $action . '_' . hash('sha256', $ip . wp_salt());
    $n = (int) get_transient($cle);
    if ($n >= $max) {
        return false;
    }
    set_transient($cle, $n + 1, $fenetre);
    return true;
}
