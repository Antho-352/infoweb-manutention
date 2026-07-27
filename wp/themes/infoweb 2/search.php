<?php
/**
 * Résultats de recherche.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header(); ?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>
  <div class="tete-arch">
    <h1>Recherche : <?php echo esc_html(get_search_query()); ?></h1>
    <p class="intro"><?php echo esc_html($GLOBALS['wp_query']->found_posts); ?> résultat(s)</p>
    <?php get_search_form(); ?>
  </div>

  <?php if (have_posts()) : ?>
    <div class="grille">
      <?php while (have_posts()) : the_post(); ?>
        <a class="carte" href="<?php the_permalink(); ?>">
          <h3><?php the_title(); ?></h3>
          <?php if (has_excerpt()) : ?>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20, '…')); ?></p>
          <?php endif; ?>
          <span class="meta"><?php echo esc_html(get_the_date()); ?></span>
        </a>
      <?php endwhile; ?>
    </div>
    <?php the_posts_pagination(['mid_size' => 2, 'class' => 'pagination']); ?>
  <?php else : ?>
    <p>Aucun résultat. Essayez un terme plus général.</p>
  <?php endif; ?>
</div>

<?php get_footer();
