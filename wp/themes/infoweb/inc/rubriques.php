<?php
/**
 * Rubriques éditoriales — les six domaines du média.
 *
 * Depuis la 0.5.3, les rubriques sont AUSSI de vraies catégories WordPress
 * (elles apparaissent dans le menu et peuvent recevoir des articles en direct).
 * Mais les familles de matériel restent des catégories de premier niveau
 * distinctes, parce qu'elles portent les URLs héritées sous lesquelles les
 * articles sont déjà nichés (/manutention/chariot-diable/, /levage/…).
 *
 * Cette carte relie chaque domaine à ses familles : elle pilote les blocs
 * « Les rubriques » de l'accueil et le fil d'Ariane. La modifier n'a aucun
 * effet sur les URLs ni sur la base.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

function infoweb_rubriques(): array {
    return [
        'manutention-levage' => [
            'nom'      => 'Manutention & levage',
            'promesse' => 'Le matériel, famille par famille : ce qu\'il fait, ses limites, son coût.',
            'familles' => [
                'manutention-levage',
                'chariot-elevateur', 'gerbeur', 'transpalette', 'diable-chariot',
                'nacelle', 'treuil-palonnier', 'aimant-de-levage', 'pont-roulant',
                'table-elevatrice', 'monte-charge', 'levage', 'manutention',
            ],
        ],
        'logistique-entrepots' => [
            'nom'      => 'Logistique & entrepôts',
            'promesse' => 'Stockage, rayonnage, flux de préparation, organisation de l\'entrepôt.',
            'familles' => ['logistique-entrepots', 'stockage', 'rayonnage'],
        ],
        'automatisation-robotique' => [
            'nom'      => 'Automatisation & robotique',
            'promesse' => 'AGV, AMR, convoyeurs, transstockeurs : quand et comment automatiser.',
            'familles' => ['automatisation-robotique'],
        ],
        'energie' => [
            'nom'      => 'Énergie',
            'promesse' => 'Batteries, hydrogène, recharge, coût énergétique d\'un parc.',
            'familles' => ['energie'],
        ],
        'btp-construction' => [
            'nom'      => 'BTP & Construction',
            'promesse' => 'Levage et manutention de chantier, engins, contraintes du gros œuvre.',
            'familles' => ['btp-construction'],
        ],
        'chimie-pharma' => [
            'nom'      => 'Chimie & Pharma',
            'promesse' => 'ATEX, salle propre, traçabilité : manutention en milieu contraint.',
            'familles' => ['chimie-pharma'],
        ],
    ];
}

/**
 * Rubrique d'appartenance d'une catégorie, ou null si elle n'est rattachée
 * à aucune — auquel cas le fil d'Ariane s'arrête à l'accueil.
 */
function infoweb_rubrique_de(string $slug_categorie): ?array {
    foreach (infoweb_rubriques() as $cle => $rubrique) {
        if (in_array($slug_categorie, $rubrique['familles'], true)) {
            return $rubrique + ['cle' => $cle];
        }
    }
    return null;
}

/**
 * Rubrique de l'article courant, déduite de sa catégorie principale.
 */
function infoweb_rubrique_du_post(?int $post_id = null): ?array {
    $cats = get_the_category($post_id ?: get_the_ID());
    if (empty($cats)) {
        return null;
    }
    return infoweb_rubrique_de($cats[0]->slug);
}

/**
 * Catégorie principale d'un article : la première rattachée à une rubrique,
 * à défaut la première tout court. La catégorie qui porte l'URL (famille de
 * matériel) prime, car c'est elle qui situe l'article dans son silo.
 */
function infoweb_categorie_principale(?int $post_id = null): ?WP_Term {
    $cats = get_the_category($post_id ?: get_the_ID());
    if (empty($cats)) {
        return null;
    }
    // Priorité à une famille de matériel (pas au domaine lui-même), pour que
    // le fil d'Ariane pointe la catégorie la plus précise.
    $rubriques_slugs = array_keys(infoweb_rubriques());
    foreach ($cats as $cat) {
        if (infoweb_rubrique_de($cat->slug) && !in_array($cat->slug, $rubriques_slugs, true)) {
            return $cat;
        }
    }
    foreach ($cats as $cat) {
        if (infoweb_rubrique_de($cat->slug)) {
            return $cat;
        }
    }
    return $cats[0];
}
