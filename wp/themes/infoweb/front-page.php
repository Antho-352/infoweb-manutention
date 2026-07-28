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
      <?php else : ?>
        <div class="une-fig" aria-hidden="true">
          <svg viewBox="0 0 120 120" fill="none" stroke="currentColor" stroke-width="2.4" width="150" height="150">
            <path d="M18 86h56M30 86V62h20v24M50 62l14-22h12v46" stroke-linejoin="round"/>
            <circle cx="36" cy="94" r="7"/><circle cx="64" cy="94" r="7"/>
            <path d="M84 34h20M84 44h20M84 54h14" stroke-linecap="round" opacity=".5"/>
          </svg>
        </div>
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
  // Les rubriques : les six univers du média, en colonnes — fidèle au modèle.
  // Un seul bloc « Les rubriques », chaque univers listant ses familles (les
  // sous-catégories), navigable même quand le site est jeune.
  ?>
  <section class="sect">
    <div class="sect-h"><h2>Les rubriques</h2><span>Six univers, une même exigence</span></div>
    <div class="rubs">
      <?php foreach (infoweb_rubriques() as $cle => $rubrique) :
        $rub_cat = get_category_by_slug($cle);
        $ids = [];
        foreach ($rubrique['familles'] as $slug) {
            $c = get_category_by_slug($slug);
            if ($c) { $ids[] = $c->term_id; }
        }
        $q = $ids ? new WP_Query([
            'category__in'        => $ids,
            'posts_per_page'      => 4,
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        ]) : null;
        ?>
        <div class="rub">
          <h3><?php if ($rub_cat) : ?><a href="<?php echo esc_url(get_category_link($rub_cat->term_id)); ?>"><?php echo esc_html($rubrique['nom']); ?></a><?php else : echo esc_html($rubrique['nom']); endif; ?></h3>
          <?php if ($q && $q->have_posts()) : ?>
            <?php while ($q->have_posts()) : $q->the_post(); ?>
              <a class="li" href="<?php the_permalink(); ?>"><?php the_title(); ?><small><?php echo esc_html(get_the_date()); ?> · <?php echo esc_html(infoweb_temps_lecture()); ?> min de lecture</small></a>
            <?php endwhile; wp_reset_postdata(); ?>
          <?php else : ?>
            <span class="rub-soon"><?php echo esc_html($rubrique['promesse']); ?></span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php // Par application : le second axe du cocon, secteur par secteur. ?>
  <section class="sect" id="par-application">
    <div class="sect-h">
      <h2>Par application</h2>
      <span>Les contraintes de manutention, secteur par secteur</span>
    </div>
    <div class="apps">
      <?php foreach (infoweb_secteurs() as $s) : ?>
        <a class="app" href="<?php echo esc_url(infoweb_secteur_lien($s['slug'])); ?>">
          <span class="app-n"><?php echo esc_html($s['nom']); ?></span>
          <u><?php echo esc_html($s['note']); ?></u>
        </a>
      <?php endforeach; ?>
    </div>
  </section>

  <?php
  // Comparatifs et guides d'achat : la brique d'affiliation. Masquée tant
  // qu'aucun comparatif n'est publié — pas de rubrique creuse.
  $cat_cmp = get_category_by_slug(apply_filters('infoweb_categorie_comparatifs', 'comparatif'));
  if ($cat_cmp && $cat_cmp->count > 0) :
      $q_cmp = new WP_Query([
          'cat'                 => $cat_cmp->term_id,
          'posts_per_page'      => 4,
          'ignore_sticky_posts' => true,
          'no_found_rows'       => true,
      ]);
      if ($q_cmp->have_posts()) : ?>
        <section class="sect">
          <div class="sect-h">
            <h2><a href="<?php echo esc_url(get_category_link($cat_cmp->term_id)); ?>">Comparatifs et guides d'achat</a></h2>
            <span>Testés sur critères, prix relevés et datés</span>
          </div>
          <div class="grille">
            <?php while ($q_cmp->have_posts()) : $q_cmp->the_post(); ?>
              <a class="carte" href="<?php the_permalink(); ?>">
                <?php if (has_post_thumbnail()) : ?>
                  <span class="vign"><?php the_post_thumbnail('infoweb-carte', ['loading' => 'lazy']); ?></span>
                <?php endif; ?>
                <span class="eyebrow">Comparatif</span>
                <h3><?php the_title(); ?></h3>
                <span class="meta"><?php echo esc_html(get_the_date()); ?></span>
              </a>
            <?php endwhile; ?>
          </div>
          <p class="aff-note">Certains liens de cette rubrique sont des liens commerciaux : si vous achetez, nous percevons une commission, sans surcoût pour vous. Le classement ne s'achète pas.</p>
        </section>
      <?php endif;
      wp_reset_postdata();
  endif; ?>

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

  <?php
  // Agenda des salons : masqué tant qu'il est vide — jamais de date inventée.
  $evenements = function_exists('infoweb_evenements') ? infoweb_evenements(6) : [];
  if ($evenements) : ?>
    <section class="sect">
      <?php $lien_agenda = get_post_type_archive_link('evenement') ?: home_url('/agenda/'); ?>
      <div class="sect-h"><h2><a href="<?php echo esc_url($lien_agenda); ?>">Agenda professionnel</a></h2><span>Salons manutention, intralogistique et industrie</span><a class="plus" href="<?php echo esc_url($lien_agenda); ?>">Tout l'agenda →</a></div>
      <div class="agenda">
        <?php foreach ($evenements as $ev) :
          $p = infoweb_evenement_pastille($ev['date']);
          $lien = !empty($ev['url']); ?>
          <<?php echo $lien ? 'a' : 'div'; ?> class="ev"<?php echo $lien ? ' href="' . esc_url($ev['url']) . '"' : ''; ?>>
            <span class="ev-d"><b><?php echo esc_html($p['jour']); ?></b><span><?php echo esc_html($p['mois']); ?></span></span>
            <span class="ev-t">
              <span class="ev-n"><?php echo esc_html($ev['nom']); ?></span>
              <?php if (!empty($ev['lieu'])) : ?><small><?php echo esc_html($ev['lieu']); ?></small><?php endif; ?>
            </span>
          </<?php echo $lien ? 'a' : 'div'; ?>>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>

  <?php // La lettre : cœur du modèle média, toujours présente. ?>
  <section class="sect" id="newsletter">
    <div class="nl">
      <div class="nl-t">
        <h2>La lettre de la manutention</h2>
        <p>Une fois par mois : les évolutions réglementaires qui vous concernent, les relevés de prix mis à jour et les comparatifs publiés. Rien d'autre.</p>
      </div>
      <div class="nl-f">
        <?php $msg = function_exists('infoweb_newsletter_message') ? infoweb_newsletter_message() : ''; ?>
        <?php if ($msg) : ?><p class="nl-msg"><?php echo esc_html($msg); ?></p><?php endif; ?>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
          <input type="hidden" name="action" value="infoweb_newsletter">
          <input type="hidden" name="page_source" value="<?php echo esc_url(home_url('/')); ?>">
          <?php wp_nonce_field('infoweb_newsletter', 'infoweb_news_nonce'); ?>
          <p class="nl-piege" aria-hidden="true"><label>Ne pas remplir<input type="text" name="site_internet" tabindex="-1" autocomplete="off"></label></p>
          <input type="email" name="email" required placeholder="Votre e-mail professionnel" aria-label="Votre e-mail professionnel">
          <button type="submit">S'abonner</button>
        </form>
        <label class="nl-rgpd">Vos données servent uniquement à l'envoi de cette lettre. Désinscription en un clic.</label>
      </div>
    </div>
  </section>

  <?php
  // Le fil : la profondeur du média, sous les briques de conversion.
  $fil = new WP_Query([
      'posts_per_page'      => 6,
      'post__not_in'        => $exclus,
      'ignore_sticky_posts' => true,
      'no_found_rows'       => true,
  ]);
  if ($fil->have_posts()) : ?>
    <section class="sect">
      <div class="sect-h"><h2>Le fil des publications</h2><span>Guides, analyses et retours d'exploitation</span></div>
      <div class="grille">
        <?php while ($fil->have_posts()) : $fil->the_post();
          $c = infoweb_categorie_principale(); ?>
          <a class="carte" href="<?php the_permalink(); ?>">
            <?php if ($c) : ?><span class="eyebrow"><?php echo esc_html($c->name); ?></span><?php endif; ?>
            <h3><?php the_title(); ?></h3>
            <?php if (has_excerpt()) : ?><p><?php echo esc_html(wp_trim_words(get_the_excerpt(), 18, '…')); ?></p><?php endif; ?>
            <span class="meta"><?php echo esc_html(get_the_date()); ?></span>
          </a>
        <?php endwhile; ?>
      </div>
    </section>
  <?php endif;
  wp_reset_postdata(); ?>

  <?php get_template_part('template-parts/redaction'); ?>
</div>

<?php get_footer();
