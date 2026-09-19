-- ============================================================
-- BIBLIOTHEQUE NUMERIQUE EN LIGNE
-- SCHÉMA COMPLET & CENTRALISÉ DE LA BASE DE DONNÉES
-- Inclus : Authentification, Livres, Espace Auteur, Emprunts,
--          Avis, Blog (avec Hommage D-CLIC) et Contact.
-- MySQL 8.x — Charset utf8mb4 / Collation utf8mb4_unicode_ci
-- ============================================================

CREATE DATABASE IF NOT EXISTS bibliotheque_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bibliotheque_db;

-- ------------------------------------------------------------
-- 1. TABLE : utilisateurs
--    Comptes lecteurs et administrateurs avec mots de passe hachés.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id            INT UNSIGNED             NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(100)             NOT NULL,
    prenom        VARCHAR(100)             NOT NULL,
    email         VARCHAR(100)             NOT NULL,
    mot_de_passe  VARCHAR(255)             NOT NULL,
    role          ENUM('lecteur', 'admin') NOT NULL DEFAULT 'lecteur',
    date_creation TIMESTAMP                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_utilisateurs_email (email)
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 2. TABLE : livres
--    Catalogue des ouvrages avec gestion des stocks, couvertures,
--    liaison à un auteur (utilisateur) et statut de modération.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS livres (
    id                INT UNSIGNED                                   NOT NULL AUTO_INCREMENT,
    titre             VARCHAR(100)                                   NOT NULL,
    auteur            VARCHAR(100)                                   NOT NULL,
    description       TEXT                                           NOT NULL,
    maison_edition    VARCHAR(100)                                   NOT NULL,
    id_auteur         INT UNSIGNED                                   NULL DEFAULT NULL,
    statut            ENUM('en_attente', 'publie', 'refuse')         NOT NULL DEFAULT 'publie',
    nombre_exemplaire INT                                            NOT NULL DEFAULT 1,
    couverture        VARCHAR(255)                                   DEFAULT NULL,
    created_at        TIMESTAMP                                      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_livres_titre (titre),
    INDEX idx_livres_auteur (auteur),
    INDEX idx_livres_statut (statut),
    CONSTRAINT chk_exemplaire_non_negatif CHECK (nombre_exemplaire >= 0),
    CONSTRAINT fk_livres_auteur
        FOREIGN KEY (id_auteur) REFERENCES utilisateurs (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 3. TABLE : emprunts
--    Suivi des emprunts avec contrôle des dates et statuts.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS emprunts (
    id                    INT UNSIGNED                           NOT NULL AUTO_INCREMENT,
    id_livre              INT UNSIGNED                           NOT NULL,
    id_utilisateur        INT UNSIGNED                           NOT NULL,
    date_emprunt          DATETIME                               NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_retour_prevue    DATE                                   NOT NULL,
    date_retour_effective DATETIME                               NULL DEFAULT NULL,
    statut                ENUM('en_cours', 'termine', 'en_retard') NOT NULL DEFAULT 'en_cours',
    PRIMARY KEY (id),
    KEY idx_emprunts_livre_statut (id_livre, statut),
    KEY idx_emprunts_utilisateur (id_utilisateur, statut),
    CONSTRAINT fk_emprunts_livre
        FOREIGN KEY (id_livre) REFERENCES livres (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_emprunts_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 4. TABLE : avis
--    Évaluations et commentaires des lecteurs (1 à 5 étoiles).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS avis (
    id               INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    id_livre         INT UNSIGNED     NOT NULL,
    id_utilisateur   INT UNSIGNED     NOT NULL,
    note             TINYINT UNSIGNED NOT NULL,
    commentaire      TEXT             NOT NULL,
    date_publication DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_avis_livre_utilisateur (id_livre, id_utilisateur),
    KEY idx_avis_livre (id_livre),
    KEY idx_avis_utilisateur (id_utilisateur),
    CONSTRAINT chk_avis_note CHECK (note BETWEEN 1 AND 5),
    CONSTRAINT fk_avis_livre
        FOREIGN KEY (id_livre) REFERENCES livres (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_avis_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 5. TABLE : blog_articles
--    Articles, chroniques littéraires et retours d'expériences.
-- ------------------------------------------------------------
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
    KEY idx_blog_created (created_at),
    CONSTRAINT fk_blog_utilisateur
        FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- 6. TABLE : contacts
--    Messages et demandes reçus via le formulaire de contact.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contacts (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    nom        VARCHAR(100)  NOT NULL,
    email      VARCHAR(120)  NOT NULL,
    sujet      VARCHAR(150)  NOT NULL,
    categorie  VARCHAR(60)   NOT NULL DEFAULT 'Autre',
    message    TEXT          NOT NULL,
    lu         TINYINT(1)    NOT NULL DEFAULT 0,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contacts_created (created_at),
    KEY idx_contacts_lu (lu)
) ENGINE = InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;


-- ============================================================
-- JEUX DE DONNÉES INITIALES (SEEDERS)
-- ============================================================

-- ------------------------------------------------------------
-- A. Insertion des utilisateurs
--    - Admin : admin@bibliotheque.sn (Mot de passe: "Admin123!")
--    - Lecteurs : Mot de passe commun "Lecteur123!"
-- ------------------------------------------------------------
INSERT IGNORE INTO utilisateurs (id, nom, prenom, email, mot_de_passe, role) VALUES
(1, 'TRAORE', 'Alseny', 'alseny.traore@etudiant.sn', '$2y$10$f6XNVylx3xeD9/vO5NgT.es.5wYRoyW9tHyd50f68HNwqyEUp0bP2', 'lecteur'),
(2, 'DIALLO', 'Aminata', 'aminata.diallo@etudiant.sn', '$2y$10$f6XNVylx3xeD9/vO5NgT.es.5wYRoyW9tHyd50f68HNwqyEUp0bP2', 'lecteur'),
(3, 'SOW', 'Ousmane', 'ousmane.sow@etudiant.sn', '$2y$10$f6XNVylx3xeD9/vO5NgT.es.5wYRoyW9tHyd50f68HNwqyEUp0bP2', 'lecteur'),
(4, 'BA', 'Fatoumata', 'fatoumata.ba@etudiant.sn', '$2y$10$f6XNVylx3xeD9/vO5NgT.es.5wYRoyW9tHyd50f68HNwqyEUp0bP2', 'lecteur'),
(5, 'NDIAYE', 'Moussa', 'moussa.ndiaye@etudiant.sn', '$2y$10$f6XNVylx3xeD9/vO5NgT.es.5wYRoyW9tHyd50f68HNwqyEUp0bP2', 'lecteur'),
(6, 'ADMIN', 'Bibliothèque', 'admin@bibliotheque.sn', '$2y$10$gXOs9qotLfQPwuNr7b9H.OTnsmhVWXV4hMXHZzZxo0N5o3mKUTdSu', 'admin');

-- ------------------------------------------------------------
-- B. Insertion des livres (Catalogue + Espace Auteur)
-- ------------------------------------------------------------
INSERT IGNORE INTO livres (id, titre, auteur, description, maison_edition, id_auteur, statut, nombre_exemplaire, couverture) VALUES
(1, 'Le Petit Prince', 'Antoine de Saint-Exupéry', 'Conte philosophique et poétique racontant l''histoire d''un aviateur et de son petit prince venu d''une autre planète.', 'Gallimard', NULL, 'publie', 4, NULL),
(2, 'Les Misérables', 'Victor Hugo', 'Récit monumental qui suit la vie de Jean Valjean, ancien forçat, dans la France du XIXe siècle.', 'Gallimard', NULL, 'publie', 3, NULL),
(3, '1984', 'George Orwell', 'Dystopie mettant en scène Winston Smith face au régime totalitaire du Grand Frère.', 'Folio', NULL, 'publie', 3, NULL),
(4, 'Le Rouge et le Noir', 'Stendhal', 'Portrait d''un jeune provincial ambitieux, Julien Sorel, dans la France de la Restauration.', 'Le Livre de Poche', NULL, 'publie', 2, NULL),
(5, 'L''Étranger', 'Albert Camus', 'Récit de Meursault, indifférent au monde qui l''entoure, confronté à l''absurde.', 'Gallimard', NULL, 'publie', 5, NULL),
(6, 'Bel-Ami', 'Guy de Maupassant', 'Ascension sociale de Georges Duroy, arriviste séduisant dans le Paris de la Belle Époque.', 'Folio', NULL, 'publie', 3, NULL),
(7, 'Vingt Mille Lieues sous les mers', 'Jules Verne', 'Les aventures du professeur Aronnax à bord du Nautilus du capitaine Nemo.', 'Le Livre de Poche', NULL, 'publie', 3, NULL),
(8, 'Notre-Dame de Paris', 'Victor Hugo', 'Paris en 1482, l''amour impossible de Quasimodo pour la bohémienne Esmeralda.', 'Folio', NULL, 'publie', 2, NULL),
(9, 'L''Assommoir', 'Émile Zola', 'La déchéance de Gervaise, ouvrière parisienne, victime de l''alcoolisme.', 'Gallimard', NULL, 'publie', 3, NULL),
(10, 'Carmen', 'Prosper Mérimée', 'Le destin tragique de la belle et libre Carmen et du soldat Don José.', 'Le Livre de Poche', NULL, 'publie', 4, NULL),
(11, 'Madame Bovary', 'Gustave Flaubert', 'Emma Bovary, en quête d''idéal romanesque, s''étiole dans la médiocrité provinciale.', 'Folio', NULL, 'publie', 2, NULL),
(12, 'Le Comte de Monte-Cristo', 'Alexandre Dumas', 'L''évasion et la vengeance d''Edmond Dantès, injustement emprisonné au château d''If.', 'Folio', NULL, 'publie', 2, NULL),
(13, 'La Peste', 'Albert Camus', 'La ville d''Oran frappée par une épidémie de peste et la solidarité des hommes.', 'Gallimard', NULL, 'publie', 5, NULL),
(14, 'Germinal', 'Émile Zola', 'La lutte des mineurs du Nord contre les conditions de travail inhumaines.', 'Folio', NULL, 'publie', 4, NULL),
(15, 'Contes de la Casamance', 'Alseny TRAORE', 'Recueil de contes et légendes du pays Diola, transmis de génération en génération.', 'Editions Teranga', 1, 'publie', 2, NULL),
(16, 'Espoirs de Dakar', 'Aminata DIALLO', 'Nouvelles urbaines au coeur de la capitale : la jeunesse, ses rêves et ses combats.', 'Plume d''Afrique', 2, 'en_attente', 1, NULL);

-- ------------------------------------------------------------
-- C. Insertion des emprunts
-- ------------------------------------------------------------
INSERT IGNORE INTO emprunts (id_livre, id_utilisateur, date_emprunt, date_retour_prevue, date_retour_effective, statut) VALUES
(1, 1, '2026-01-15 10:00:00', '2026-02-15', '2026-02-15 18:00:00', 'termine'),
(3, 1, '2026-02-01 14:30:00', '2026-02-15', NULL, 'en_cours'),
(2, 2, '2026-01-20 09:15:00', '2026-02-20', '2026-02-20 17:00:00', 'termine'),
(5, 3, '2026-02-10 11:00:00', '2026-02-24', NULL, 'en_cours'),
(7, 4, '2026-01-25 16:45:00', '2026-02-08', NULL, 'en_cours'),
(9, 5, '2026-02-05 08:30:00', '2026-03-05', '2026-03-05 16:00:00', 'termine');

-- ------------------------------------------------------------
-- D. Insertion des avis
-- ------------------------------------------------------------
INSERT IGNORE INTO avis (id_livre, id_utilisateur, note, commentaire) VALUES
(1, 2, 5, 'Un chef-d''œuvre intemporel, à lire absolument !'),
(1, 3, 4, 'Poétique et philosophique. La rose et le renard restent gravés.'),
(3, 1, 5, 'Dérangeant et visionnaire. Une dystopie toujours d''actualité.'),
(14, 2, 4, 'La mine, la grève, la faim : une fresque sociale saisissante.'),
(5, 4, 5, 'L''indifférence de Meursault est bouleversante.'),
(11, 5, 3, 'Bien écrit, mais un personnage qui agace par moments.');

-- ------------------------------------------------------------
-- E. Insertion des articles de Blog (dont l'Hommage D-CLIC)
-- ------------------------------------------------------------
INSERT INTO blog_articles (id, id_utilisateur, auteur_nom, titre, slug, chapeau, contenu, categorie, temps_lecture, image, epingle, vues)
SELECT 
    1,
    1, 
    'Traoré Alseny', 
    'Retour sur mon parcours D-CLIC : 6 semaines intensives pour forger un développeur web moderne', 
    'retour-sur-mon-parcours-d-clic-6-semaines-intensives',
    'Témoignage et sincères remerciements au programme D-CLIC ainsi qu\'à mon tuteur pour cette formation intermédiaire en développement web qui a permis de donner vie à cette Bibliothèque Numérique.',
    '## Un tremplin décisif pour ma carrière numérique\n\nLorsque j\'ai intégré la **formation de niveau intermédiaire en développement web du programme D-CLIC**, j\'avais la soif d\'apprendre, de structurer mes compétences et de passer un cap décisif dans l\'ingénierie logicielle.\n\nDurant **6 semaines intensives**, nous avons plongé au cœur des exigences du développement web moderne. Ce ne fut pas une simple formation théorique, mais un véritable parcours d\'immersion, de rigueur technique et de partage humain.\n\n### Un immense merci à D-CLIC\n\nJe tiens à exprimer ma profonde gratitude au programme **D-CLIC** (Organisation Internationale de la Francophonie) pour cette formidable opportunité. Offrir aux jeunes talents l\'accès à une formation technique exigeante, professionnalisante et alignée sur les standards de l\'industrie est un engagement inestimable.\n\n### Hommage et gratitude à mon tuteur\n\nUn projet et une montée en compétences ne seraient rien sans un encadrement d\'exception. Je tiens à adresser mes **remerciements les plus chaleureux à mon tuteur/coach** qui nous a accompagnés avec une pédagogie remarquable, une patience infinie et une exigence bienveillante tout au long de ces 6 semaines.\n\nQu\'il s\'agisse de débloquer des défis complexes sur l\'architecture MVC, de nous guider sur les bonnes pratiques de sécurité PDO, ou de nous insuffler l\'amour du code propre et bien documenté, sa disponibilité et ses précieux retours ont été le moteur de notre progression.\n\n### Ce projet de fin de formation : la consécration pratique\n\nCette application de **Bibliothèque Numérique** est bien plus qu\'une simple démonstration : c\'est la concrétisation tangible de tout ce que j\'ai assimilé au fil des modules :\n\n- **Architecture logicielle robuste** : structuration en MVC strict avec Front Controller et Autoloader PSR-4.\n- **Sécurité approfondie** : protection CSRF systématique, requêtes préparées PDO contre les injections SQL, hachage sécurisé des mots de passe et gestion fine des rôles (Lecteur, Auteur, Administrateur).\n- **Expérience Utilisateur & Design UI/UX moderne** : interface épurée, responsive, composants interactifs, micro-animations, feedbacks toasts et accessibilité pensée pour tous.\n- **Fonctionnalités avancées** : gestion dynamique des emprunts avec contrôle des stocks, espace auteur autonome, modération des contenus, système d\'avis et désormais ce module de Blog et Contact.\n\nCe projet final marque l\'aboutissement d\'une étape clé et le début d\'une aventure passionnante dans le monde du développement web.',
    'Formation D-CLIC',
    '5 min',
    NULL,
    1,
    185
WHERE NOT EXISTS (
    SELECT 1 FROM blog_articles WHERE slug = 'retour-sur-mon-parcours-d-clic-6-semaines-intensives'
);

INSERT INTO blog_articles (id, id_utilisateur, auteur_nom, titre, slug, chapeau, contenu, categorie, temps_lecture, image, epingle, vues)
SELECT 
    2,
    1, 
    'Traoré Alseny', 
    'Comment le numérique transforme l\'accès aux livres et à la culture en Afrique', 
    'comment-le-numerique-transforme-l-acces-aux-livres-en-afrique',
    'Analyse des opportunités qu\'offrent les bibliothèques en ligne pour démocratiser la lecture, valoriser la littérature africaine et stimuler l\'apprentissage continu.',
    '## La révolution de la lecture connectée\n\nÀ l\'ère où les smartphones et les connexions internet se généralisent, les bibliothèques numériques représentent une passerelle incontournable pour faciliter l\'accès aux œuvres littéraires, scientifiques et pédagogiques.\n\n### Rapprocher les lecteurs des chefs-d\'œuvre\n\nEn éliminant les contraintes géographiques, notre plateforme permet aux passionnés comme aux étudiants d\'explorer instantanément un catalogue varié, de consulter les disponibilités en temps réel et de réserver leurs lectures en quelques clics.\n\n### Mettre en avant nos auteurs locaux\n\nGrâce à l\'espace Auteur intégré, chaque plume peut soumettre ses manuscrits, partager des récits et trouver son public. C\'est un levier puissant pour préserver et diffuser nos patrimoines culturels et contemporains.',
    'Culture & Numérique',
    '4 min',
    NULL,
    0,
    112
WHERE NOT EXISTS (
    SELECT 1 FROM blog_articles WHERE slug = 'comment-le-numerique-transforme-l-acces-aux-livres-en-afrique'
);

INSERT INTO blog_articles (id, id_utilisateur, auteur_nom, titre, slug, chapeau, contenu, categorie, temps_lecture, image, epingle, vues)
SELECT 
    3,
    1, 
    'Traoré Alseny', 
    'Les coulisses techniques de la Bibliothèque Numérique : du MVC au Clean Code', 
    'les-coulisses-techniques-de-la-bibliotheque-numerique',
    'Plongée dans les choix d\'architecture, les principes de conception sécurisés et les optimisations UI/UX réalisés dans cette application PHP/MySQL native.',
    '## Pourquoi le PHP natif moderne en MVC ?\n\nConstruire une application complète sans framework lourd permet de maîtriser chaque rouage du cycle de vie d\'une requête HTTP :\n\n- **Routeur centralisé** : résolution élégante des URLs avec alias conviviaux.\n- **Contrôleurs et Modèles découpés** : séparation claire des responsabilités.\n- **PDO & Sécurité** : zéro concaténation SQL, protection XSS avec typage strict PHP 8.\n- **Design System Vanilla CSS** : une vitesse de chargement instantanée et une identité visuelle soignée sans surcoût.',
    'Tech & Architecture',
    '6 min',
    NULL,
    0,
    94
WHERE NOT EXISTS (
    SELECT 1 FROM blog_articles WHERE slug = 'les-coulisses-techniques-de-la-bibliotheque-numerique'
);