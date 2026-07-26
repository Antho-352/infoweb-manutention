<?php
/**
 * Page d'accueil.
 *
 * Deux fonctions tenues ensemble : ressembler à un média vivant, et
 * redistribuer l'autorité des liens entrants vers les pages qui rapportent.
 * Le bloc des rubriques fait les deux à la fois — il se lit comme un
 * sommaire et il envoie le jus vers les familles.
 *
 * La une est l'article épinglé (mécanisme natif de WordPress) : rien à
 * configurer, il suffit de cocher « Mettre cet article en avant ».
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$epingles = get_option('sticky_posts') ?: [];
$une = $epingles ? get_post((int) $epingles[0]) : null;
if (!$une) {
    $recents = get_posts(['posts_per_page' => 1, 'ignore_sticky_posts' => true]);
    $une = $recents ? $recents[0] : null;
}
$exclus = $une ? [$une->ID] : [];
?>

<div class="w">

  <?php if ($une) :
    $cat_une = infoweb_categorie_principale($une->ID); ?>
    <section class="une">
      <div class="une-txt">
        <?php if ($cat_une) : ?>
          <a class="eyebrow" href="<?php echo esc_url(get_category_link($cat_une->term_id)); ?>">
            <?php echo esc_html($cat_une->name); ?>
          </a>
        <?php endif; ?>
        <h1><a href="<?php echo esc_url(get_permalink($une)); ?>"><?php echo esc_html(get_the_title($une)); ?></a></h1>
        <?php if ($une->post_excerpt) : ?>
          <p class="une-chapo"><?php echo esc_html($une->post_excerpt); ?></p>
        <?php endif; ?>
        <p class="une-sig">Par <b><?php echo esc_html(get_the_author_meta('display_name', $une->post_author)); ?></b>
          · <?php echo esc_html(get_the_date('', $une)); ?>
          · <?php echo esc_html(infoweb_temps_lecture($une->ID)); ?> min
          <?php $v = infoweb_date_verification($une->ID); ?>
          <?php if ($v) : ?><span class="verif">✓ Vérifié le <?php echo esc_html($v); ?></span><?php endif; ?>
        </p>
      </div>
      <?php if (has_post_thumbnail($une)) : ?>
        <a class="une-img" href="<?php echo esc_url(get_permalink($une)); ?>" tabindex="-1" aria-hidden="true">
          <?php echo get_the_post_thumbnail($une, 'infoweb-une', ['fetchpriority' => 'high']); ?>
        </a>
      <?php endif; ?>
    </section>
  <?php endif; ?>

  <?php
  // À la une : les publications récentes, hors article de tête.
  $recents = new WP_Query([
      'posts_per_page'      => 4,
      'post__not_in'        => $exclus,
      'ignore_sticky_posts' => true,
      'no_found_rows'       => true,
  ]);
  if ($recents->have_posts()) : ?>
    <section class="sect">
      <div class="sect-h"><h2>À la une</h2><span>Vérifié, daté, sourcé</span></div>
      <div class="grille">
        <?php while ($recents->have_posts()) : $recents->the_post();
          $c = infoweb_categorie_principale();
          $exclus[] = get_the_ID(); ?>
          <a class="carte" href="<?php the_permalink(); ?>">
            <?php if (has_post_thumbnail()) : ?>
              <span class="vign"><?php the_post_thumbnail('infoweb-carte', ['loading' => 'lazy']); ?></span>
            <?php endif; ?>
            <?php if ($c) : ?><span class="eyebrow"><?php echo esc_html($c->name); ?></span><?php endif; ?>
            <h3><?php the_title(); ?></h3>
            <span class="meta"><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(infoweb_temps_lecture()); ?> min</span>
          </a>
        <?php endwhile; ?>
      </div>
    </section>
  <?php endif;
  wp_reset_postdata(); ?>

  <?php
  // Les rubriques : sommaire du média et moteur de redistribution.
  foreach (infoweb_rubriques() as $cle => $rubrique) :
      $ids = [];
      foreach ($rubrique['familles'] as $slug) {
          $c = get_category_by_slug($slug);
          if ($c && $c->count > 0) { $ids[] = $c->term_id; }
      }
      if (!$ids) { continue; }

      $q = new WP_Query([
          'category__in'        => $ids,
          'posts_per_page'      => 3,
          'post__not_in'        => $exclus,
          'ignore_sticky_posts' => true,
          'no_found_rows'       => true,
      ]);
      if (!$q->have_posts()) { wp_reset_postdata(); continue; } ?>

      <section class="sect">
        <div class="sect-h">
          <h2><a href="<?php echo esc_url(home_url('/' . $cle . '/')); ?>"><?php echo esc_html($rubrique['nom']); ?></a></h2>
          <span><?php echo esc_html($rubrique['promesse']); ?></span>
        </div>
        <div class="grille">
          <?php while ($q->have_posts()) : $q->the_post(); ?>
            <a class="carte" href="<?php the_permalink(); ?>">
              <h3><?php the_title(); ?></h3>
              <?php if (has_excerpt()) : ?>
                <p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 20, '…')); ?></p>
              <?php endif; ?>
              <span class="meta"><?php echo esc_html(get_the_date()); ?></span>
            </a>
          <?php endwhile; ?>
        </div>

        <?php // Les familles de la rubrique : ancres descriptives, pas « en savoir plus ». ?>
        <div class="familles">
          <?php foreach ($rubrique['familles'] as $slug) :
            $c = get_category_by_slug($slug);
            if (!$c) { continue; } ?>
            <a href="<?php echo esc_url(get_category_link($c->term_id)); ?>"><?php echo esc_html($c->name); ?></a>
          <?php endforeach; ?>
        </div>
      </section>
      <?php wp_reset_postdata();
  endforeach; ?>

  <?php
  // Combien ça coûte : la démonstration éditoriale, avec dates visibles.
  $reperes = function_exists('infoweb_prix_recents') ? infoweb_prix_recents(6) : [];
  if ($reperes) : ?>
    <section class="sect">
      <div class="sect-h"><h2>Combien ça coûte</h2><span>Fourchettes relevées et datées</span></div>
      <div class="grille-prix">
        <?php foreach ($reperes as $p) : ?>
          <div class="cprix">
            <span class="v"><?php echo esc_html(infoweb_prix_format((float) $p['montant_min'])
              . ' – ' . infoweb_prix_format((float) $p['montant_max'])
              . (INFOWEB_MODES[$p['mode']] ?? '')); ?></span>
            <span class="l"><?php echo esc_html($p['libelle']); ?></span>
            <span class="d">Constaté le <?php echo esc_html(date_i18n('j F Y', strtotime($p['constate_le']))); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php get_template_part('template-parts/redaction'); ?>
</div>

<?php get_footer();
