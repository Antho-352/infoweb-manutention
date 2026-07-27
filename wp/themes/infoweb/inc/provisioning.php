<?php
/**
 * Mise en place initiale.
 *
 * Le thème crée lui-même les pages système et les deux menus à son
 * activation. Sans cela, l'en-tête pointerait vers des pages inexistantes,
 * et il faudrait une notice d'installation que personne ne lit.
 *
 * L'opération est idempotente : une page déjà présente n'est jamais
 * recréée ni écrasée. Réactiver le thème ne détruit donc aucun contenu.
 *
 * @package infoweb
 */

defined('ABSPATH') || exit;

add_action('after_switch_theme', 'infoweb_mise_en_place');

/**
 * Déclenchement aussi sur montée de version : une mise à jour du thème par
 * téléversement ne passe pas par after_switch_theme, et les pages ajoutées
 * dans la nouvelle version ne seraient jamais créées.
 */
add_action('init', function () {
    if (get_option('infoweb_provision_version') !== INFOWEB_VERSION) {
        infoweb_mise_en_place();
        update_option('infoweb_provision_version', INFOWEB_VERSION);
    }
}, 20);

function infoweb_mise_en_place(): void {
    $pages = infoweb_pages_systeme();
    $ids = [];

    foreach ($pages as $slug => $p) {
        $existante = get_page_by_path($slug);
        if ($existante) {
            $ids[$slug] = $existante->ID;
            continue;
        }
        $id = wp_insert_post([
            'post_type'      => 'page',
            'post_name'      => $slug,
            'post_title'     => $p['titre'],
            'post_excerpt'   => $p['chapo'] ?? '',
            'post_content'   => $p['contenu'] ?? '',
            'post_status'    => 'publish',
            'comment_status' => 'closed',
            'ping_status'    => 'closed',
        ]);
        if (!is_wp_error($id)) {
            $ids[$slug] = $id;
            if (!empty($p['gabarit'])) {
                update_post_meta($id, '_wp_page_template', $p['gabarit']);
            }
            if (!empty($p['noindex'])) {
                update_post_meta($id, '_infoweb_noindex', '1');
            }
        }
    }

    infoweb_construire_menus($ids);

    // Les permaliens doivent inclure la catégorie pour que les URLs héritées
    // soient reproduites. On ne force rien si l'administrateur a déjà choisi
    // une structure : on se contente de signaler l'écart.
    if (get_option('permalink_structure') === '') {
        update_option('permalink_structure', '/%category%/%postname%/');
        flush_rewrite_rules(false);
    }
}

/**
 * Les pages dont le thème a besoin pour que sa navigation ne pointe pas
 * dans le vide. Contenu de départ réel, à compléter — jamais de lorem ipsum,
 * qui finit toujours par se retrouver en ligne.
 */
