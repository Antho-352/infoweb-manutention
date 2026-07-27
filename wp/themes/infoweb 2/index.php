<?php
/**
 * Gabarit de repli. WordPress l'exige ; il sert aussi la page de blog et
 * les archives qui n'ont pas de gabarit dédié.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header(); ?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>

  <div class="tete-arch">
    <h1><?php echo esc_html(wp_get_document_title()); ?></h1>
  </div>

  <?php if (have_posts()) : ?>
    <div class="grille">
      <?php while (have_posts()) : the_post(); ?>
        <a class="carte" href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()) : ?>
            <span class="vign"><?php the_post_thumbnail('infoweb-carte', ['loading' => 'lazy']); ?></span>
          <?php endif; ?>
          <h3><?php the_title(); ?></h3>
          <span class="meta"><?php echo esc_html(get_the_date()); ?></span>
        </a>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 2, 'class' => 'pagination']); ?>
  <?php else : ?>
    <p>Aucune publication pour l'instant.</p>
  <?php endif; ?>
</div>

<?php get_footer();
