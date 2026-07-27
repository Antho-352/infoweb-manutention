<?php
/**
 * Gabarit d'archive de catégorie — pages familles et piliers.
 *
 * La boucle est filtrée sur la catégorie courante, et rien d'autre. Deux
 * pages de ce gabarit ne doivent jamais afficher la même liste d'articles :
 * c'est la première chose à vérifier en recette.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$terme = get_queried_object();
$rubrique = infoweb_rubrique_de($terme->slug);
$desc = term_description($terme);
?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>

  <div class="tete-arch">
    <?php if ($rubrique) : ?>
      <a class="eyebrow" href="<?php echo esc_url(home_url('/' . $rubrique['cle'] . '/')); ?>">
        <?php echo esc_html($rubrique['nom']); ?>
      </a>
    <?php endif; ?>

    <h1><?php single_cat_title(); ?></h1>

    <?php if ($desc) : ?>
      <div class="intro"><?php echo wp_kses_post($desc); ?></div>
    <?php endif; ?>
  </div>

  <?php if (have_posts()) : ?>
    <div class="grille">
      <?php while (have_posts()) : the_post(); ?>
        <a class="carte" href="<?php the_permalink(); ?>">
          <?php if (has_post_thumbnail()) : ?>
            <span class="vign"><?php the_post_thumbnail('infoweb-carte', ['loading' => 'lazy']); ?></span>
          <?php endif; ?>
          <h3><?php the_title(); ?></h3>
          <?php if (has_excerpt()) : ?>
            <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 22, '…')); ?></p>
          <?php endif; ?>
          <span class="meta">
            <?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(infoweb_temps_lecture()); ?> min
          </span>
        </a>
      <?php endwhile; ?>
    </div>

    <?php
    the_posts_pagination([
        'mid_size'  => 2,
        'prev_text' => 'Précédent',
        'next_text' => 'Suivant',
        'class'     => 'pagination',
    ]);
    ?>

    <?php
    // Liens latéraux vers les familles sœurs de la même rubrique : le silo
    // se tient horizontalement autant que verticalement.
    if ($rubrique) :
        $soeurs = array_diff($rubrique['familles'], [$terme->slug]);
        if ($soeurs) : ?>
          <section class="silo">
            <h2>Autres familles de la rubrique <?php echo esc_html($rubrique['nom']); ?></h2>
            <div class="grille">
              <?php foreach ($soeurs as $slug) :
                $s = get_category_by_slug($slug);
                if (!$s) { continue; } ?>
                <a class="carte" href="<?php echo esc_url(get_category_link($s->term_id)); ?>">
                  <h3><?php echo esc_html($s->name); ?></h3>
                  <span class="meta"><?php echo esc_html($s->count); ?> publication<?php echo $s->count > 1 ? 's' : ''; ?></span>
                </a>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endif;
    endif; ?>

  <?php else : ?>
    <p>Aucune publication dans cette rubrique pour l'instant.</p>
  <?php endif; ?>
</div>

<?php get_footer();
