#!/usr/bin/env python3
"""Construit le plan de redirections final :
- data/redirections-exactes.csv (url_source, action, url_cible, motif)
- wp/mu-plugins/arw-legacy-redirects.php (map exacte + routeur .html + fallbacks)
Règles : RECREATE par défaut (l'URL ranke → on la recrée telle quelle) ;
301 pour les consolidations de cannibalisation et les /produit/ absorbés par les familles ;
410 pour le hors-sujet.
"""
import csv, os, re

BASE = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(BASE, 'data', 'plan-urls-a-traiter.csv')
OUT_CSV = os.path.join(BASE, 'data', 'redirections-exactes.csv')
OUT_PHP = os.path.join(BASE, 'wp', 'mu-plugins', 'arw-legacy-redirects.php')
os.makedirs(os.path.dirname(OUT_PHP), exist_ok=True)

# --- Overrides explicites : consolidations de cannibalisation + hors-sujet ---
OVERRIDES = {
    # Cluster "marques chariot élévateur" : 3 pages sur la même intention → 1 canonique.
    # Canonique = /entreprise/le-classement.../ : elle ranke en page 1 sur "meilleur chariot
    # élévateur / comparatif marques" (SERP live 2026-07-25) et porte 78 mots-clés historiques
    # contre 33 pour l'autre. On préserve l'URL qui performe.
    '/chariot-elevateur/classement-des-marques-chariot-elevateur/':
        ('301', '/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/', 'cannibalisation marques'),
    '/fabricants-chariots-elevateurs/':
        ('301', '/entreprise/le-classement-marques-chariot-elevateur-avis-et-comparatifs/', 'cannibalisation marques'),
    # CACES : que-dois-je-savoir → guide complet (même intention)
    '/chariot-elevateur/caces-chariot-elevateur-que-dois-je-savoir/':
        ('301', '/caces-chariot-elevateur-guide-complet/', 'cannibalisation CACES'),
    '/chariot-elevateur/cest-quoi-le-caces-1/':
        ('301', '/caces-chariot-elevateur-guide-complet/', 'cannibalisation CACES'),
    # Fiche ITM dupliquée
    '/table-elevatrice/itm-logistique-alimentaire-international/':
        ('301', '/entreprise/itm-logistique-alimentaire-international/', 'doublon fiche ITM'),
    # Diables : 2 articles concurrents → 1
    '/manutention/chariots-manuels-diables/les-diables-pour-votre-entrepot/':
        ('301', '/manutention/chariot-diable/', 'cannibalisation diables'),
    # Prix chariot : slug défectueux → page prix canonique
    '/chariot-elevateur/quel-prix-pour-un-prix-chariot-elevateur-neuf-noatre-avis/':
        ('301', '/prix/chariot-elevateur/', 'slug défectueux, intention prix'),
    # Nacelles : 2 hubs hérités → 1 univers
    '/nacelles-elevatrices/': ('301', '/nacelle/', 'fusion univers nacelle'),
    '/acheter-ou-louer-une-nacelle-elevatrice/':
        ('RECREATE', '', 'satellite décision achat/location, maille vers /nacelle/ et /outils/tco/'),
    # Ponts roulants : articles éparpillés sous /table-elevatrice/ → recréés, mais les doublons vont à la famille
    '/table-elevatrice/levage-ponts-roulants-potences/pont-roulant-a-commande-manuelle/':
        ('301', '/levage/pont-roulant/', 'article catalogue absorbé par famille'),
    '/table-elevatrice/pont-roulant-a-commande-electrique-la-solution-efficace-et-precise-pour-les-operations-de-levage-regulieres/':
        ('301', '/levage/pont-roulant/', 'article catalogue absorbé par famille'),
    '/table-elevatrice/pont-roulant-a-double-poutre-la-solution-robuste-pour-les-charges-lourdes/':
        ('301', '/levage/pont-roulant/', 'article catalogue absorbé par famille'),
    '/treuil-palonnier/levolution-des-ponts-roulants-industriels-innovations-technologiques-et-performance/':
        ('301', '/levage/pont-roulant/', 'article catalogue absorbé par famille'),
    '/levage/levage-ponts-roulants-potences/': ('301', '/levage/pont-roulant/', 'hub hérité → famille'),
    '/levage-nacelles-tables-elevatrices/levage/elevateur-monte-charge/':
        ('301', '/levage/monte-charge/', 'hub hérité → famille'),
    '/levage-nacelles-tables-elevatrices/tables-elevatrices-moto/':
        ('301', '/levage/table-elevatrice/', 'page morte → famille'),
    '/levage-palonniers-palans-treuils-accessoire/': ('301', '/levage/palan/', 'hub hérité → famille'),
    '/treuil-palonnier/palan-a-chaine-guide-conseils-dachat/':
        ('RECREATE', '', 'guide palan à chaîne — ranke, conservé'),
    # Catégories e-commerce héritées → familles
    '/categorie/chariot-elevateur-gaz/': ('301', '/chariot-elevateur/gaz/', 'catégorie → famille'),
    '/categorie/chariot-elevateur-electrique/': ('301', '/chariot-elevateur/electrique/', 'catégorie → famille'),
    '/categorie/aimant-de-levage/': ('301', '/levage/aimant-de-levage/', 'catégorie → famille'),
    '/categorie/palan-treuils/': ('301', '/levage/palan/', 'catégorie → famille'),
    '/equipements-dentrepot/': ('301', '/stockage/', 'hub hérité → univers'),
    '/accueil/': ('301', '/', 'doublon home'),
    '/blog/': ('301', '/', 'hub vide'),
    # Transpalettes : prix → silo prix ; le reste recréé
    '/transpalette/prix-des-transpalettes-guide-des-tarifs-complet/':
        ('301', '/prix/transpalette/', 'intention prix'),
    '/transpalette/5-astuces-pour-trouver-un-transpalette-pas-cher/':
        ('301', '/prix/transpalette/', 'intention prix, cannibalisation'),
    # Hors-sujet assumé (dilution ère précédente)
    '/accessoire/les-remorques-pour-voitures-lesquelles-choisir/': ('410', '', 'hors sujet — remorques auto'),
    '/accessoire/crics/crics-et-chariots-elevateurs/': ('RECREATE', '', 'limite mais lié chariots — conservé'),
    '/outillage/parksidelidl-bricolage/': ('410', '', 'hors sujet — outillage grand public'),
    '/btp/le-prix-de-la-renovation-de-locaux-et-entrepots-industriels/': ('410', '', 'hors sujet — BTP réno'),
    '/btp/8-conseils-choisir-bacs-de-retention/': ('301', '/stockage/', 'connexe stockage'),
    '/project/fenwick/': ('301', '/gerbeur/le-gerbeur-fenwick-tout-savoir-sur-cet-appareil/', 'page projet morte → article Fenwick'),
    '/stockage/roll-conteneur/': ('RECREATE', '', 'ranke — famille roll-conteneur créée en phase 2'),
    '/nacelle/location-camion-nacelle-rentforce/': ('RECREATE', '', 'satellite location camion nacelle'),
}

