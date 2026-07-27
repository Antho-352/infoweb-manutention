<?php
/**
 * Formulaire de recherche.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;
?>
<form role="search" method="get" class="recherche" action="<?php echo esc_url(home_url('/')); ?>">
  <label>
    <span class="vh">Rechercher sur le site</span>
    <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>"
           placeholder="Rechercher un matériel, une obligation, un prix…" required>
  </label>
  <button type="submit" class="btn btn-plein">Rechercher</button>
</form>
