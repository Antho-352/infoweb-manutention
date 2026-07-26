<?php
/**
 * Encart auteur de la colonne latérale.
 *
 * Sur du contenu sécurité et réglementation, l'auteur identifié pèse pour
 * Google comme pour le lecteur professionnel — et il conditionne la vente
 * d'espace éditorial.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

$auteur_id = (int) get_post_field('post_author', get_the_ID());
$bio = get_the_author_meta('description', $auteur_id);
?>
<div class="boite">
  <h2>L'auteur</h2>
  <div class="auteur">
    <?php echo get_avatar($auteur_id, 88, '', '', ['class' => '', 'loading' => 'lazy']); ?>
    <div>
      <a class="n" href="<?php echo esc_url(get_author_posts_url($auteur_id)); ?>">
        <?php echo esc_html(get_the_author_meta('display_name', $auteur_id)); ?>
      </a>
      <?php if ($bio) : ?>
        <span class="r"><?php echo esc_html(wp_trim_words($bio, 14, '…')); ?></span>
      <?php endif; ?>
    </div>
  </div>
</div>
