# Bibliothèque Numérique en Ligne

Application web PHP 8 / MySQL — Architecture **MVC** (POO, PSR-4), conforme OWASP.

### Lien github

- **Pour cloner le projet sur github:**

```bash
git clone https://github.com/Traore-AT/bibliotheque_app.git
```

- **Pour recuperer les derniere modifications:**

```bash
git pull origin main
```

## Fonctionnalités

- **Catalogue de livres** paginé (10 livres/page) + **recherche temps réel** (AJAX / Fetch API, requête `LIKE`).
- **Fiche détaillée** : couverture, description, disponibilité en temps réel, note moyenne et avis des lecteurs.
- **Authentification & rôles** : inscription, connexion, déconnexion ; deux rôles (`lecteur`, `admin`) avec **middleware d'accès** 403.
- **Emprunts stricts avec verrouillage de disponibilité** : un emprunt décrémente le stock de façon atomique (`UPDATE … WHERE nombre_exemplaire > 0` en transaction) ; le retour le ré-incrémente. Un livre mono-exemplaire est réservé à un seul lecteur.
- **Espace personnel lecteur** (`/profile`) : emprunts en cours (restitution), historique, mes avis.
- **Avis & notes (1 à 5 étoiles)** : un avis par livre et par utilisateur (contrainte `UNIQUE`), dépôt / modification / suppression, note moyenne.
- **Panneau d'administration** : tableau de bord statistique, CRUD des livres, gestion des utilisateurs et de leurs rôles, vue globale des emprunts + retour manuel.
- **Upload de couvertures** : ajout / remplacement / retrait des images (JPG, PNG, GIF, WebP ≤ 2 Mo), validation par type MIME réel, stockage sécurisé dans `public/uploads/` (exécution désactivée) et nettoyage automatique des fichiers orphelins.
- Protection **CSRF** sur tous les POST, requêtes **préparées PDO** (anti-injection), échappement **XSS** (`htmlspecialchars`) sur toutes les vues, hachage des mots de passe avec `password_hash()`.
- HTML5 sémantique, CSS3 responsive natif (Grid/Flex), accessibilité **WCAG** (44px, contrastes, focus, `prefers-reduced-motion`).

## Structure

```
/
├── app/
│   ├── Controllers/     Book, Auth, Loan, Review, Admin
│   ├── Models/          Book, User, Loan, Review, AbstractModel
│   ├── Views/
│   │   ├── layouts/     main, errors
│   │   ├── partials/    stars
│   │   ├── books/       index, show, _search_results
│   │   ├── auth/        login, register
│   │   ├── profile/     index
│   │   └── admin/       index, form, users, loans, _form_fields
│   ├── Core/            Router, Database, Controller, Session, Csrf, CoverUploader, helpers
├── config/              config.php (constantes centralisées)
├── public/              Front Controller (index.php) + css/ js/ uploads/
│   └── index.php        Point d'entrée unique (Front Controller)
├── sql/                 schema.sql (schéma complet centralisé + seeders)
└── README.md
```

## Installation

1. **Import de la base de données** (phpMyAdmin, MySQL 8.0+) : exécuter le fichier unique `sql/schema.sql`.
   - `schema.sql` crée la base `bibliotheque_db` (charset `utf8mb4`), l'ensemble des tables (`utilisateurs`, `livres`, `emprunts`, `avis`, `blog_articles`, `contacts`) et les données de démonstration.

