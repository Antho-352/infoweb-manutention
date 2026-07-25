<?php
/**
 * Plugin Name: ARW Legacy Redirects
 * Description: Redirections 301/410 des URLs héritées d'infoweb-manutention.fr (ère marketplace .html + consolidations éditoriales). Généré par scripts/build_redirects.py — ne pas éditer à la main.
 * Version: 1.0.0
 */

defined('ABSPATH') || exit;

add_action('template_redirect', 'arw_legacy_redirects', 1);

function arw_legacy_redirects(): void {
    if (!is_404()) {
        return; // ne jamais interférer avec une URL servie par WP
    }

    $path = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') . '/';
    if ($path !== '/') {
        $path = '/' . ltrim($path, '/');
    }

    $map = ARW_LEGACY_301;
    if (isset($map[$path])) {
        wp_redirect(home_url($map[$path]), 301);
        exit;
    }

    if (in_array($path, ARW_LEGACY_410, true)) {
        status_header(410);
        nocache_headers();
        exit;
    }

    // Ère marketplace 2018-2021 : ~2 600 URLs plates en {slug}-{id}.html → routage par mot-clé
    if (preg_match('#^/[a-z0-9-]+\.html/$#', $path)) {
        $routes = [
            'nacelle'            => '/nacelle/',
            'chariot-elevateur'  => '/chariot-elevateur/',
            'gerbeur'            => '/gerbeur/',
            'transpalette'       => '/transpalette/',
            'diable'             => '/diable-chariot/',
            'pont-roulant'       => '/pont-roulant/',
            'ponts-roulants'     => '/pont-roulant/',
            'palan'              => '/treuil-palonnier/',
            'treuil'             => '/treuil-palonnier/',
            'aimant'             => '/aimant-de-levage/',
            'potence'            => '/pont-roulant/',
            'table-elevatrice'   => '/table-elevatrice/',
            'rayonnage'          => '/rayonnage/',
            'stockage'           => '/stockage/',
            'rack'               => '/rayonnage/',
            'levage'             => '/levage/',
            'manutention'        => '/manutention/',
        ];
        foreach ($routes as $needle => $dest) {
            if (strpos($path, $needle) !== false) {
                wp_redirect(home_url($dest), 301);
                exit;
            }
        }
        wp_redirect(home_url('/'), 301);
        exit;
    }

    // Résidus techniques de l'ancien site : images, assets → 410 (inutile de rediriger)
    if (preg_match('#^/(image|images|uploads|satellites|stats)/#', $path)
        || preg_match('#\.(css|js|jpg|jpeg|png|gif|pdf)/$#', $path)) {
        status_header(410);
        nocache_headers();
        exit;
    }
}

