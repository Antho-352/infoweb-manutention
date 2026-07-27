<?php
/**
 * Page d'un événement (salon).
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

while (have_posts()) : the_post();
    $debut = get_post_meta(get_the_ID(), '_iw_ev_date', true);
    $fin   = get_post_meta(get_the_ID(), '_iw_ev_fin', true);
    $lieu  = get_post_meta(get_the_ID(), '_iw_ev_lieu', true);
    $url   = get_post_meta(get_the_ID(), '_iw_ev_url', true);
    $passe = $fin && $fin < current_time('Y-m-d');
    ?>

<div class="w">
  <div class="fil"><ol>
    <li><a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a></li>
    <li><a href="<?php echo esc_url(get_post_type_archive_link('evenement')); ?>">Agenda</a></li>
    <li><?php the_title(); ?></li>
  </ol></div>

  <div class="art">
    <article <?php post_class(); ?>>
      <span class="eyebrow">Agenda<?php echo $passe ? ' · Édition passée' : ''; ?></span>
      <h1><?php the_title(); ?></h1>

      <div class="ev-meta">
        <?php if ($debut) : ?>
          <span><b>📅 <?php echo esc_html(infoweb_evenement_dates($debut, $fin)); ?></b></span>
        <?php endif; ?>
        <?php if ($lieu) : ?><span>📍 <?php echo esc_html($lieu); ?></span><?php endif; ?>
      </div>

      <?php if (has_post_thumbnail()) : ?>
        <figure class="ev-img"><?php the_post_thumbnail('infoweb-une'); ?></figure>
      <?php endif; ?>

      <div class="corps"><?php the_content(); ?></div>

      <?php if ($url && !$passe) : ?>
        <p class="ev-cta"><a class="btn btn-plein" href="<?php echo esc_url($url); ?>" rel="nofollow noopener" target="_blank">Site officiel du salon ↗</a></p>
      <?php endif; ?>

      <p class="remonte">Retour à l'<a href="<?php echo esc_url(get_post_type_archive_link('evenement')); ?>">agenda complet</a>.</p>
    </article>
  </div>
</div>

<?php
endwhile;
get_footer();
