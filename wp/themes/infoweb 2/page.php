<?php
/**
 * Gabarit de page.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header(); ?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>
  <div class="art">
    <article <?php post_class(); ?>>
      <h1><?php the_title(); ?></h1>
      <?php if (has_excerpt()) : ?>
        <p class="chapo"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>
      <div class="corps"><?php the_content(); ?></div>
    </article>
  </div>
</div>

<?php get_footer();
