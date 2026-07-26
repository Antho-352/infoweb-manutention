<?php
/**
 * Rubriques éditoriales.
 *
 * Les familles de matériel sont des catégories de premier niveau, parce
 * qu'elles portent les URLs héritées sous lesquelles les articles sont déjà
 * nestés (voir docs/strategie-media-industrie.md §3.2). Les rubriques, elles,
 * ne sont pas une taxonomie : ce serait une seconde hiérarchie à maintenir en
 * base pour un regroupement purement éditorial, et cela ferait apparaître le
 * chemin complet des ancêtres dans les URLs d'articles.
 *
 * Elles vivent donc ici, dans une carte. Ajouter une rubrique ou déplacer une
 * famille est une modification de ce tableau, sans effet sur les URLs ni sur
 * la base.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

function infoweb_rubriques(): array {
    return [
        'equipements' => [
            'nom'      => 'Équipements',
            'promesse' => 'Le matériel, famille par famille : ce qu\'il fait, ses limites, son prix.',
            'familles' => [
                'chariot-elevateur', 'gerbeur', 'transpalette', 'diable-chariot',
                'nacelle', 'treuil-palonnier', 'aimant-de-levage', 'pont-roulant',
                'table-elevatrice', 'monte-charge', 'rayonnage',
            ],
        ],
        'reglementation' => [
            'nom'      => 'Réglementation',
            'promesse' => 'CACES, autorisation de conduite, VGP. Sourcé, daté, vérifié.',
            'familles' => ['reglementation', 'securite'],
        ],
        'couts' => [
            'nom'      => 'Coûts',
            'promesse' => 'Budgets, coût de possession, achat contre location, financement.',
            'familles' => ['couts'],
        ],
        'exploitation' => [
            'nom'      => 'Exploitation',
            'promesse' => 'Méthodes d\'entrepôt, flux, maintenance, gestion de parc.',
            'familles' => ['exploitation', 'stockage'],
        ],
        'marche' => [
            'nom'      => 'Marché',
            'promesse' => 'Constructeurs, distributeurs, réseaux de service.',
            'familles' => ['entreprise'],
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
 * à défaut la première tout court. Une catégorie unique par article est la
 * règle éditoriale (un article, un silo), cette fonction n'est qu'un garde-fou.
 */
function infoweb_categorie_principale(?int $post_id = null): ?WP_Term {
    $cats = get_the_category($post_id ?: get_the_ID());
    if (empty($cats)) {
        return null;
    }
    foreach ($cats as $cat) {
        if (infoweb_rubrique_de($cat->slug)) {
            return $cat;
        }
    }
    return $cats[0];
}
