<?php
/**
 * « À lire dans le même univers ».
 *
 * Restreint à la catégorie principale de l'article : le maillage latéral
 * reste intra-silo. Des articles liés pris au hasard du site diluent le
 * signal thématique au lieu de le renforcer — et une requête interne évite
 * de dépendre d'une extension tierce pour une brique structurelle.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

$cat = infoweb_categorie_principale();
if (!$cat) {
    return;
}

$q = new WP_Query([
    'cat'                 => $cat->term_id,
    'post__not_in'        => [get_the_ID()],
    'posts_per_page'      => 3,
    'orderby'             => 'rand',
    'ignore_sticky_posts' => true,
    'no_found_rows'       => true,
]);

if (!$q->have_posts()) {
    wp_reset_postdata();
    return;
}
?>
<section class="silo">
  <h2>À lire dans le même univers</h2>
  <div class="grille">
    <?php while ($q->have_posts()) : $q->the_post(); ?>
      <a class="carte" href="<?php the_permalink(); ?>">
        <?php if (has_post_thumbnail()) : ?>
          <span class="vign"><?php the_post_thumbnail('infoweb-carte', ['loading' => 'lazy']); ?></span>
        <?php endif; ?>
        <span class="eyebrow"><?php echo esc_html($cat->name); ?></span>
        <h3><?php the_title(); ?></h3>
        <span class="meta"><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(infoweb_temps_lecture()); ?> min</span>
      </a>
    <?php endwhile; ?>
  </div>
</section>
<?php wp_reset_postdata();