function infoweb_pages_systeme(): array {
    $site = get_bloginfo('name');
    return [
        'devis' => [
            'titre'   => 'Demander un devis — matériel de manutention',
            'gabarit' => 'template-devis.php',
            'chapo'   => 'Décrivez votre besoin en une minute. Nous transmettons votre demande à des fournisseurs de votre département, qui vous répondent directement. Gratuit et sans engagement.',
            'contenu' => "<!-- wp:heading --><h2>Ce qui fait varier un devis</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Quatre paramètres pèsent réellement sur une offre. Les connaître avant de comparer évite de mettre en regard des devis qui ne portent pas sur la même chose.</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li><strong>La machine et sa capacité</strong> — un besoin ponctuel et un parc permanent n'appellent pas la même réponse.</li><li><strong>Achat, location courte ou longue durée</strong> — le bon montage dépend de la durée d'usage et de l'intensité.</li><li><strong>L'énergie</strong> — l'électrique s'impose en intérieur, le thermique sort en extérieur, sous réserve des restrictions de circulation en centre-ville.</li><li><strong>La zone d'intervention</strong> — la proximité d'un service après-vente pèse souvent plus lourd que l'écart de prix à l'achat.</li></ul><!-- /wp:list -->\n<!-- wp:paragraph --><p>Notre rôle s'arrête à la mise en relation : nous ne vendons pas de matériel, nous n'avons aucun loueur au capital, et vos coordonnées ne sont jamais revendues à des tiers.</p><!-- /wp:paragraph -->",
        ],
        'annuaire' => [
            'titre'   => 'Annuaire des prestataires',
            'chapo'   => 'Loueurs, concessionnaires, réparateurs et organismes de contrôle de la manutention et du levage, département par département.',
            'contenu' => "<!-- wp:paragraph --><p>L'annuaire recense les professionnels de la manutention et du levage : location de matériel, vente et concession, réparation et maintenance, contrôle réglementaire et formation.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Chaque fiche indique les activités réelles de l'établissement, les machines et marques couvertes, sa zone d'intervention et ses coordonnées vérifiées.</p><!-- /wp:paragraph -->",
        ],
        'newsletter' => [
            'titre'   => 'La lettre de la manutention',
            'chapo'   => 'Une fois par mois : les évolutions réglementaires qui vous concernent, les relevés de prix mis à jour et les comparatifs publiés.',
            'contenu' => "<!-- wp:paragraph --><p>Une fois par mois, sans plus. Ce que contient chaque envoi :</p><!-- /wp:paragraph -->\n<!-- wp:list --><ul><li>les évolutions réglementaires qui touchent la conduite d'engins et les vérifications périodiques ;</li><li>les relevés de prix mis à jour, avec leur date de constat ;</li><li>les comparatifs et guides d'achat publiés dans le mois.</li></ul><!-- /wp:list -->\n<!-- wp:paragraph --><p>Désinscription en un clic. Vos coordonnées ne sont jamais revendues.</p><!-- /wp:paragraph -->",
        ],
        'contact' => [
            'titre'   => 'Contact',
            'chapo'   => 'Une question, une correction à signaler, une proposition de partenariat.',
            'contenu' => "<!-- wp:paragraph --><p>Pour toute question sur un contenu, une erreur à signaler ou une demande de partenariat commercial, écrivez à <a href=\"mailto:" . esc_attr(get_option('admin_email')) . "\">" . esc_html(get_option('admin_email')) . "</a>.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p><strong>Vous avez repéré une erreur ?</strong> Signalez-la en précisant la page concernée. Les corrections factuelles sont traitées en priorité et la date de vérification de la page est mise à jour.</p><!-- /wp:paragraph -->",
        ],
        'a-propos' => [
            'titre'   => 'À propos',
            'chapo'   => 'Qui édite ce site, selon quelle méthode, et comment il se finance.',
            'contenu' => "<!-- wp:heading --><h2>Notre méthode</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Chaque affirmation réglementaire renvoie à un texte officiel : Légifrance, recommandations de l'Assurance Maladie – Risques professionnels, publications de l'INRS. Les sources sont listées en fin d'article, avec leur date de consultation.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p>Les prix sont donnés en fourchettes hors taxes, datées, et révisées deux fois par an. Ils ne constituent jamais une offre commerciale.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Notre indépendance</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Aucun constructeur, loueur ou distributeur n'est au capital de ce site.</p><!-- /wp:paragraph -->",
        ],
        'transparence' => [
            'titre'   => 'Transparence',
            'chapo'   => 'Comment ce site se finance, et ce que cela change — ou non — pour vous.',
            'contenu' => "<!-- wp:heading --><h2>Liens commerciaux</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Certains liens vers des marchands sont des liens d'affiliation : si vous achetez après avoir cliqué, nous percevons une commission, sans surcoût pour vous. Ces liens sont signalés sur chaque page qui en contient.</p><!-- /wp:paragraph -->\n<!-- wp:paragraph --><p><strong>Le classement des comparatifs ne s'achète pas.</strong> Les critères de sélection sont publiés sur chaque comparatif, y compris ceux que nous avons écartés et pourquoi.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Mise en relation</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Les demandes de devis sont transmises à trois prestataires au maximum, choisis sur leur zone d'intervention. Vos coordonnées ne sont jamais revendues à des tiers.</p><!-- /wp:paragraph -->",
        ],
        'mentions-legales' => [
            'titre'   => 'Mentions légales',
            'contenu' => "<!-- wp:heading --><h2>Éditeur</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>{$site}<br>Directeur de la publication : Anthony Russo<br>SIRET : 98497752000019<br>Contact : " . esc_html(get_option('admin_email')) . "</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Hébergement</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>OVH SAS — 2 rue Kellermann, 59100 Roubaix, France.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Propriété intellectuelle</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>L'ensemble des contenus de ce site est protégé. Toute reproduction sans autorisation est interdite.</p><!-- /wp:paragraph -->",
        ],
        'confidentialite' => [
            'titre'   => 'Confidentialité',
            'contenu' => "<!-- wp:heading --><h2>Données collectées</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Les formulaires de demande de devis collectent : nom, e-mail professionnel, téléphone, entreprise, département et description du besoin. Ces données servent uniquement à transmettre votre demande aux prestataires concernés.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Durée de conservation</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>24 mois à compter de la demande, puis suppression automatique. Les demandes ayant abouti sont anonymisées et non supprimées, afin de conserver l'historique commercial sans conserver les données personnelles.</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Vos droits</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Accès, rectification, suppression et opposition sur simple demande à " . esc_html(get_option('admin_email')) . ".</p><!-- /wp:paragraph -->\n<!-- wp:heading --><h2>Adresses IP</h2><!-- /wp:heading -->\n<!-- wp:paragraph --><p>Les adresses IP ne sont jamais conservées en clair : seule une empreinte irréversible est enregistrée, pour limiter les envois automatisés.</p><!-- /wp:paragraph -->",
        ],
        'plan-du-site' => [
            'titre'   => 'Plan du site',
            'chapo'   => 'Toutes les rubriques et familles de ce site.',
            'contenu' => '<!-- wp:shortcode -->[plan_du_site]<!-- /wp:shortcode -->',
        ],
    ];
}

