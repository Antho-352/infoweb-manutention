<?php
/**
 * Structure éditoriale de l'article : les briques que le gabarit impose et
 * que la prose seule ne porte pas.
 *
 *   - « L'essentiel » : résumé factuel en tête, pensé pour la lecture rapide
 *     et l'extraction par les moteurs et les LLM ;
 *   - le sommaire, déduit automatiquement des titres de niveau 2 ;
 *   - les « Sources », rendues depuis un champ structuré, jamais laissées à
 *     la rédaction dans le corps ;
 *   - l'appel à l'action, injecté par le gabarit et non par l'auteur.
 *
 * Les champs sont exposés à l'API REST : c'est ainsi qu'ils se remplissent à
 * la publication. Ils restent éditables à la main dans la métabox SEO.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

/**
 * Champs structurés exposés à REST. Chaînes simples : « L'essentiel » et les
 * sources sont des textes multi-lignes, une entrée par ligne — lisibles et
 * modifiables sans interface dédiée.
 */
add_action('init', function () {
    $commun = [
        'type'         => 'string',
        'single'       => true,
        'show_in_rest' => true,
        'auth_callback' => static fn () => current_user_can('edit_posts'),
    ];
    foreach ([
        '_infoweb_titre_seo',
        '_infoweb_description_seo',
        '_infoweb_verifie_le',
        '_infoweb_essentiel',
        '_infoweb_sources',
    ] as $cle) {
        register_post_meta('post', $cle, $commun);
    }
});

/**
 * Les points de « L'essentiel », une puce par ligne non vide.
 *
 * @return string[]
 */
function infoweb_essentiel(?int $post_id = null): array {
    $brut = (string) get_post_meta($post_id ?: get_the_ID(), '_infoweb_essentiel', true);
    return array_values(array_filter(array_map('trim', explode("\n", $brut)), 'strlen'));
}

/**
 * Les sources, une par ligne au format « TYPE | Titre | Éditeur · date | url ».
 * Les trois derniers champs sont facultatifs.
 *
 * @return array<int,array{type:string,titre:string,meta:string,url:string}>
 */
function infoweb_sources(?int $post_id = null): array {
    $brut = (string) get_post_meta($post_id ?: get_the_ID(), '_infoweb_sources', true);
    $lignes = array_filter(array_map('trim', explode("\n", $brut)), 'strlen');
    $sources = [];
    foreach ($lignes as $ligne) {
        $p = array_map('trim', explode('|', $ligne));
        $sources[] = [
            'type'  => $p[0] ?? '',
            'titre' => $p[1] ?? ($p[0] ?? ''),
            'meta'  => $p[2] ?? '',
            'url'   => (isset($p[3]) && filter_var($p[3], FILTER_VALIDATE_URL)) ? $p[3] : '',
        ];
    }
    return $sources;
}

/**
 * Ajoute une ancre à chaque titre de niveau 2 du contenu et renvoie le
 * sommaire correspondant. Le contenu est modifié en place (ids injectés) pour
 * que les liens du sommaire pointent réellement.
 *
 * @return array{contenu:string,toc:array<int,array{id:string,texte:string}>}
 */
function infoweb_sommaire(string $contenu): array {
    $toc = [];
    $n = 0;
    $contenu = preg_replace_callback(
        '#<h2\b([^>]*)>(.*?)</h2>#is',
        static function ($m) use (&$toc, &$n) {
            $texte = trim(wp_strip_all_tags($m[2]));
            if ($texte === '') {
                return $m[0];
            }
            // Ne pas écraser une ancre déjà posée par l'auteur.
            if (preg_match('/\bid=/', $m[1])) {
                if (preg_match('/id=["\']([^"\']+)["\']/', $m[1], $mid)) {
                    $toc[] = ['id' => $mid[1], 'texte' => $texte];
                }
                return $m[0];
            }
            $id = 'sec-' . (++$n) . '-' . sanitize_title($texte);
            $toc[] = ['id' => $id, 'texte' => $texte];
            return '<h2' . $m[1] . ' id="' . esc_attr($id) . '">' . $m[2] . '</h2>';
        },
        $contenu
    );
    return ['contenu' => $contenu, 'toc' => $toc];
}

