<?php
/**
 * Bloc « qui écrit ici » de la page d'accueil.
 *
 * C'est la première chose que regarde un annonceur, et ce qui pèse pour
 * Google sur du contenu sécurité et réglementation.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

// L'auteur mis en avant est le premier compte capable de publier.
$auteurs = get_users(['capability' => ['publish_posts'], 'number' => 1]);
if (!$auteurs) {
    return;
}
$a = $auteurs[0];
$bio = get_the_author_meta('description', $a->ID);
?>
<section class="redac">
  <?php echo get_avatar($a->ID, 128, '', '', ['loading' => 'lazy']); ?>
  <div>
    <h2>Qui écrit ici</h2>
    <p><strong><?php echo esc_html($a->display_name); ?></strong><?php
      if ($bio) { echo ' — ' . esc_html($bio); } ?></p>
    <p class="redac-note">Média indépendant : aucun constructeur ni loueur au capital.
      Chaque chiffre est daté et sourcé, chaque référence réglementaire renvoie au texte officiel.
      Les liens commerciaux sont signalés.</p>
  </div>
  <a class="btn" href="<?php echo esc_url(get_author_posts_url($a->ID)); ?>">La rédaction</a>
</section>
