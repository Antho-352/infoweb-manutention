<?php
/**
 * Page introuvable.
 *
 * Elle propose une sortie utile plutôt qu'un cul-de-sac : les rubriques et
 * la recherche. Une part du trafic arrive ici par d'anciennes URLs qui n'ont
 * pas de redirection — autant les récupérer.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

get_header(); ?>

<div class="w">
  <div class="art">
    <h1>Cette page n'existe pas</h1>
    <p class="chapo">Elle a peut-être été déplacée, ou l'adresse comporte une erreur.</p>

    <div class="corps">
      <?php get_search_form(); ?>
      <h2>Les rubriques</h2>
      <ul>
        <?php foreach (infoweb_rubriques() as $cle => $r) : ?>
          <li><a href="<?php echo esc_url(home_url('/' . $cle . '/')); ?>"><?php echo esc_html($r['nom']); ?></a>
            — <?php echo esc_html($r['promesse']); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</div>

<?php get_footer();