2. **Configuration** : adapter les constantes DB dans `config/config.php`
   (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`, éventuellement le port + `APP_BASE_URL` si l'app est dans un sous-dossier du vhost).

3. **Serveur Web** :

   - **Apache (recommandé)** : pointez le vhost vers `public/`.
     Le fichier `public/.htaccess` réécrit toutes les URL vers `index.php`.
   - **PHP intégré** (développement) :
     ```
     php -S localhost:8000 -t public
     ```
     Les URL deviennent alors `?page=...` (ex : `index.php?page=book/show&id=1`).

4. **Accès** :
   - Catalogue et recherche : `/` (ou `?page=books`)
   - Blog & Hommage D-CLIC : `/blog` (ou `?page=blog`)
   - Contact & Support : `/contact` (ou `?page=contact`)
   - Détail livre : `book/show?id=1`
   - Comptes de démonstration :
     - Administrateur : `admin@bibliotheque.sn` / `Admin123!`
     - Lecteur (utilisateurs migrés) : mot de passe commun `Lecteur123!`
       (ex. `alseny.traore@etudiant.sn`)
   - Connexion : `/login` · Inscription : `/inscription`
   - Espace personnel : `/profile` · Espace auteur : `/auteur` · Administration : `/admin`

## Mode d'utilisation

### 1. Consulter le catalogue et rechercher

1. Ouvrir la page d'accueil : `http://localhost/bibliotheque_app/public/` (ou `http://localhost:8000` avec le serveur PHP intégré).
2. Le catalogue affiche **10 livres par page**, avec le badge de disponibilité et la note moyenne.
3. **Recherche instantanée** : saisir au moins **2 caractères** dans la barre de recherche. Une liste de suggestions apparaît automatiquement (AJAX, sans rechargement) sur le **titre** ou l'**auteur**. Un clic sur une suggestion ouvre la fiche du livre.
4. **Recherche classique** : appuyer sur « Rechercher » (ou `Entrée`) filtre la page complète via l'URL `?q=...` ; le bouton « Réinitialiser la recherche » vide le filtre.

### 2. S'authentifier

1. Se créer un compte via **« Créer un compte »** (`/inscription`) : prénom, nom, email unique, mot de passe (8 caractères min., haché en base).
2. Se connecter via **« Connexion »** (`/login`). La session est régénérée à la connexion (anti-fixation).
3. Rôles : tout nouveau compte est **lecteur** ; seuls les administrateurs peuvent promouvoir (l'application garantit au moins un admin).
4. Les pages d'authentification sont inaccessibles à un utilisateur déjà connecté (redirection vers son espace).

### 3. Emprunter un livre (verrouillage de disponibilité)

1. Ouvrir la fiche d'un livre (`book/show?id=X`).
2. Si des exemplaires sont disponibles et que vous n'avez pas ce livre en cours, un bouton **« Emprunter ce livre »** s'affiche : le stock est décrémenté et une date de retour (+14 jours) est fixée.
3. Si tous les exemplaires sont empruntés, le badge **« Indisponible — actuellement loué »** s'affiche : l'emprunt est refusé (la contrainte est appliquée au niveau de la requête `UPDATE … WHERE nombre_exemplaire > 0`, en transaction).
4. Si vous empruntez (ou restituez) le livre, la fiche reflète aussitôt votre statut.

### 4. Gérer son espace personnel

1. **« Mon espace »** (`/profile`) : emprunts en cours (avec date de rendu et badge « En retard » si dépassée), bouton **« Rendre le livre »** (le stock est ré-incrémenté), historique des emprunts rendus, et la liste de **mes avis**.
2. Le rendu est confirmé par une boîte de dialogue JS. Un emprunt en retard reste parfaitement restituable.

### 5. Donner son avis (étoiles)

1. Sur la fiche d'un livre, la section **« Avis des lecteurs »** affiche la note moyenne et les avis publiés.
2. Connecté : cliquer sur une étoile (1 à 5) puis publier un commentaire. Un seul avis par livre est possible : le reposter **met à jour** l'avis existant.
3. « Supprimer mon avis » retire son propre avis.

### 6. Administrer la plateforme

1. **« Administration »** (`/admin`) : statistiques globales (livres, utilisateurs, emprunts en cours, retards, avis) et CRUD des livres.
2. **Ajouter / Modifier / Supprimer** : mêmes usages qu'en v1 ; la **couverture** se réglé via la zone d'upload (aperçu, glisser-déposer, remplacement, retrait).
3. **« Utilisateurs »** (`/admin/users`) : liste des comptes, changement de rôle lecteur ↔ administrateur (impossible de retirer le dernier admin).
4. **« Emprunts »** (`/admin/loans`) : vue globale, filtres par statut (tous / en cours / en retard / terminés) et recherche ; bouton **« Rendre le livre »** pour un retour manuel.

### 7. Comportement attendu en cas d'erreur

| Situation | Réponse de l'application |
|---|---|
| Route ou livre inexistant | Page **404** personnalisée |
| Accès Lecteur à `/admin` | Page **403** Accès refusé |
| Accès non connecté à `/profile`, emprunt ou avis | Redirection vers **/login** avec message flash |
| Formulaire sans jeton CSRF / jeton expiré | Blocage **403** avec message explicite |
| Champs obligatoires vides | Formulaire réaffiché avec messages d'erreur par champ |
| Emprunt d'un livre indisponible | Refus avec avertissement : « tous les exemplaires sont actuellement empruntés » |
| Dépos d'un second avis sur le même livre | Mise à jour silencieuse de l'avis existant (contrainte `UNIQUE`) |
| Méthode HTTP incorrecte sur une action | **405** Méthode non autorisée |

## Sécurité mise en œuvre

| Risque                | Contre-mesure                                                            |
|-----------------------|--------------------------------------------------------------------------|
| Injection SQL         | PDO `ATTR_EMULATE_PREPARES=false` + requêtes préparées systématiques      |
| XSS                   | `htmlspecialchars()` via `e()` dans **toutes** les vues                  |
| CSRF                  | Token par session (`Csrf::check()`) sur tous les formulaires modifiants  |
| Fixation de session   | Cookie `HttpOnly`, `SameSite=Lax`, `session_regenerate_id` à la connexion |
| Course critique (stock) | Transaction PDO + décrément conditionnel `WHERE nombre_exemplaire > 0`  |
| Mots de passe         | Hachage `password_hash()` (bcrypt/argon2) + vérification `password_verify()` |
| Rôles                 | Middleware Router : `AUTH_REQUIRED` (lecteur) / `ADMIN_ONLY` (admin)      |
| Expositions données   | Constantes de config séparées, jamais de secrets dans le code            |

## Points de code notables

- `app/Core/Router.php` : analyse `?page=` ou `PATH_INFO`, résolution d'aliases, middleware d'accès (`checkAccess()`), 403/404.
- `app/Models/Loan.php` : emprunt transactionnel avec verrouillage de disponibilité ; retard calculé à la volée (`en_retard` non persisté).
- `app/Models/User.php` : `attempt()` (vérification + rehash si l'algorithme évolue), gestion du conflit d'email.
- `app/Core/Database.php` : singleton PDO (ERRMODE_EXCEPTION, vraies requêtes préparées).
- `app/Core/Csrf.php` : token `random_bytes(32)` comparé en temps constant (`hash_equals`).
- `app/Views/partials/stars.php` : rendu des étoiles (pleines / à moitié / vides) partagé par les vues.
- `public/js/app.js` : recherche AJAX debouncée (250 ms), menu mobile, confirmation JS, saisie d'étoiles.