/**
 * Construit les deux menus si l'emplacement est libre. On ne touche jamais
 * à un menu déjà assigné : l'administrateur reste maître de sa navigation.
 */
function infoweb_construire_menus(array $ids): void {
    $emplacements = get_theme_mod('nav_menu_locations', []);

    if (empty($emplacements['principal'])) {
        $menu_id = wp_create_nav_menu('Navigation principale');
        if (!is_wp_error($menu_id)) {
            foreach (infoweb_rubriques() as $cle => $rubrique) {
                // On accroche la rubrique à sa première famille peuplée :
                // une entrée qui pointe vers une page vide dessert le site.
                $cible = null;
                foreach ($rubrique['familles'] as $slug) {
                    $c = get_category_by_slug($slug);
                    if ($c) { $cible = $c; break; }
                }
                if (!$cible) { continue; }
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title'     => $rubrique['nom'],
                    'menu-item-object'    => 'category',
                    'menu-item-object-id' => $cible->term_id,
                    'menu-item-type'      => 'taxonomy',
                    'menu-item-status'    => 'publish',
                    'menu-item-description' => $rubrique['promesse'],
                ]);
            }
            $emplacements['principal'] = $menu_id;
        }
    }

    if (empty($emplacements['pied'])) {
        $menu_id = wp_create_nav_menu('Pied de page');
        if (!is_wp_error($menu_id)) {
            foreach (['a-propos', 'contact', 'transparence', 'mentions-legales',
                      'confidentialite', 'plan-du-site'] as $slug) {
                if (empty($ids[$slug])) { continue; }
                wp_update_nav_menu_item($menu_id, 0, [
                    'menu-item-title'     => get_the_title($ids[$slug]),
                    'menu-item-object'    => 'page',
                    'menu-item-object-id' => $ids[$slug],
                    'menu-item-type'      => 'post_type',
                    'menu-item-status'    => 'publish',
                ]);
            }
            $emplacements['pied'] = $menu_id;
        }
    }

    set_theme_mod('nav_menu_locations', $emplacements);
}

/**
 * Plan du site lisible, distinct du sitemap XML natif de WordPress
 * (/wp-sitemap.xml), qui reste la référence pour les moteurs.
 */
add_shortcode('plan_du_site', function () {
    ob_start();
    echo '<div class="plan">';
    foreach (infoweb_rubriques() as $cle => $rubrique) {
        printf('<h2>%s</h2><p>%s</p><ul>', esc_html($rubrique['nom']), esc_html($rubrique['promesse']));
        foreach ($rubrique['familles'] as $slug) {
            $c = get_category_by_slug($slug);
            if (!$c) { continue; }
            printf(
                '<li><a href="%s">%s</a> <small>%d publication%s</small></li>',
                esc_url(get_category_link($c->term_id)),
                esc_html($c->name),
                (int) $c->count,
                $c->count > 1 ? 's' : ''
            );
        }
        echo '</ul>';
    }
    echo '</div>';
    return ob_get_clean();
});
