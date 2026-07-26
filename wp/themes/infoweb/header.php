<?php
/**
 * En-tête.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip" href="#contenu">Aller au contenu</a>

<header class="hdr">
  <div class="w hdr-in">
    <?php if (has_custom_logo()) : ?>
      <?php the_custom_logo(); ?>
    <?php else : ?>
      <a class="marque" href="<?php echo esc_url(home_url('/')); ?>" rel="home">infoweb<i>·</i>manutention</a>
    <?php endif; ?>

    <?php
    if (has_nav_menu('principal')) {
        wp_nav_menu([
            'theme_location' => 'principal',
            'container'      => 'nav',
            'container_class'=> 'nav-p',
            'container_aria_label' => 'Navigation principale',
            'depth'          => 2,
            'fallback_cb'    => false,
        ]);
    }
    ?>

    <div class="hdr-act">
      <a class="btn" href="<?php echo esc_url(home_url('/newsletter/')); ?>">Newsletter</a>
      <a class="btn btn-plein" href="<?php echo esc_url(home_url('/annuaire/')); ?>">
        <span class="dsk">Trouver un prestataire</span>
      </a>
    </div>
  </div>
</header>

<main id="contenu">