const ARW_LEGACY_301 = [
    '/blog/' => '/',
    '/btp/8-conseils-choisir-bacs-de-retention/' => '/stockage/',
    '/categorie/aimant-de-levage/' => '/aimant-de-levage/',
    '/categorie/chariot-elevateur-electrique/' => '/chariot-elevateur/electrique/',
    '/categorie/chariot-elevateur-gaz/' => '/chariot-elevateur/gaz/',
    '/categorie/palan-treuils/' => '/treuil-palonnier/',
    '/chariot-elevateur/caces-chariot-elevateur-que-dois-je-savoir/' => '/caces-chariot-elevateur-guide-complet/',
    '/chariot-elevateur/cest-quoi-le-caces-1/' => '/caces-chariot-elevateur-guide-complet/',
    '/chariot-elevateur/classement-des-marques-chariot-elevateur/' => '/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/',
    '/chariot-elevateur/quel-prix-pour-un-prix-chariot-elevateur-neuf-noatre-avis/' => '/chariot-elevateur/prix/',
    '/fabricants-chariots-elevateurs/' => '/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/',
    '/levage-nacelles-tables-elevatrices/levage/elevateur-monte-charge/' => '/monte-charge/',
    '/levage-nacelles-tables-elevatrices/tables-elevatrices-moto/' => '/table-elevatrice/',
    '/levage-palonniers-palans-treuils-accessoire/' => '/treuil-palonnier/',
    '/levage/levage-ponts-roulants-potences/' => '/pont-roulant/',
    '/manutention/chariots-manuels-diables/les-diables-pour-votre-entrepot/' => '/diable-chariot/',
    '/nacelles-elevatrices/' => '/nacelle/',
    '/produit/1t-chariot-a-engrenage-avec-chaine-factories-curve-geared-trolley/' => '/transpalette/',
    '/produit/aimant-de-levage-400-kg/' => '/aimant-de-levage/',
    '/produit/aimant-de-levage-magnetique-1000-kg/' => '/aimant-de-levage/',
    '/produit/aimant-de-levage-magnetique-400kg/' => '/aimant-de-levage/',
    '/produit/aimant-de-levage-magnetique-capacite-de-levage-de-1500-kg/' => '/aimant-de-levage/',
    '/produit/chariot-de-transport-pliable-350-kg/' => '/transpalette/',
    '/produit/chariot-elevateur-a-gaz-15t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-a-gaz-25t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-a-gaz-2t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-a-gaz-35t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-a-gaz-5t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-a-gaz-7t/' => '/chariot-elevateur/gaz/',
    '/produit/chariot-elevateur-diesel-4x4-25t/' => '/chariot-elevateur/diesel/',
    '/produit/chariot-elevateur-diesel-4x4-2t/' => '/chariot-elevateur/diesel/',
    '/produit/chariot-elevateur-electrique-25t/' => '/chariot-elevateur/electrique/',
    '/produit/chariot-elevateur-electrique-2t/' => '/chariot-elevateur/electrique/',
    '/produit/chariot-elevateur-electrique-3t/' => '/chariot-elevateur/electrique/',
    '/produit/chariot-elevateur-electrique-5t/' => '/chariot-elevateur/electrique/',
    '/produit/elevateur-magnetique-a-aimant-en-acier-capacite-de-100-kg/' => '/aimant-de-levage/',
    '/produit/elevateur-magnetique-a-aimant-en-acier-capacite-poids-1000-kg/' => '/aimant-de-levage/',
    '/produit/elevateur-magnetique-a-aimant-en-acier-capacite-poids-1500-kg/' => '/aimant-de-levage/',
    '/produit/elevateur-magnetique-a-aimant-en-acier-capacite-poids-300-kg/' => '/aimant-de-levage/',
    '/produit/griffe-poutrelle-pince-a-poutre-5tonnes/' => '/levage/',
    '/produit/grue-de-levage-2t/' => '/levage/',
    '/produit/levage-magnetique-300-kg/' => '/aimant-de-levage/',
    '/produit/levage-magnetique-aimants-de-levage-100-kg/' => '/aimant-de-levage/',
    '/produit/levage-magnetique-aimants-de-levage-200-kg/' => '/aimant-de-levage/',
    '/produit/levage-magnetique-grue-600-kg/' => '/aimant-de-levage/',
    '/produit/mat-retractable-2-000-kg-a-2500-kg/' => '/chariot-elevateur/retractable/',
    '/produit/palan-a-chaine-05-tonne-de-6-m/' => '/treuil-palonnier/',
    '/produit/palan-a-chaine-3-tonnes/' => '/treuil-palonnier/',
    '/produit/palan-a-chaine-manuel-1-5t/' => '/treuil-palonnier/',
    '/produit/palan-chaine-3-m-2-crochets-de-2-tonnes/' => '/treuil-palonnier/',
    '/produit/palan-electrique-500-1000-kg-treuil-palan-a-chaine-a-levier-1800-w/' => '/treuil-palonnier/',
    '/produit/porte-palan-05-tonne/' => '/treuil-palonnier/',
    '/produit/treuil-a-cable-electrique-125-250kg-600w/' => '/treuil-palonnier/',
    '/produit/treuil-a-cable-electrique-150kg-300kg-telecommande-sans-fill/' => '/treuil-palonnier/',
    '/produit/treuil-a-cable-electrique-250-500-kg/' => '/treuil-palonnier/',
    '/produit/treuil-a-cable-electrique-400-800-kg-palan-electrique/' => '/treuil-palonnier/',
    '/produit/treuil-de-levage-chaine-3t/' => '/treuil-palonnier/',
    '/produit/treuil-electrique-pa1200-palan-220-v-telecommande-avec-un-cable-de-15-m/' => '/treuil-palonnier/',
    '/produit/treuil-electrique-palan-220-v-avec-un-cable-de-15-m/' => '/treuil-palonnier/',
    '/produit/treuil-electrique-telecommande/' => '/treuil-palonnier/',
    '/produit/treuil-manuel-3-2-t-treuil-de-main-corde-20-m/' => '/treuil-palonnier/',
    '/produit/treuil-palan-a-cable-electrique-900w-250-500kg/' => '/treuil-palonnier/',
    '/produit/treuil-palan-electrique-treuil100-200kg/' => '/treuil-palonnier/',
    '/project/fenwick/' => '/gerbeur/le-gerbeur-fenwick-tout-savoir-sur-cet-appareil/',
    '/table-elevatrice/itm-logistique-alimentaire-international/' => '/entreprise/itm-logistique-alimentaire-international/',
    '/table-elevatrice/levage-ponts-roulants-potences/pont-roulant-a-commande-manuelle/' => '/pont-roulant/',
    '/table-elevatrice/pont-roulant-a-commande-electrique-la-solution-efficace-et-precise-pour-les-operations-de-levage-regulieres/' => '/pont-roulant/',
    '/table-elevatrice/pont-roulant-a-double-poutre-la-solution-robuste-pour-les-charges-lourdes/' => '/pont-roulant/',
    '/transpalette/5-astuces-pour-trouver-un-transpalette-pas-cher/' => '/transpalette/prix/',
    '/transpalette/prix-des-transpalettes-guide-des-tarifs-complet/' => '/transpalette/prix/',
    '/treuil-palonnier/levolution-des-ponts-roulants-industriels-innovations-technologiques-et-performance/' => '/pont-roulant/'
];

const ARW_LEGACY_410 = [
    '/accessoire/les-remorques-pour-voitures-lesquelles-choisir/',
    '/accueil/vehicule-remorques/',
    '/btp/le-prix-de-la-renovation-de-locaux-et-entrepots-industriels/',
    '/outillage/parksidelidl-bricolage/'
];
