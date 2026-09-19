<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle Blog : gestion des articles, photos, catégories et compteur de vues.
 */
class Blog extends AbstractModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /**
     * Crée la table et insère les articles de démonstration (y compris l'hommage D-CLIC)
     * automatiquement si elle n'existe pas encore.
     */
    private function ensureTable(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS blog_articles (
                id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
                id_utilisateur INT UNSIGNED  NULL DEFAULT NULL,
                auteur_nom     VARCHAR(100)  NOT NULL,
                titre          VARCHAR(200)  NOT NULL,
                slug           VARCHAR(220)  NOT NULL,
                chapeau        TEXT          NOT NULL,
                contenu        LONGTEXT      NOT NULL,
                categorie      VARCHAR(60)   NOT NULL DEFAULT 'Actualités',
                temps_lecture  VARCHAR(20)   NOT NULL DEFAULT '4 min',
                image          VARCHAR(255)  NULL DEFAULT NULL,
                epingle        TINYINT(1)    NOT NULL DEFAULT 0,
                vues           INT UNSIGNED  NOT NULL DEFAULT 0,
                created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id),
                KEY idx_blog_categorie (categorie),
                KEY idx_blog_epingle (epingle),
                KEY idx_blog_created (created_at)
            ) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
        ");

        // Vérification si des articles existent déjà
        $count = (int) $this->db->query("SELECT COUNT(*) FROM blog_articles")->fetchColumn();
        if ($count === 0) {
            $this->seedInitialArticles();
        }
    }

    /**
     * Insère les articles de référence et l'hommage D-CLIC.
     */
    private function seedInitialArticles(): void
    {
        $articles = [
            [
                'auteur_nom'    => 'Traoré Alseny',
                'titre'         => 'Retour sur mon parcours D-CLIC : 6 semaines intensives pour forger un développeur web moderne',
                'slug'          => 'retour-sur-mon-parcours-d-clic-6-semaines-intensives',
                'chapeau'       => 'Témoignage émouvant et sincères remerciements au programme D-CLIC ainsi qu\'à mon tuteur pour cette formation intermédiaire en développement web de 6 semaines qui a permis de donner vie à cette Bibliothèque Numérique.',
                'contenu'       => "## Un tremplin d'excellence pour mon avenir dans le numérique\n\nLorsque j'ai intégré la **formation de niveau intermédiaire en développement web du programme D-CLIC**, j'avais une ambition claire : consolider mes bases, acquérir les méthodes professionnelles de l'ingénierie web et franchir un palier décisif.\n\nDurant **6 semaines intenses et enrichissantes**, nous avons exploré les facettes les plus exigeantes du développement web moderne : de la conception de bases de données relationnelles optimisées à la mise en place d'une architecture MVC rigoureuse en PHP 8 natif, en passant par la sécurité (PDO, CSRF, hachage des mots de passe) et l'ergonomie UI/UX moderne.\n\n### Un immense merci au programme D-CLIC\n\nJe tiens à exprimer ma profonde et sincère gratitude à l'équipe du programme **D-CLIC** (Organisation Internationale de la Francophonie). Votre engagement en faveur de l'autonomisation des jeunes talents par le biais des métiers du numérique est un catalyseur d'opportunités inestimable.\n\n### Hommage respectueux et chaleureux à mon tuteur / coach\n\nUne formation d'exception repose avant tout sur la qualité humaine et pédagogique de son encadrement. Je souhaite rendre un **hommage tout particulier à mon tuteur et coach** qui a assuré notre suivi durant ces 6 semaines.\n\nPar sa disponibilité sans faille, ses retours bienveillants et sa capacité à nous pousser vers l'excellence technique, il a su débloquer chaque doute, nous transmettre la culture du code propre (*Clean Code*) et nous encourager à toujours aller au bout de nos idées.\n\n### Ce projet de fin de formation : la concrétisation de nos acquis\n\nLa réalisation de cette application de **Bibliothèque Numérique** représente pour moi la plus belle des consécrations. C'est l'incarnation concrète de tout ce que j'ai appris et expérimenté :\n\n- **Architecture robuste** : Front Controller, routeur d'URL propre, autoloader PSR-4 et séparation stricte des couches Modèle-Vue-Contrôleur.\n- **Sécurité intégrée** : requêtes préparées systématiques, filtrage des saisies, gestion étanche des sessions et des rôles d'accès.\n- **Expérience Utilisateur raffinée (UI/UX)** : interface sobre, typographie soignée, composants modulaires, toasts dynamiques et micro-interactions modernes.\n- **Gestion métier complète** : catalogue de livres avec gestion de stock en temps réel, emprunts contrôlés, espace auteur collaboratif, modération administrative et maintenant ce module d'articles et d'échanges.\n\nCe projet final n'est pas une fin en soi, mais le début prometteur d'une aventure passionnée dans l'écosystème du développement web !",
                'categorie'     => 'Formation D-CLIC',
                'temps_lecture' => '5 min',
                'image'         => null,
                'epingle'       => 1,
                'vues'          => 185,
            ],
            [
                'auteur_nom'    => 'Traoré Alseny',
                'titre'         => 'Comment le numérique transforme l\'accès aux livres et à la culture en Afrique',
                'slug'          => 'comment-le-numerique-transforme-l-acces-aux-livres-en-afrique',
                'chapeau'       => 'Analyse des opportunités qu\'offrent les bibliothèques en ligne pour démocratiser la lecture, valoriser la littérature africaine et stimuler l\'apprentissage continu.',
                'contenu'       => "## La révolution de la lecture connectée\n\nÀ l'ère où les smartphones et les connexions internet se généralisent, les bibliothèques numériques représentent une passerelle incontournable pour faciliter l'accès aux œuvres littéraires, scientifiques et pédagogiques.\n\n### Rapprocher les lecteurs des chefs-d'œuvre\n\nEn éliminant les contraintes géographiques, notre plateforme permet aux passionnés comme aux étudiants d'explorer instantanément un catalogue varié, de consulter les disponibilités en temps réel et de réserver leurs lectures en quelques clics.\n\n### Mettre en avant nos auteurs locaux\n\nGrâce à l'espace Auteur intégré, chaque plume peut soumettre ses manuscrits, partager des récits et trouver son public. C'est un levier puissant pour préserver et diffuser nos patrimoines culturels et contemporains.",
                'categorie'     => 'Culture & Numérique',
                'temps_lecture' => '4 min',
                'image'         => null,
                'epingle'       => 0,
                'vues'          => 112,
            ],
            [
                'auteur_nom'    => 'Traoré Alseny',
                'titre'         => 'Les coulisses techniques de la Bibliothèque Numérique : du MVC au Clean Code',
                'slug'          => 'les-coulisses-techniques-de-la-bibliotheque-numerique',
                'chapeau'       => 'Plongée dans les choix d\'architecture, les principes de conception sécurisés et les optimisations UI/UX réalisés dans cette application PHP/MySQL native.',
                'contenu'       => "## Pourquoi le PHP natif moderne en architecture MVC ?\n\nConstruire une application complète sans framework lourd permet de maîtriser chaque rouage du cycle de vie d'une requête HTTP :\n\n- **Routeur centralisé** : résolution élégante des URLs avec alias conviviaux.\n- **Contrôleurs et Modèles découpés** : séparation claire des responsabilités.\n- **PDO & Sécurité** : zéro concaténation SQL, protection XSS avec typage strict PHP 8.\n- **Design System Vanilla CSS** : une vitesse de chargement instantanée et une identité visuelle soignée sans surcoût.",
                'categorie'     => 'Tech & Architecture',
                'temps_lecture' => '6 min',
                'image'         => null,
                'epingle'       => 0,
                'vues'          => 94,
            ]
        ];

        foreach ($articles as $art) {
            $sql = "INSERT INTO blog_articles (auteur_nom, titre, slug, chapeau, contenu, categorie, temps_lecture, image, epingle, vues)
                    VALUES (:auteur_nom, :titre, :slug, :chapeau, :contenu, :categorie, :temps_lecture, :image, :epingle, :vues)";
            $this->execute($sql, $art);
        }
    }

    /**
     * Liste tous les articles avec filtre de recherche et catégorie optionnels.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(?string $categorie = null, ?string $search = null): array
    {
        $sql = "SELECT b.*, 
                       DATE_FORMAT(b.created_at, '%d/%m/%Y à %H:%i') as date_formatee,
                       DATE_FORMAT(b.created_at, '%d %b %Y') as date_courte
                FROM blog_articles b
                WHERE 1=1";
        $params = [];

        if ($categorie !== null && $categorie !== '' && $categorie !== 'tous') {
            $sql .= " AND b.categorie = :categorie";
            $params['categorie'] = $categorie;
        }

        if ($search !== null && $search !== '') {
            $sql .= " AND (b.titre LIKE :search OR b.chapeau LIKE :search OR b.contenu LIKE :search OR b.auteur_nom LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY b.epingle DESC, b.created_at DESC";

        return $this->rows($sql, $params);
    }

    /**
     * Récupère l'article épinglé (ex: hommage D-CLIC).
     *
     * @return array<string, mixed>|null
     */
    public function getFeatured(): ?array
    {
        $sql = "SELECT b.*, 
                       DATE_FORMAT(b.created_at, '%d/%m/%Y') as date_formatee,
                       DATE_FORMAT(b.created_at, '%d %b %Y') as date_courte
                FROM blog_articles b
                WHERE b.epingle = 1
                ORDER BY b.created_at DESC
                LIMIT 1";
        return $this->one($sql);
    }

    /**
     * Trouve un article par son ID.
     *
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT b.*, 
                       DATE_FORMAT(b.created_at, '%d/%m/%Y à %H:%i') as date_formatee,
                       DATE_FORMAT(b.created_at, '%d %M %Y') as date_longue
                FROM blog_articles b
                WHERE b.id = :id
                LIMIT 1";
        return $this->one($sql, ['id' => $id]);
    }

    /**
     * Trouve un article par son slug.
     *
     * @return array<string, mixed>|null
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT b.*, 
                       DATE_FORMAT(b.created_at, '%d/%m/%Y à %H:%i') as date_formatee,
                       DATE_FORMAT(b.created_at, '%d %M %Y') as date_longue
                FROM blog_articles b
                WHERE b.slug = :slug
                LIMIT 1";
        return $this->one($sql, ['slug' => $slug]);
    }

    /**
     * Incrémente le compteur de vues d'un article.
     */
    public function incrementViews(int $id): void
    {
        $this->execute("UPDATE blog_articles SET vues = vues + 1 WHERE id = :id", ['id' => $id]);
    }

    /**
     * Crée un nouvel article avec photo.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data, ?int $userId = null): int
    {
        $slug = $this->generateSlug($data['titre']);
        
        // Calcul du temps de lecture approximatif
        $wordCount = str_word_count(strip_tags((string) ($data['contenu'] ?? '')));
        $minutes = max(1, (int) ceil($wordCount / 200));
        $tempsLecture = $minutes . ' min';

        $sql = "INSERT INTO blog_articles (id_utilisateur, auteur_nom, titre, slug, chapeau, contenu, categorie, temps_lecture, image, epingle, vues)
                VALUES (:id_utilisateur, :auteur_nom, :titre, :slug, :chapeau, :contenu, :categorie, :temps_lecture, :image, :epingle, 0)";

        $this->execute($sql, [
            'id_utilisateur' => $userId,
            'auteur_nom'     => $data['auteur_nom'],
            'titre'          => $data['titre'],
            'slug'           => $slug,
            'chapeau'        => $data['chapeau'],
            'contenu'        => $data['contenu'],
            'categorie'      => $data['categorie'] ?? 'Actualités',
            'temps_lecture'  => $tempsLecture,
            'image'          => $data['image'] ?? null,
            'epingle'        => !empty($data['epingle']) ? 1 : 0,
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Liste des catégories uniques disponibles.
     *
     * @return array<int, string>
     */
    public function getCategories(): array
    {
        $rows = $this->rows("SELECT DISTINCT categorie FROM blog_articles ORDER BY categorie ASC");
        return array_map(static fn($r) => (string) $r['categorie'], $rows);
    }

    /**
     * Articles récents connexes (hors article courant).
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRelated(int $currentId, string $categorie, int $limit = 3): array
    {
        $sql = "SELECT b.*, DATE_FORMAT(b.created_at, '%d %b %Y') as date_courte
                FROM blog_articles b
                WHERE b.id != :id
                ORDER BY (b.categorie = :cat) DESC, b.created_at DESC
                LIMIT " . (int) $limit;
        return $this->rows($sql, ['id' => $currentId, 'cat' => $categorie]);
    }

    /**
     * Génère un slug propre à partir d'un titre.
     */
    private function generateSlug(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('~[^\pL\d]+~u', '-', $slug);
        $slug = iconv('utf-8', 'us-ascii//TRANSLIT', (string) $slug);
        $slug = preg_replace('~[^-\w]+~', '', (string) $slug);
        $slug = trim((string) $slug, '-');
        $slug = preg_replace('~-+~', '-', (string) $slug);
        
        if (empty($slug)) {
            $slug = 'article-' . time();
        }

        // Vérifier l'unicité
        $base = $slug;
        $i = 1;
        while ($this->one("SELECT id FROM blog_articles WHERE slug = :slug", ['slug' => $slug]) !== null) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
