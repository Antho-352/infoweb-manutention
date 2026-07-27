<?php
/**
 * Balises SEO : titre, méta description, canonique, Open Graph, robots.
 *
 * Remplace une extension SEO complète. Trois champs sont réglables par page
 * (voir inc/metabox-seo.php) ; tout le reste est déduit de ce que le thème
 * sait déjà de la page, pour qu'il n'y ait rien à saisir à la publication.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

/**
 * Titre de la balise <title>. Le champ manuel prime, sinon on compose.
 */
add_filter('pre_get_document_title', 'infoweb_title', 20);
function infoweb_title(string $titre): string {
    $nom_site = get_bloginfo('name');

    if (is_front_page()) {
        return 'Manutention et levage : guides, prix et prestataires — ' . $nom_site;
    }

    if (is_singular()) {
        $manuel = get_post_meta(get_the_ID(), '_infoweb_titre_seo', true);
        if ($manuel) {
            return $manuel;
        }
        return get_the_title() . ' — ' . $nom_site;
    }

    if (is_category()) {
        $terme = get_queried_object();
        $manuel = get_term_meta($terme->term_id, '_infoweb_titre_seo', true);
        if ($manuel) {
            return $manuel;
        }
        return single_cat_title('', false) . ' — ' . $nom_site;
    }

    if (is_search()) {
        return 'Recherche : ' . get_search_query() . ' — ' . $nom_site;
    }

    if (is_404()) {
        return 'Page introuvable — ' . $nom_site;
    }

    return $titre;
}

/**
 * Description : champ manuel, sinon extrait, sinon début du contenu.
 * Tronquée sur un mot entier, jamais au milieu.
 */
function infoweb_description(): string {
    if (is_front_page()) {
        return 'Média professionnel de la manutention et du levage : guides d\'achat, '
             . 'fourchettes de prix datées, réglementation CACES et VGP, et annuaire '
             . 'des loueurs et prestataires par département.';
    }

    $brut = '';
    if (is_singular()) {
        $brut = get_post_meta(get_the_ID(), '_infoweb_description_seo', true)
             ?: get_the_excerpt()
             ?: wp_strip_all_tags(get_post_field('post_content', get_the_ID()));
    } elseif (is_category()) {
        $terme = get_queried_object();
        $brut = get_term_meta($terme->term_id, '_infoweb_description_seo', true)
             ?: term_description($terme);
    }

    $brut = trim(preg_replace('/\s+/', ' ', wp_strip_all_tags($brut)));
    if ($brut === '') {
        return '';
    }
    if (mb_strlen($brut) <= 160) {
        return $brut;
    }
    $coupe = mb_substr($brut, 0, 160);
    $espace = mb_strrpos($coupe, ' ');
    return rtrim(mb_substr($coupe, 0, $espace ?: 160), " ,;:.") . '…';
}

/**
 * URL canonique. Sur une page paginée, la canonique pointe vers la page
 * courante et non vers la première : chaque page de liste a son propre
 * contenu et doit rester indexable indépendamment.
 */
function infoweb_canonique(): string {
    if (is_front_page()) {
        return home_url('/');
    }
    if (is_singular()) {
        return get_permalink();
    }
    if (is_category()) {
        $base = get_category_link(get_queried_object_id());
        $page = (int) get_query_var('paged');
        return $page > 1 ? trailingslashit($base) . "page/{$page}/" : $base;
    }
    return home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
}

/**
 * Directive robots. Une page marquée noindex par son auteur, une recherche
 * ou une 404 ne doivent pas entrer dans l'index.
 */
function infoweb_robots(): string {
    if (is_search() || is_404()) {
        return 'noindex, follow';
    }
    if (is_singular() && get_post_meta(get_the_ID(), '_infoweb_noindex', true)) {
        return 'noindex, follow';
    }
    return 'index, follow, max-image-preview:large, max-snippet:-1';
}

add_action('wp_head', 'infoweb_balises_head', 1);
function infoweb_balises_head(): void {
    $desc = infoweb_description();
    $canon = infoweb_canonique();
    $titre = wp_get_document_title();

    printf('<meta name="robots" content="%s">' . "\n", esc_attr(infoweb_robots()));
    if ($desc) {
        printf('<meta name="description" content="%s">' . "\n", esc_attr($desc));
    }
    printf('<link rel="canonical" href="%s">' . "\n", esc_url($canon));

    // Open Graph : le minimum utile au partage, sans Twitter Cards
    // redondantes — X lit les balises og: en absence des siennes.
    printf('<meta property="og:type" content="%s">' . "\n", is_singular() ? 'article' : 'website');
    printf('<meta property="og:title" content="%s">' . "\n", esc_attr($titre));
    if ($desc) {
        printf('<meta property="og:description" content="%s">' . "\n", esc_attr($desc));
    }
    printf('<meta property="og:url" content="%s">' . "\n", esc_url($canon));
    printf('<meta property="og:site_name" content="%s">' . "\n", esc_attr(get_bloginfo('name')));
    printf('<meta property="og:locale" content="fr_FR">' . "\n");

    if (is_singular() && has_post_thumbnail()) {
        $img = get_the_post_thumbnail_url(null, 'infoweb-une');
        printf('<meta property="og:image" content="%s">' . "\n", esc_url($img));
    }
}
