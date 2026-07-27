<?php
/**
 * Fil d'Ariane et BreadcrumbList.
 *
 * Le fil est la vérité hiérarchique de la page : Accueil › Rubrique › Famille
 * › Article. Il est construit depuis la carte des rubriques et la catégorie
 * principale, jamais depuis la hiérarchie des catégories en base — c'est
 * précisément une hiérarchie de catégories corrompue qui produisait des fils
 * d'Ariane faux sur un site précédent.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

/**
 * Segments du fil, du plus général au plus précis.
 * Le dernier segment n'a pas d'URL : c'est la page courante.
 */
function infoweb_fil(): array {
    $fil = [['nom' => 'Accueil', 'url' => home_url('/')]];

    if (is_singular('post')) {
        $cat = infoweb_categorie_principale();
        if ($cat) {
            $rubrique = infoweb_rubrique_de($cat->slug);
            if ($rubrique) {
                $fil[] = ['nom' => $rubrique['nom'], 'url' => home_url('/' . $rubrique['cle'] . '/')];
            }
            $fil[] = ['nom' => $cat->name, 'url' => get_category_link($cat->term_id)];
        }
        $fil[] = ['nom' => get_the_title(), 'url' => null];
        return $fil;
    }

    if (is_page()) {
        foreach (array_reverse(get_post_ancestors(get_the_ID())) as $id) {
            $fil[] = ['nom' => get_the_title($id), 'url' => get_permalink($id)];
        }
        $fil[] = ['nom' => get_the_title(), 'url' => null];
        return $fil;
    }

    if (is_category()) {
        $terme = get_queried_object();
        $rubrique = infoweb_rubrique_de($terme->slug);
        if ($rubrique) {
            $fil[] = ['nom' => $rubrique['nom'], 'url' => home_url('/' . $rubrique['cle'] . '/')];
        }
        $fil[] = ['nom' => $terme->name, 'url' => null];
        return $fil;
    }

    if (is_search()) {
        $fil[] = ['nom' => 'Recherche : ' . get_search_query(), 'url' => null];
    } elseif (is_404()) {
        $fil[] = ['nom' => 'Page introuvable', 'url' => null];
    }
    return $fil;
}

/**
 * Rendu visible du fil. Rien sur l'accueil, où il n'apporte pas d'information.
 */
function infoweb_afficher_fil(): void {
    if (is_front_page()) {
        return;
    }
    $fil = infoweb_fil();
    if (count($fil) < 2) {
        return;
    }

    echo '<nav class="fil" aria-label="Fil d\'Ariane"><ol>';
    $dernier = count($fil) - 1;
    foreach ($fil as $i => $s) {
        echo '<li>';
        if ($s['url'] && $i !== $dernier) {
            printf('<a href="%s">%s</a>', esc_url($s['url']), esc_html($s['nom']));
        } else {
            printf('<span aria-current="page">%s</span>', esc_html($s['nom']));
        }
        echo '</li>';
    }
    echo '</ol></nav>';
}

/**
 * BreadcrumbList, émis avec le reste des données structurées.
 * JSON_HEX_TAG et JSON_UNESCAPED_UNICODE : on neutralise les chevrons sans
 * échapper les accents, qui resteraient lisibles mais illisibles au débogage.
 */
function infoweb_fil_schema(): ?array {
    $fil = infoweb_fil();
    if (count($fil) < 2) {
        return null;
    }
    $items = [];
    foreach ($fil as $i => $s) {
        $item = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $s['nom'],
        ];
        if ($s['url']) {
            $item['item'] = $s['url'];
        }
        $items[] = $item;
    }
    return [
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ];
}
