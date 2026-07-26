# Brief de collecte — données d'établissements pour l'annuaire

Document de reprise, à confier à l'outil ou au prestataire qui réalisera la collecte. Il décrit **ce qu'il faut obtenir**, pas par quel moyen : la spécification vaut pour l'API Places de Google, pour un fournisseur de données sous licence, ou pour une saisie manuelle.

---

## 1. Le projet

**infoweb-manutention.fr** est un média professionnel français consacré à la manutention et au levage : chariots élévateurs, gerbeurs, transpalettes, nacelles élévatrices, ponts roulants, palans, rayonnages industriels.

Il comporte un **annuaire de prestataires** destiné aux responsables logistique, chefs d'entrepôt et acheteurs industriels qui cherchent à louer du matériel, le faire réparer, le faire contrôler ou former leurs opérateurs.

Chaque établissement donne lieu à une fiche publique, et les établissements sont regroupés sur des pages de croisement « type de prestation × zone géographique » — par exemple « Location de chariot élévateur en Moselle ».

L'annuaire est monétisé par la mise en relation : un formulaire de demande de devis sur chaque fiche, et une option de fiche premium. **Une fiche sans téléphone ni horaires ne sert à rien** : c'est ce qui rend cette collecte indispensable au projet.

---

## 2. La liste cible

Fichier : `data/annuaire/export/etablissements-a-enrichir.csv`
Séparateur point-virgule, encodage UTF-8.

Il contient les établissements déjà identifiés depuis la base publique des entreprises françaises (API Recherche d'entreprises, data.gouv.fr), filtrés sur les codes d'activité de la manutention et du levage.

| Colonne | Contenu |
|---|---|
| `siret` | Identifiant unique de l'établissement — **clé de rapprochement, à conserver intacte** |
| `raison_sociale` | Dénomination légale |
| `adresse` | Adresse de l'établissement, pas du siège |
| `code_postal`, `commune`, `departement` | Localisation |
| `naf` | Code d'activité principale |
| `activites_presumees` | Déduction provisoire : loueur, concessionnaire, sav, controle_vgp, formation |
| `lat`, `lon` | Coordonnées issues de la base officielle |
| `telephone_connu`, `site_connu`, `horaires_connus` | Déjà obtenus via OpenStreetMap, à ne pas réécraser s'ils sont remplis |
| `a_completer` | `oui` si le téléphone manque encore |

La liste s'étend au fur et à mesure de la collecte des départements. Elle est régénérable par `scripts/collecte_annuaire.py`.

---

## 3. Les données à obtenir

Pour chaque établissement de la liste, sa fiche d'établissement Google (Google Business Profile) :

| Champ attendu | Description | Priorité |
|---|---|---|
| `place_id` | Identifiant Google du lieu | **Indispensable** — clé de mise à jour ultérieure |
| `telephone` | Numéro national, format `03 87 12 34 56` | **Indispensable** |
| `horaires` | Sept lignes, une par jour, texte lisible | **Indispensable** |
| `categorie` | Catégorie principale déclarée sur la fiche Google | **Indispensable** — elle sert à écarter les faux positifs (voir §5) |
| `site_web` | URL du site officiel | Haute |
| `note` | Note moyenne, sur 5 | Haute |
| `nb_avis` | Nombre total d'avis | Haute |
| `statut` | Ouvert, fermé temporairement, fermé définitivement | Haute |
| `adresse_google` | Adresse formatée telle que Google l'affiche | Moyenne |
| `lat_google`, `lon_google` | Coordonnées Google | Moyenne |
| `nom_google` | Nom affiché sur la fiche | Moyenne |

**Ne sont pas demandés** : le contenu des avis, les noms ou photos d'auteurs d'avis, les photos de l'établissement, les données de fréquentation. Aucune donnée personnelle n'entre dans le périmètre.

---

## 4. Format de sortie

Un fichier JSON par département, nommé `dept-XX.json`, contenant un tableau d'objets :

```json
[
  {
    "siret": "45077696800080",
    "place_id": "ChIJ...",
    "nom_google": "Loxam Sarreguemines",
    "telephone": "03 87 12 34 56",
    "site_web": "https://www.loxam.fr/agence/sarreguemines",
    "horaires": [
      "lundi: 07:30 – 12:00, 13:30 – 18:00",
      "mardi: 07:30 – 12:00, 13:30 – 18:00"
    ],
    "note": 4.2,
    "nb_avis": 87,
    "statut": "OPERATIONAL",
    "categorie": "Service de location de matériel",
    "adresse_google": "14 Rue Gutenberg, 57200 Sarreguemines",
    "lat_google": 49.110471,
    "lon_google": 7.100321,
    "collecte_le": "2026-07-26"
  }
]
```

**Le `siret` doit être repris tel quel** depuis le fichier d'entrée. C'est lui qui permet de réinjecter les données dans la base sans doublon.

Si un établissement reste introuvable, il faut le retourner quand même, avec `"place_id": null` et un champ `"motif": "aucune correspondance"`. Une absence documentée vaut mieux qu'une ligne manquante.

---

## 5. Règles de qualité

**Rapprochement.** Chercher par `raison_sociale` **plus** `adresse` complète. La raison sociale seule produit des faux positifs : beaucoup de ces entreprises portent des noms génériques (« Loca-Metz », « VKLOC », « PA PIL »). En cas de doute entre deux résultats, retenir celui dont l'adresse correspond au code postal du fichier d'entrée.

**Rejet.** Si l'adresse trouvée est dans un autre département que celui indiqué, ne pas apparier : retourner `place_id: null`. Un mauvais appariement est plus coûteux qu'une absence, parce qu'il publie une donnée fausse sous un nom réel.

**Catégorie.** Le champ `categorie` est déterminant au-delà de l'affichage : la liste d'entrée contient du bruit, issu de codes d'activité trop larges. La catégorie Google permet d'écarter les entreprises de terrassement, de transport routier, d'échafaudage ou de déménagement qui figurent encore dans la liste.

**Fraîcheur.** Horodater chaque collecte avec `collecte_le`. Les horaires et les notes se périment ; une donnée non datée devient inexploitable.

**Établissements fermés.** Les retourner avec leur statut réel plutôt que les omettre : la fiche correspondante sera dépubliée côté site.

---

## 6. Volume et rythme

Environ 100 à 130 établissements par département, soit **de l'ordre de 10 000 pour la France entière**.

L'annuaire publie par lots de 100 pages, avec un contrôle du taux d'indexation à trente jours avant de lancer le lot suivant. La collecte peut donc être étalée : **1 000 établissements par mois suffisent** à alimenter le rythme de publication. Il n'y a aucun intérêt à tout collecter d'un coup.

Ordre de priorité des départements : ceux qui concentrent l'activité industrielle et logistique — 59, 69, 13, 44, 33, 67, 57, 76, 38, 31 — avant le reste.

---

## 7. Contrainte de réutilisation

Les données collectées seront publiées sur un site public. Le moyen d'obtention doit permettre cette réutilisation : c'est le cas de l'API Places de Google dans les limites de ses conditions, et des fournisseurs de données sous licence commerciale. Ce point est à vérifier avant la collecte, pas après.
