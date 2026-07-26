<?php
/**
 * Template Name: Page à devis
 *
 * Le formulaire est l'objet de la page : il passe au-dessus du contenu.
 * Le contenu qui qualifie le besoin reste dessous — il sert le référencement
 * et rassure celui qui hésite, mais il ne s'interpose pas.
 *
 * Aucun lien d'affiliation sur ce gabarit : un seul appel à l'action par page.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$machine = get_post_meta(get_the_ID(), '_infoweb_machine', true);
?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>

  <div class="devis-grille">
    <div class="dg-tete">
      <h1><?php the_title(); ?></h1>
      <?php if (has_excerpt()) : ?>
        <p class="chapo"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>
    </div>

    <aside class="dg-form">
      <?php echo do_shortcode('[devis machine="' . esc_attr($machine) . '"]'); ?>
    </aside>

    <div class="dg-corps corps">
      <?php while (have_posts()) : the_post(); the_content(); endwhile; ?>
    </div>
  </div>
</div>

<?php get_footer();
