<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * Modèle métier des livres (table `livres`).
 */
final class Book extends AbstractModel
{
    /** Statut : soumis, en attente de validation par l'admin. */
    public const STATUT_EN_ATTENTE = 'en_attente';

    /** Statut : visible dans le catalogue public (défaut). */
    public const STATUT_PUBLIE = 'publie';

    /** Statut : refusé par l'admin (l'auteur peut le modifier pour resoumettre). */
    public const STATUT_REFUSE = 'refuse';

    /**
     * Liste paginée des livres, avec recherche optionnelle
     * par titre OU auteur (LIKE SQL) et filtre de statut.
     *
     * @param int    $page      Numéro de page (1-indexé).
     * @param int    $perPage   Nombre d'articles par page.
     * @param string $search    Terme de recherche (titre/auteur).
     * @param string $statut    Filtre de statut ('' = tous, Book::STATUT_* sinon).
     *
     * @return array{ books: array<int, array<string, mixed>>, total: int, pages: int, page: int }
     */
    public function paginated(int $page = 1, int $perPage = 10, string $search = '', string $statut = self::STATUT_PUBLIE): array
    {
        $search = trim($search);
        $where = '';
        $params = [];

        if ($statut !== '') {
            $where = ' WHERE statut = :statut';
            $params[':statut'] = $statut;
        }

        // Recherche : titre OU auteur (requête LIKE préparée).
        if ($search !== '') {
            $where .= ($where === '' ? ' WHERE' : ' AND') . ' (titre LIKE :q1 OR auteur LIKE :q2)';
            $params[':q1'] = '%' . $search . '%';
            $params[':q2'] = '%' . $search . '%';
        }

        // Total d'éléments pour le calcul de la pagination.
        $total = (int) $this->one(
            'SELECT COUNT(*) AS n FROM livres' . $where,
            $params
        )['n'];

        $pages = max(1, (int) ceil($total / $perPage));
        $page = max(1, min($page, $pages));
        $offset = ($page - 1) * $perPage;

        $params[':limit'] = $perPage;
        $params[':offset'] = $offset;

        $books = $this->rows(
            'SELECT l.*, u.prenom AS auteur_prenom, u.nom AS auteur_nom
             FROM livres l
             LEFT JOIN utilisateurs u ON u.id = l.id_auteur' . $where .
            ' ORDER BY titre ASC LIMIT :limit OFFSET :offset',
            $params
        );

        return [
            'books' => $books,
            'total' => $total,
            'pages' => $pages,
            'page'  => $page,
        ];
    }

    /**
     * Recherche en temps réel (AJAX) : retourne les 8 premiers résultats
     * correspondant au titre ou à l'auteur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function search(string $term, int $limit = 8): array
    {
        $term = trim($term);

        if ($term === '') {
            return [];
        }

        return $this->rows(
            'SELECT l.id, l.titre, l.auteur, l.maison_edition, l.nombre_exemplaire
             FROM livres l
             WHERE l.statut = :statut AND (l.titre LIKE :q1 OR l.auteur LIKE :q2)
             ORDER BY l.titre ASC
             LIMIT :limit',
            [
                ':statut' => self::STATUT_PUBLIE,
                ':q1' => '%' . $term . '%',
                ':q2' => '%' . $term . '%',
                ':limit' => $limit,
            ]
        );
    }

    /**
     * Fiche complète d'un livre (par nom/prénom du déposant si connu).
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        return $this->one(
            'SELECT l.*, u.prenom AS auteur_prenom, u.nom AS auteur_nom
             FROM livres l
             LEFT JOIN utilisateurs u ON u.id = l.id_auteur
             WHERE l.id = :id',
            [':id' => $id]
        );
    }

    /**
     * Livres déposés par un utilisateur (espace auteur), tous statuts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function byAuthor(int $authorId): array
    {
        return $this->rows(
            'SELECT l.*, u.prenom AS auteur_prenom, u.nom AS auteur_nom
             FROM livres l
             LEFT JOIN utilisateurs u ON u.id = l.id_auteur
             WHERE l.id_auteur = :authorId
             ORDER BY FIELD(l.statut, \'en_attente\', \'publie\', \'refuse\'), l.titre ASC',
            [':authorId' => $authorId]
        );
    }

    /**
     * Crée un nouveau livre. Retourne l'identifiant inséré.
     *
     * @param array<string, string|int|null> $data
     * @param int|null  $authorId  Utilisateur qui écrit/soumet le livre.
     * @param string    $statut    Statut initial du livre.
     */
    public function create(array $data, ?int $authorId = null, string $statut = self::STATUT_PUBLIE): int
    {
        $this->execute(
            'INSERT INTO livres (titre, auteur, description, maison_edition, nombre_exemplaire, couverture, id_auteur, statut)
             VALUES (:titre, :auteur, :description, :maison_edition, :nombre_exemplaire, :couverture, :id_auteur, :statut)',
            [
                ':titre'            => $data['titre'],
                ':auteur'           => $data['auteur'],
                ':description'      => $data['description'],
                ':maison_edition'   => $data['maison_edition'],
                ':nombre_exemplaire'=> $data['nombre_exemplaire'],
                ':couverture'       => $data['couverture'] ?? null,
                ':id_auteur'        => $authorId,
                ':statut'           => $statut,
            ]
        );

        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un livre existant.
     *
     * @param array<string, string|int|null> $data
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->execute(
            'UPDATE livres
             SET titre = :titre, auteur = :auteur, description = :description,
                 maison_edition = :maison_edition, nombre_exemplaire = :nombre_exemplaire,
                 couverture = :couverture
             WHERE id = :id',
            [
                ':titre'            => $data['titre'],
                ':auteur'           => $data['auteur'],
                ':description'      => $data['description'],
                ':maison_edition'   => $data['maison_edition'],
                ':nombre_exemplaire'=> $data['nombre_exemplaire'],
                ':couverture'       => $data['couverture'] ?? null,
                ':id'               => $id,
            ]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Supprime un livre (les réservations liées sont cascadées).
     */
    public function delete(int $id): bool
    {
        $stmt = $this->execute('DELETE FROM livres WHERE id = :id', [':id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Nombre total de livres (pour le tableau de bord admin).
     */
    public function count(): int
    {
        return (int) $this->one('SELECT COUNT(*) AS n FROM livres')['n'];
    }

    /**
     * Nombre de livres selon un statut (ex : en attente de modération).
     */
    public function countByStatus(string $statut): int
    {
        return (int) $this->one(
            'SELECT COUNT(*) AS n FROM livres WHERE statut = :statut',
            [':statut' => $statut]
        )['n'];
    }

    /**
     * Change le statut d'un livre (publication / refus / remise en attente).
     */
    public function setStatus(int $id, string $statut): bool
    {
        $stmt = $this->execute(
            'UPDATE livres SET statut = :statut WHERE id = :id',
            [':statut' => $statut, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }
}