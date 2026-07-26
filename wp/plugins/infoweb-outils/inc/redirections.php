<?php
/**
 * Redirections des URLs héritées.
 *
 * Le domaine a eu trois vies : marketplace en .html de 2018 à 2021, déclin,
 * puis site de contenu WordPress. 144 URLs portaient encore des positions au
 * moment de la reprise. Chacune est traitée : recréée à l'identique,
 * redirigée, ou explicitement supprimée.
 *
 * Ce fichier est généré par scripts/build_redirects.py — ne pas l'éditer
 * à la main, la prochaine génération écraserait la modification.
 *
 * @package infoweb-outils
 */

defined('ABSPATH') || exit;

add_action('template_redirect', 'infoweb_redirections_heritees', 1);

function infoweb_redirections_heritees(): void {
    // On n'intervient jamais sur une URL que WordPress sait servir.
    if (!is_404()) {
        return;
    }

    $chemin = rtrim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/', '/') . '/';
    if ($chemin !== '/') {
        $chemin = '/' . ltrim($chemin, '/');
    }

    if (isset(INFOWEB_REDIRECTIONS[$chemin])) {
        wp_redirect(home_url(INFOWEB_REDIRECTIONS[$chemin]), 301);
        exit;
    }

    if (in_array($chemin, INFOWEB_SUPPRIMEES, true)) {
        infoweb_repondre_410();
    }

    // Ère marketplace : environ 2 600 URLs plates en {slug}-{id}.html.
    // Routage par mot-clé vers la famille correspondante, à défaut l'accueil.
    if (preg_match('#^/[a-z0-9-]+\.html/$#', $chemin)) {
        foreach (INFOWEB_ROUTAGE_HTML as $mot => $cible) {
            if (str_contains($chemin, $mot)) {
                wp_redirect(home_url($cible), 301);
                exit;
            }
        }
        wp_redirect(home_url('/'), 301);
        exit;
    }

    // Résidus techniques de l'ancien site : rediriger n'aurait aucun sens.
    if (preg_match('#^/(image|images|uploads|satellites|stats)/#', $chemin)
        || preg_match('#\.(css|js|jpg|jpeg|png|gif|pdf)/$#', $chemin)) {
        infoweb_repondre_410();
    }
}

/**
 * 410 plutôt que 404 : la ressource a existé et ne reviendra pas. Google
 * la retire de son index plus vite, et cesse de la recrawler.
 */
function infoweb_repondre_410(): void {
    status_header(410);
    nocache_headers();
    wp_die(
        '<h1>Cette page a été supprimée</h1><p>Elle ne sera pas remplacée. '
        . '<a href="' . esc_url(home_url('/')) . '">Retour à l\'accueil</a></p>',
        'Page supprimée',
        ['response' => 410]
    );
}

const INFOWEB_ROUTAGE_HTML = [
    'nacelle'           => '/nacelle/',
    'chariot-elevateur' => '/chariot-elevateur/',
    'gerbeur'           => '/gerbeur/',
    'transpalette'      => '/transpalette/',
    'diable'            => '/diable-chariot/',
    'pont-roulant'      => '/pont-roulant/',
    'ponts-roulants'    => '/pont-roulant/',
    'palan'             => '/treuil-palonnier/',
    'treuil'            => '/treuil-palonnier/',
    'aimant'            => '/aimant-de-levage/',
    'potence'           => '/pont-roulant/',
    'table-elevatrice'  => '/table-elevatrice/',
    'rayonnage'         => '/rayonnage/',
    'rack'              => '/rayonnage/',
    'stockage'          => '/stockage/',
    'levage'            => '/levage/',
    'manutention'       => '/manutention/',
];

const INFOWEB_REDIRECTIONS = [
    '/accessoire/crics/crics-et-chariots-elevateurs/' => '/chariot-elevateur/',
    '/blog/' => '/',
    '/btp/8-conseils-choisir-bacs-de-retention/' => '/stockage/',
    '/categorie/aimant-de-levage/' => '/aimant-de-levage/',
    '/categorie/chariot-elevateur-diesel/' => '/chariot-elevateur/diesel/',
    '/categorie/chariot-elevateur-electrique/' => '/chariot-elevateur/electrique/',
    '/categorie/chariot-elevateur-gaz/' => '/chariot-elevateur/gaz/',
    '/categorie/gerbeur/' => '/gerbeur/',
    '/categorie/palan-treuils/' => '/treuil-palonnier/',
    '/chariot-elevateur/caces-chariot-elevateur-que-dois-je-savoir/' => '/caces-chariot-elevateur-guide-complet/',
    '/chariot-elevateur/cest-quoi-le-caces-1/' => '/caces-chariot-elevateur-guide-complet/',
    '/chariot-elevateur/classement-des-marques-chariot-elevateur/' => '/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/',
    '/chariot-elevateur/quel-prix-pour-un-prix-chariot-elevateur-neuf-noatre-avis/' => '/chariot-elevateur/prix/',
    '/equipements-dentrepot/entrepot-abris-elements-dentrepot/' => '/stockage/',
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

const INFOWEB_SUPPRIMEES = [
    '/accessoire/les-remorques-pour-voitures-lesquelles-choisir/',
    '/accueil/vehicule-remorques/',
    '/btp/le-prix-de-la-renovation-de-locaux-et-entrepots-industriels/',
    '/outillage/parksidelidl-bricolage/'
];

/**
 * Écran d'état, sous Outils.
 */
function infoweb_ecran_redirections(): void {
    $structure = get_option('permalink_structure');
    ?>
    <h2>État</h2>
    <table class="widefat" style="max-width:640px">
      <tbody>
        <tr><td>Redirections 301 actives</td><td><strong><?php echo count(INFOWEB_REDIRECTIONS); ?></strong></td></tr>
        <tr><td>URLs répondant 410</td><td><strong><?php echo count(INFOWEB_SUPPRIMEES); ?></strong></td></tr>
        <tr><td>Motifs de routage .html</td><td><strong><?php echo count(INFOWEB_ROUTAGE_HTML); ?></strong> (couvrent ~2 600 anciennes URLs)</td></tr>
        <tr><td>Structure de permaliens</td><td><code><?php echo esc_html($structure ?: 'Simple'); ?></code>
          <?php echo str_contains($structure, '%category%')
            ? '<span style="color:#2f7a52">— correcte</span>'
            : '<strong style="color:#b1382f">— doit être /%category%/%postname%/</strong>'; ?></td></tr>
      </tbody>
    </table>

    <h2>Tester une URL</h2>
    <form method="get">
      <input type="hidden" name="page" value="infoweb-outils">
      <input type="hidden" name="onglet" value="redirections">
      <input type="text" name="test" class="regular-text" placeholder="/gerbeur/ancienne-page/"
             value="<?php echo esc_attr(wp_unslash($_GET['test'] ?? '')); ?>">
      <button class="button">Tester</button>
    </form>
    <?php
    $test = trim(sanitize_text_field(wp_unslash($_GET['test'] ?? '')));
    if ($test !== '') {
        $c = '/' . trim($test, '/') . '/';
        if (isset(INFOWEB_REDIRECTIONS[$c])) {
            printf('<p><strong>301</strong> vers <code>%s</code></p>', esc_html(INFOWEB_REDIRECTIONS[$c]));
        } elseif (in_array($c, INFOWEB_SUPPRIMEES, true)) {
            echo '<p><strong>410</strong> — supprimée volontairement.</p>';
        } else {
            echo '<p>Aucune règle. Cette URL doit être recréée, ou elle renverra une 404.</p>';
        }
    }
}