/**
 * Bloc « L'essentiel ». Vide si aucun point n'est renseigné.
 */
function infoweb_bloc_essentiel(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();
    $points = infoweb_essentiel($post_id);
    if (!$points) {
        return '';
    }
    $sources = infoweb_sources($post_id);
    $verif = infoweb_date_verification($post_id);

    $html  = '<section class="ess" aria-label="L\'essentiel">';
    $html .= '<div class="ess-h"><div><span class="sur">L\'essentiel</span>'
           . '<h2>Ce qu\'il faut retenir</h2></div>'
           . '<span class="badge">Faits vérifiés</span></div>';
    $html .= '<ul>';
    foreach ($points as $p) {
        $html .= '<li>' . wp_kses_post($p) . '</li>';
    }
    $html .= '</ul>';

    $pied = [sprintf('%d fait%s vérifié%s', count($points), count($points) > 1 ? 's' : '', count($points) > 1 ? 's' : '')];
    if ($sources) {
        $pied[] = sprintf('%d source%s', count($sources), count($sources) > 1 ? 's' : '');
    }
    if ($verif) {
        $pied[] = 'Mis à jour le ' . $verif;
    }
    $html .= '<div class="ess-f"><span>' . implode('</span><span>', array_map('esc_html', $pied)) . '</span></div>';
    $html .= '</section>';
    return $html;
}

/**
 * Bloc « Sources ». Vide si aucune source n'est renseignée.
 */
function infoweb_bloc_sources(?int $post_id = null): string {
    $sources = infoweb_sources($post_id ?: get_the_ID());
    if (!$sources) {
        return '';
    }
    $html  = '<section class="sources" aria-label="Sources"><h2>Sources</h2>';
    $html .= '<p>Toute affirmation réglementaire de cet article renvoie à un texte officiel. '
           . 'Les données produit proviennent de la documentation constructeur, relevée à la date indiquée.</p><ol>';
    foreach ($sources as $s) {
        $titre = $s['url']
            ? '<a href="' . esc_url($s['url']) . '" rel="nofollow noopener" target="_blank">' . esc_html($s['titre']) . '</a>'
            : esc_html($s['titre']);
        $html .= '<li><div>';
        if ($s['type'] !== '') {
            $html .= '<span class="type">' . esc_html($s['type']) . '</span>';
        }
        $html .= $titre;
        if ($s['meta'] !== '') {
            $html .= '<small>' . esc_html($s['meta']) . '</small>';
        }
        $html .= '</div></li>';
    }
    $html .= '</ol></section>';
    return $html;
}

/**
 * Appel à l'action vers la page à devis, adapté à la catégorie de l'article.
 * Injecté par le gabarit : sa présence ne dépend pas de la rédaction.
 */
function infoweb_bloc_cta(?int $post_id = null): string {
    $post_id = $post_id ?: get_the_ID();
    $page = get_page_by_path('devis') ?: get_page_by_path('demande-de-devis');
    $lien = $page ? get_permalink($page) : home_url('/devis/');
    $cat = function_exists('infoweb_categorie_principale') ? infoweb_categorie_principale($post_id) : null;
    $quoi = $cat ? strtolower($cat->name) : 'matériel de manutention';

    return '<aside class="cta">'
        . '<div><span class="t">Un besoin de ' . esc_html($quoi) . ' pour votre site ?</span>'
        . '<p>Recevez trois devis de fournisseurs de votre département. Gratuit et sans engagement.</p></div>'
        . '<a href="' . esc_url($lien) . '">Demander un devis</a></aside>';
}