# /produit/ : familles d'absorption par mot-clé du slug (ordre = priorité)
PRODUIT_ROUTES = [
    (r'aimant|magnetique', '/levage/aimant-de-levage/'),
    (r'palan', '/levage/palan/'),
    (r'treuil', '/levage/treuil/'),
    (r'retractable', '/chariot-elevateur/retractable/'),
    (r'diesel', '/chariot-elevateur/diesel/'),
    (r'chariot-elevateur-electrique', '/chariot-elevateur/electrique/'),
    (r'chariot-elevateur-a-gaz', '/chariot-elevateur/gaz/'),
    (r'chariot-de-transport|chariot-a-engrenage', '/manutention/chariot/'),
    (r'grue|griffe|poutrelle', '/levage/'),
]

def route_produit(url):
    for pat, dest in PRODUIT_ROUTES:
        if re.search(pat, url):
            return dest
    return '/levage/'

rows_out = []
with open(SRC) as f:
    for r in csv.DictReader(f):
        url = r['url']
        if url in OVERRIDES:
            action, cible, motif = OVERRIDES[url]
        elif url.startswith('/produit/'):
            action, cible, motif = '301', route_produit(url), 'fiche produit absorbée par famille'
        elif 'hors sujet' in r['action']:
            action, cible, motif = '410', '', 'hors sujet'
        else:
            action, cible, motif = 'RECREATE', '', 'ranke — recréée à l’identique'
        rows_out.append({'url_source': url, 'action': action, 'url_cible': cible,
                         'motif': motif, 'trafic_actuel': r['trafic_actuel'],
                         'volume_cumule': r['volume_cumule'], 'top_mot_cle': r['top_mot_cle']})

rows_out.sort(key=lambda x: (x['action'] != 'RECREATE', -int(x['trafic_actuel'] or 0), -int(x['volume_cumule'] or 0)))
with open(OUT_CSV, 'w', newline='') as f:
    w = csv.DictWriter(f, fieldnames=list(rows_out[0].keys()))
    w.writeheader(); w.writerows(rows_out)

# --- Génération du mu-plugin ---
map301 = {r['url_source']: r['url_cible'] for r in rows_out if r['action'] == '301'}
gone = [r['url_source'] for r in rows_out if r['action'] == '410']

php_map = ",\n".join(f"    '{k}' => '{v}'" for k, v in sorted(map301.items()))
php_gone = ",\n".join(f"    '{u}'" for u in sorted(gone))

php = """<?php
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
    if (preg_match('#^/[a-z0-9-]+\\.html/$#', $path)) {
        $routes = [
            'nacelle'            => '/nacelle/',
            'chariot-elevateur'  => '/chariot-elevateur/',
            'gerbeur'            => '/manutention/gerbeur/',
            'transpalette'       => '/manutention/transpalette/',
            'diable'             => '/manutention/diable/',
            'pont-roulant'       => '/levage/pont-roulant/',
            'ponts-roulants'     => '/levage/pont-roulant/',
            'palan'              => '/levage/palan/',
            'treuil'             => '/levage/treuil/',
            'aimant'             => '/levage/aimant-de-levage/',
            'potence'            => '/levage/potence/',
            'table-elevatrice'   => '/levage/table-elevatrice/',
            'rayonnage'          => '/stockage/rayonnage/',
            'stockage'           => '/stockage/',
            'rack'               => '/stockage/rayonnage/',
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
        || preg_match('#\\.(css|js|jpg|jpeg|png|gif|pdf)/$#', $path)) {
        status_header(410);
        nocache_headers();
        exit;
    }
}

const ARW_LEGACY_301 = [
__MAP__
];

const ARW_LEGACY_410 = [
__GONE__
];
"""
php = php.replace('__MAP__', php_map).replace('__GONE__', php_gone)
with open(OUT_PHP, 'w') as f:
    f.write(php)

n = {'RECREATE': 0, '301': 0, '410': 0}
for r in rows_out:
    n[r['action']] += 1
print(f"OK — {OUT_CSV}")
print(f"OK — {OUT_PHP}")
print(f"RECREATE={n['RECREATE']}  301={n['301']}  410={n['410']}")
