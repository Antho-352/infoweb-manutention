<?php
/**
 * Gabarit article.
 *
 * Le volume du site. Sa valeur ne se mesure pas à son trafic propre mais à
 * ce qu'il transmet aux pages qui rapportent : d'où le lien remontant
 * contextuel et les articles du même silo, tous deux imposés par le gabarit
 * et non laissés à la rédaction.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$cat = infoweb_categorie_principale();
$rubrique = infoweb_rubrique_du_post();
?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>

  <div class="art art-grille">
    <article <?php post_class(); ?>>
      <?php if ($cat) : ?>
        <a class="eyebrow" href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
          <?php echo esc_html($rubrique ? $rubrique['nom'] . ' · ' . $cat->name : $cat->name); ?>
        </a>
      <?php endif; ?>

      <h1><?php the_title(); ?></h1>

      <div class="sig">
        <span>Par <b><?php the_author(); ?></b></span>
        <span>Publié le <?php echo esc_html(get_the_date()); ?></span>
        <span><?php echo esc_html(infoweb_temps_lecture()); ?> min de lecture</span>
        <?php $verif = infoweb_date_verification(); ?>
        <?php if ($verif) : ?>
          <span class="verif">✓ Vérifié le <?php echo esc_html($verif); ?></span>
        <?php endif; ?>
      </div>

      <?php if (has_excerpt()) : ?>
        <p class="chapo"><?php echo esc_html(get_the_excerpt()); ?></p>
      <?php endif; ?>

      <div class="corps">
        <?php the_content(); ?>
      </div>

      <?php
      // Lien remontant contextuel : le fil d'Ariane ne suffit pas, il faut un
      // lien dans le flux de lecture, avec une ancre descriptive.
      if ($cat) : ?>
        <p class="remonte">
          Cet article fait partie de notre dossier sur
          <a href="<?php echo esc_url(get_category_link($cat->term_id)); ?>"><?php echo esc_html(strtolower($cat->name)); ?></a><?php
          if ($rubrique) : ?>, dans la rubrique
          <a href="<?php echo esc_url(home_url('/' . $rubrique['cle'] . '/')); ?>"><?php echo esc_html(strtolower($rubrique['nom'])); ?></a><?php
          endif; ?>.
        </p>
      <?php endif; ?>

      <?php get_template_part('template-parts/silo'); ?>
    </article>

    <aside class="aside">
      <?php get_template_part('template-parts/boite-auteur'); ?>

      <?php if ($cat) :
        $memes = new WP_Query([
            'cat'                 => $cat->term_id,
            'post__not_in'        => [get_the_ID()],
            'posts_per_page'      => 5,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]);
        if ($memes->have_posts()) : ?>
          <div class="boite">
            <h2>Dans <?php echo esc_html($cat->name); ?></h2>
            <?php while ($memes->have_posts()) : $memes->the_post(); ?>
              <a class="li" href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            <?php endwhile; ?>
          </div>
        <?php endif;
        wp_reset_postdata();
      endif; ?>
    </aside>
  </div>
</div>

<?php get_footer();
