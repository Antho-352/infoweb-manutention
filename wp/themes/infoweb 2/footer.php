<?php
/**
 * Pied de page.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;
?>
</main>

<footer class="ftr">
  <div class="w ftr-in">
    <span>© <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?> — média indépendant de la manutention et du levage</span>
    <?php
    if (has_nav_menu('pied')) {
        wp_nav_menu([
            'theme_location' => 'pied',
            'container'      => false,
            'depth'          => 1,
            'fallback_cb'    => false,
        ]);
    }
    ?>
  </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
