<?php
/**
 * Données structurées.
 *
 * Un seul bloc JSON-LD par page, avec un @graph : plusieurs blocs séparés
 * obligent Google à recoller les entités lui-même et empêchent de les relier
 * par identifiant.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('wp_head', 'infoweb_schema', 5);
function infoweb_schema(): void {
    $accueil = home_url('/');
    $graph = [];

    // L'éditeur, référencé par les autres entités via son identifiant.
    $graph[] = [
        '@type' => 'Organization',
        '@id'   => $accueil . '#editeur',
        'name'  => get_bloginfo('name'),
        'url'   => $accueil,
    ];

    $fil = infoweb_fil_schema();
    if ($fil) {
        $graph[] = $fil;
    }

    if (is_singular('post')) {
        $auteur_id = (int) get_post_field('post_author', get_the_ID());
        $article = [
            '@type'            => 'Article',
            '@id'              => get_permalink() . '#article',
            'headline'         => get_the_title(),
            'datePublished'    => get_the_date('c'),
            'dateModified'     => get_the_modified_date('c'),
            'inLanguage'       => 'fr-FR',
            'mainEntityOfPage' => get_permalink(),
            'publisher'        => ['@id' => $accueil . '#editeur'],
            'author'           => [
                '@type' => 'Person',
                'name'  => get_the_author_meta('display_name', $auteur_id),
                'url'   => get_author_posts_url($auteur_id),
            ],
        ];
        $desc = infoweb_description();
        if ($desc) {
            $article['description'] = $desc;
        }
        if (has_post_thumbnail()) {
            $article['image'] = get_the_post_thumbnail_url(null, 'infoweb-une');
        }
        $cat = infoweb_categorie_principale();
        if ($cat) {
            $article['articleSection'] = $cat->name;
        }
        $graph[] = $article;

        $faq = infoweb_faq_schema(get_the_ID());
        if ($faq) {
            $graph[] = $faq;
        }
    }

    if (empty($graph)) {
        return;
    }

    printf(
        '<script type="application/ld+json">%s</script>' . "\n",
        wp_json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        )
    );
}

/**
 * FAQPage construite depuis le contenu réel de l'article.
 *
 * On ne demande pas à l'auteur de ressaisir ses questions dans un champ
 * dédié : le balisage est déduit des blocs de détail (<details><summary>)
 * présents dans le contenu. Une seule source, donc jamais de divergence
 * entre ce que lit le visiteur et ce que lit Google — divergence qui vaut
 * une pénalité manuelle.
 */
function infoweb_faq_schema(int $post_id): ?array {
    $contenu = get_post_field('post_content', $post_id);
    if (stripos($contenu, '<details') === false) {
        return null;
    }

    $rendu = apply_filters('the_content', $contenu);
    if (!preg_match_all(
        '#<details[^>]*>\s*<summary[^>]*>(.*?)</summary>(.*?)</details>#is',
        $rendu, $m, PREG_SET_ORDER
    )) {
        return null;
    }

    $questions = [];
    foreach ($m as $bloc) {
        $q = trim(wp_strip_all_tags($bloc[1]));
        $r = trim(wp_strip_all_tags($bloc[2]));
        if ($q === '' || $r === '') {
            continue;
        }
        $questions[] = [
            '@type'          => 'Question',
            'name'           => $q,
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $r],
        ];
    }

    return $questions ? ['@type' => 'FAQPage', 'mainEntity' => $questions] : null;
}
