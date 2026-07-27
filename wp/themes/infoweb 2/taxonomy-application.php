<?php
/**
 * Archive d'un secteur d'application — second axe du cocon.
 *
 * Même présentation que les archives de famille, mais l'entête situe la page
 * dans l'axe sectoriel et non dans une rubrique éditoriale.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$terme = get_queried_object();
$desc  = $terme ? term_description($terme) : '';
?>

<div class="w">
  <?php infoweb_afficher_fil(); ?>

  <div class="tete-arch">
    <span class="eyebrow">Par application</span>
    <h1><?php echo esc_html(single_term_title('', false)); ?></h1>
    <?php if ($desc) : ?>
      <div class="intro"><?php echo wp_kses_post($desc); ?></div>
    <?php else : ?>
      <div class="intro">Les contraintes de manutention et de levage propres au secteur
        <?php echo esc_html(strtolower(single_term_title('', false))); ?> : équipements adaptés,
        exigences réglementaires et repères de coûts.</div>
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
  <?php else : ?>
    <div class="vide">
      <strong>Secteur en cours de couverture</strong>
      Aucune publication n'est encore rattachée à ce secteur. Consultez les
      <a href="<?php echo esc_url(home_url('/')); ?>">dernières analyses</a> en attendant.
    </div>
  <?php endif; ?>
</div>

<?php get_footer();
