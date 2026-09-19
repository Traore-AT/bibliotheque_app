<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

/**
 * Modèle métier des avis (table `avis`) : note de 1 à 5 étoiles
 * + commentaire. Contrainte UNIQUE (id_livre, id_utilisateur) :
 * un utilisateur ne peut laisser qu'un seul avis par livre
 * (le dépôt d'un nouvel avis met à jour l'existant).
 */
final class Review extends AbstractModel
{
    /**
     * Tous les avis d'un livre, avec le prénom/nom de l'auteur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forBook(int $bookId): array
    {
        return $this->rows(
            'SELECT a.id AS review_id, a.note, a.commentaire, a.date_publication,
                    u.id AS user_id, u.nom AS user_nom, u.prenom AS user_prenom
             FROM avis a
             INNER JOIN utilisateurs u ON u.id = a.id_utilisateur
             WHERE a.id_livre = :book
             ORDER BY a.date_publication DESC',
            [':book' => $bookId]
        );
    }

    /**
     * Note moyenne et nombre d'avis pour un livre.
     *
     * @return array{ avg: float, count: int }
     */
    public function averageFor(int $bookId): array
    {
        $row = $this->one(
            'SELECT AVG(note) AS avg_note, COUNT(*) AS nb
             FROM avis WHERE id_livre = :book',
            [':book' => $bookId]
        );

        $avg = round((float) ($row['avg_note'] ?? 0), 1);
        $count = (int) ($row['nb'] ?? 0);

        return ['avg' => $avg, 'count' => $count];
    }

    /**
     * Avis d'un utilisateur pour un livre donné (ou null).
     * @return array<string, mixed>|null
     */
    public function findFor(int $userId, int $bookId): ?array
    {
        return $this->one(
            'SELECT id AS review_id, note, commentaire, date_publication
             FROM avis
             WHERE id_livre = :book AND id_utilisateur = :user
             LIMIT 1',
            [':book' => $bookId, ':user' => $userId]
        );
    }

    /**
     * Dépose (ou met à jour) un avis. La contrainte UNIQUE est gérée.
     *
     * @return string 'created' | 'updated' | 'duplicate'
     */
    public function save(int $userId, int $bookId, int $note, string $commentaire): string
    {
        $exists = $this->findFor($userId, $bookId) !== null;

        try {
            $this->execute(
                'INSERT INTO avis (id_livre, id_utilisateur, note, commentaire)
                 VALUES (:book, :user, :note, :commentaire)
                 ON DUPLICATE KEY UPDATE
                     note = VALUES(note),
                     commentaire = VALUES(commentaire),
                     date_publication = NOW()',
                [
                    ':book'        => $bookId,
                    ':user'        => $userId,
                    ':note'        => $note,
                    ':commentaire' => $commentaire,
                ]
            );

            return $exists ? 'updated' : 'created';
        } catch (PDOException $e) {
            return 'duplicate';
        }
    }

    /**
     * Supprime un avis Si et seulement s'il appartient à l'utilisateur.
     */
    public function delete(int $reviewId, int $userId): bool
    {
        $stmt = $this->execute(
            'DELETE FROM avis WHERE id = :id AND id_utilisateur = :user',
            [':id' => $reviewId, ':user' => $userId]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Tous les avis d'un utilisateur, avec le titre du livre associé.
     *
     * @return array<int, array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        return $this->rows(
            'SELECT a.id AS review_id, a.note, a.commentaire, a.date_publication,
                    l.id AS livre_id, l.titre AS livre_titre, l.couverture AS livre_couverture
             FROM avis a
             INNER JOIN livres l ON l.id = a.id_livre
             WHERE a.id_utilisateur = :user
             ORDER BY a.date_publication DESC',
            [':user' => $userId]
        );
    }

    /**
     * Nombre total d'avis (statistique admin).
     */
    public function count(): int
    {
        return (int) $this->one('SELECT COUNT(*) AS n FROM avis')['n'];
    }
}