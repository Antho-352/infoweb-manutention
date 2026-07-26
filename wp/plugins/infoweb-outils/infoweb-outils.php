<?php
/**
 * Plugin Name: Infoweb Outils
 * Plugin URI: https://github.com/Antho-352/infoweb-manutention
 * Description: Redirections des URLs héritées, structure d'URL sans base de catégorie, et import des contenus à recréer. S'installe depuis l'administration, sans accès au serveur.
 * Version: 1.0.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Anthony Russo
 * License: GPL-2.0-or-later
 *
 * Ces briques sont dans un plugin et non dans le thème : elles doivent
 * survivre à un changement de thème. Une redirection perdue coûte des
 * positions, pas seulement de la mise en forme.
 *
 * @package infoweb-outils
 */

defined('ABSPATH') || exit;

const INFOWEB_OUTILS_VERSION = '1.0.0';
define('INFOWEB_OUTILS_DIR', plugin_dir_path(__FILE__));

foreach (['redirections', 'url-structure', 'import'] as $module) {
    require_once INFOWEB_OUTILS_DIR . "inc/{$module}.php";
}

register_activation_hook(__FILE__, function () {
    infoweb_outils_vider_regles();
});
register_deactivation_hook(__FILE__, function () {
    flush_rewrite_rules(false);
});

/**
 * Page de réglages unique, qui regroupe l'état des redirections et
 * l'import. Une entrée de menu, pas trois.
 */
add_action('admin_menu', function () {
    add_management_page(
        'Infoweb Outils', 'Infoweb Outils', 'manage_options',
        'infoweb-outils', 'infoweb_outils_ecran'
    );
});

function infoweb_outils_ecran(): void {
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé.');
    }
    $onglet = sanitize_key($_GET['onglet'] ?? 'import');
    ?>
    <div class="wrap">
      <h1>Infoweb Outils</h1>
      <nav class="nav-tab-wrapper">
        <a href="<?php echo esc_url(admin_url('tools.php?page=infoweb-outils&onglet=import')); ?>"
           class="nav-tab <?php echo $onglet === 'import' ? 'nav-tab-active' : ''; ?>">Import de contenus</a>
        <a href="<?php echo esc_url(admin_url('tools.php?page=infoweb-outils&onglet=redirections')); ?>"
           class="nav-tab <?php echo $onglet === 'redirections' ? 'nav-tab-active' : ''; ?>">Redirections</a>
      </nav>
      <?php
      $onglet === 'redirections' ? infoweb_ecran_redirections() : infoweb_ecran_import();
      ?>
    </div>
    <?php
}
