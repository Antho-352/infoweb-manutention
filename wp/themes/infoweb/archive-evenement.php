<?php
/**
 * Archive « Agenda » — tous les salons, à venir puis passés.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header();

$aujourdhui = current_time('Y-m-d');
$a_venir = infoweb_evenements(50);

$passes = get_posts([
    'post_type'      => 'evenement',
    'posts_per_page' => 20,
    'meta_key'       => '_iw_ev_date',
    'orderby'        => 'meta_value',
    'order'          => 'DESC',
    'meta_query'     => [['key' => '_iw_ev_fin', 'value' => $aujourdhui, 'compare' => '<', 'type' => 'DATE']],
]);
?>

<div class="w">
  <div class="fil"><ol>
    <li><a href="<?php echo esc_url(home_url('/')); ?>">Accueil</a></li>
    <li>Agenda</li>
  </ol></div>

  <div class="tete-arch">
    <span class="eyebrow">Agenda professionnel</span>
    <h1>Salons manutention, intralogistique et industrie</h1>
    <div class="intro">Les rendez-vous à ne pas manquer : dates, lieux et lien officiel, vérifiés.</div>
  </div>

  <?php if ($a_venir) : ?>
    <section class="sect">
      <div class="sect-h"><h2>À venir</h2></div>
      <div class="agenda">
        <?php foreach ($a_venir as $ev) :
          $p = infoweb_evenement_pastille($ev['date']); ?>
          <a class="ev" href="<?php echo esc_url($ev['url']); ?>">
            <span class="ev-d"><b><?php echo esc_html($p['jour']); ?></b><span><?php echo esc_html($p['mois']); ?></span></span>
            <span class="ev-t">
              <span class="ev-n"><?php echo esc_html($ev['nom']); ?></span>
              <?php if ($ev['lieu']) : ?><small><?php echo esc_html(infoweb_evenement_dates($ev['date'], $ev['fin']) . ' · ' . $ev['lieu']); ?></small><?php endif; ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php else : ?>
    <div class="vide"><strong>Aucun salon à venir pour l'instant</strong>Revenez bientôt : l'agenda est mis à jour à chaque nouvelle date confirmée.</div>
  <?php endif; ?>

  <?php if ($passes) : ?>
    <section class="sect">
      <div class="sect-h"><h2>Éditions passées</h2></div>
      <div class="agenda">
        <?php foreach ($passes as $p) :
          $debut = get_post_meta($p->ID, '_iw_ev_date', true);
          $lieu  = get_post_meta($p->ID, '_iw_ev_lieu', true);
          $past  = infoweb_evenement_pastille($debut); ?>
          <a class="ev ev--passe" href="<?php echo esc_url(get_permalink($p)); ?>">
            <span class="ev-d"><b><?php echo esc_html($past['jour']); ?></b><span><?php echo esc_html($past['mois']); ?></span></span>
            <span class="ev-t">
              <span class="ev-n"><?php echo esc_html(get_the_title($p)); ?></span>
              <?php if ($lieu) : ?><small><?php echo esc_html($lieu); ?></small><?php endif; ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</div>

<?php get_footer();
