<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use PDO;
use PDOException;

/**
 * Modèle métier des emprunts (table `emprunts`).
 *
 * Règle d'exclusivité (CRITIQUE) :
 * - `livres.nombre_exemplaire` représente le nombre d'exemplaires
 *   DISPONIBLES. Un emprunt décrémente ce stock de manière atomique
 *   (UPDATE ... WHERE nombre_exemplaire > 0) ; le retour le ré-incrémente.
 * - Un livre mono-exemplaire ne peut donc être emprunté que par une
 *   seule personne à la fois : les autres voient "Indisponible".
 */
final class Loan extends AbstractModel
{
    public const DUREE_EMPRUNT_JOURS = 14;

    public const STATUT_EN_COURS = 'en_cours';
    public const STATUT_TERMINE  = 'termine';

    /**
     * Déclenche l'emprunt d'un livre : vérifie la disponibilité
     * ET l'exclusivité, décrémente le stock et enregistre l'emprunt.
     * L'ensemble est transactionnel pour éviter toute course critique.
     *
     * @return array{ok: bool, message: string, loanId?: int}
     */
    public function borrow(int $userId, int $bookId): array
    {
        $book = $this->one(
            'SELECT id, titre, nombre_exemplaire FROM livres WHERE id = :id',
            [':id' => $bookId]
        );

        if ($book === null) {
            return ['ok' => false, 'message' => 'Ce livre est introuvable.'];
        }

        $dejaEmprunte = $this->one(
            'SELECT id FROM emprunts
             WHERE id_livre = :book AND id_utilisateur = :user AND statut = \'en_cours\'
             LIMIT 1',
            [':book' => $bookId, ':user' => $userId]
        );

        if ($dejaEmprunte !== null) {
            return ['ok' => false, 'message' => 'Vous avez déjà emprunté ce livre.'];
        }

        $dateRetourPrevue = (new DateTimeImmutable('today'))
            ->modify('+' . self::DUREE_EMPRUNT_JOURS . ' days')
            ->format('Y-m-d');

        try {
            $this->db->beginTransaction();

            // Décrémentation atomique du stock (verrouillage de disponibilité).
            $stmt = $this->execute(
                'UPDATE livres
                 SET nombre_exemplaire = nombre_exemplaire - 1
                 WHERE id = :id AND nombre_exemplaire > 0',
                [':id' => $bookId]
            );

            if ($stmt->rowCount() !== 1) {
                $this->db->rollBack();
                return [
                    'ok'      => false,
                    'message' => 'Ce livre est indisponible : tous les exemplaires sont actuellement empruntés.',
                ];
            }

            $this->execute(
                'INSERT INTO emprunts (id_livre, id_utilisateur, date_retour_prevue, statut)
                 VALUES (:book, :user, :retour, \'en_cours\')',
                [
                    ':book'   => $bookId,
                    ':user'   => $userId,
                    ':retour' => $dateRetourPrevue,
                ]
            );

            $loanId = (int) $this->db->lastInsertId();
            $this->db->commit();

            return [
                'ok'      => true,
                'message' => 'Vous avez emprunté "' . $book['titre'] .
                             '". À rendre avant le ' . $dateRetourPrevue . '.',
                'loanId'  => $loanId,
            ];
        } catch (PDOException $e) {
            try {
                $this->db->rollBack();
            } catch (PDOException) {
                // transaction déjà fermée.
            }
            return ['ok' => false, 'message' => 'Une erreur est survenue lors de l\'emprunt.'];
        }
    }

    /**
     * L'utilisateur restitue son propre livre : clôt l'emprunt
     * et ré-incrémente le stock de l'exemplaire rendu.
     */
    public function returnOwnBorrow(int $loanId, int $userId): bool
    {
        $loan = $this->one(
            'SELECT id, id_livre, statut FROM emprunts
             WHERE id = :id AND id_utilisateur = :user AND statut = \'en_cours\'
             LIMIT 1',
            [':id' => $loanId, ':user' => $userId]
        );

        if ($loan === null) {
            return false;
        }

        // Si l'hygienne des données est rompue (stock incohérent), on
        // reporte la ré-incrémentation sans échouer pour autant.
        $this->execute(
            'UPDATE livres
             SET nombre_exemplaire = nombre_exemplaire + 1
             WHERE id = :id',
            [':id' => $loan['id_livre']]
        );

        $this->execute(
            'UPDATE emprunts
             SET statut = \'termine\', date_retour_effective = NOW()
             WHERE id = :id AND statut = \'en_cours\'',
            [':id' => $loanId]
        );

        return true;
    }

    /**
     * Retour manuel d'un emprunt par l'administrateur (remet le livre
     * en disponibilité), quel que soit l'emprunteur.
     */
    public function returnAdmin(int $loanId): bool
    {
        $loan = $this->one(
            'SELECT id, id_livre, statut FROM emprunts
             WHERE id = :id AND statut = \'en_cours\' LIMIT 1',
            [':id' => $loanId]
        );

        if ($loan === null) {
            return false;
        }

        $this->execute(
            'UPDATE livres SET nombre_exemplaire = nombre_exemplaire + 1 WHERE id = :id',
            [':id' => $loan['id_livre']]
        );

        $this->execute(
            'UPDATE emprunts
             SET statut = \'termine\', date_retour_effective = NOW()
             WHERE id = :id AND statut = \'en_cours\'',
            [':id' => $loanId]
        );

        return true;
    }

    /**
     * Emprunts actuellement en cours pour un utilisateur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function activeForUser(int $userId): array
    {
        return $this->rows(
            'SELECT e.id AS loan_id, e.date_emprunt, e.date_retour_prevue, e.statut,
                    (e.date_retour_effective IS NULL
                        AND e.date_retour_prevue < CURDATE()) AS est_retard,
                    l.id AS livre_id, l.titre, l.auteur, l.maison_edition, l.couverture
             FROM emprunts e
             INNER JOIN livres l ON l.id = e.id_livre
             WHERE e.id_utilisateur = :user AND e.statut = \'en_cours\'
             ORDER BY e.date_retour_prevue ASC',
            [':user' => $userId]
        );
    }

    /**
     * Historique des emprunts rendus pour un utilisateur.
     *
     * @return array<int, array<string, mixed>>
     */
    public function historyForUser(int $userId): array
    {
        return $this->rows(
            'SELECT e.id AS loan_id, e.date_emprunt, e.date_retour_prevue,
                    e.date_retour_effective, e.statut,
                    l.id AS livre_id, l.titre, l.auteur, l.couverture
             FROM emprunts e
             INNER JOIN livres l ON l.id = e.id_livre
             WHERE e.id_utilisateur = :user AND e.statut = \'termine\'
             ORDER BY e.date_retour_effective DESC',
            [':user' => $userId]
        );
    }

    /**
     * Emprunt en cours d'un utilisateur pour un livre donné (ou null).
     * @return array<string, mixed>|null
     */
    public function activeLoanFor(int $userId, int $bookId): ?array
    {
        return $this->one(
            'SELECT id, date_emprunt, date_retour_prevue FROM emprunts
             WHERE id_livre = :book AND id_utilisateur = :user AND statut = \'en_cours\'
             LIMIT 1',
            [':book' => $bookId, ':user' => $userId]
        );
    }

    /**
     * Nombre d'exemplaires actuellement prêtés pour un livre.
     */
    public function activeCountFor(int $bookId): int
    {
        return (int) $this->one(
            'SELECT COUNT(*) AS n FROM emprunts
             WHERE id_livre = :book AND statut = \'en_cours\'',
            [':book' => $bookId]
        )['n'];
    }

    /**
     * Vue globale : tous les emprunts (admin).
     *
     * @param string $statut Filtre optionnel : 'en_cours' | 'termine' | 'en_retard' | ''.
     * @return array<int, array<string, mixed>>
     */
    public function all(string $statut = '', string $search = ''): array
    {
        $where = 'WHERE 1 = 1';
        $params = [];

        if ($statut === 'en_retard') {
            $where .= ' AND e.statut = \'en_cours\'
                         AND e.date_retour_effective IS NULL
                         AND e.date_retour_prevue < CURDATE()';
        } elseif (in_array($statut, [self::STATUT_EN_COURS, self::STATUT_TERMINE], true)) {
            $where .= ' AND e.statut = :statut';
            $params[':statut'] = $statut;
        }

        if ($search !== '') {
            $where .= ' AND (l.titre LIKE :q OR u.nom LIKE :q2 OR u.prenom LIKE :q3)';
            $params[':q'] = '%' . $search . '%';
            $params[':q2'] = '%' . $search . '%';
            $params[':q3'] = '%' . $search . '%';
        }

        return $this->rows(
            'SELECT e.id AS loan_id, e.date_emprunt, e.date_retour_prevue,
                    e.date_retour_effective, e.statut,
                    (e.date_retour_effective IS NULL
                        AND e.date_retour_prevue < CURDATE()) AS est_retard,
                    l.id AS livre_id, l.titre AS livre_titre, l.auteur AS livre_auteur,
                    l.couverture AS livre_couverture,
                    u.id AS user_id, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email
             FROM emprunts e
             INNER JOIN livres l ON l.id = e.id_livre
             INNER JOIN utilisateurs u ON u.id = e.id_utilisateur
             ' . $where . '
             ORDER BY e.date_emprunt DESC
             LIMIT 200',
            $params
        );
    }

    /**
     * Statistiques globales (tableau de bord admin).
     *
     * @return array{ total: int, actives: int, en_retard: int, utilisateurs_actifs: int }
     */
    public function stats(): array
    {
        $total = (int) $this->one('SELECT COUNT(*) AS n FROM emprunts')['n'];

        $actives = (int) $this->one(
            'SELECT COUNT(*) AS n FROM emprunts WHERE statut = \'en_cours\''
        )['n'];

        $enRetard = (int) $this->one(
            'SELECT COUNT(*) AS n FROM emprunts
             WHERE statut = \'en_cours\'
               AND date_retour_effective IS NULL
               AND date_retour_prevue < CURDATE()'
        )['n'];

        $actifs = (int) $this->one(
            'SELECT COUNT(DISTINCT id_utilisateur) AS n FROM emprunts WHERE statut = \'en_cours\''
        )['n'];

        return [
            'total'              => $total,
            'actives'            => $actives,
            'en_retard'          => $enRetard,
            'utilisateurs_actifs'=> $actifs,
        ];
    }

    /**
     * Tendance mensuelle des emprunts sur les N derniers mois.
     * Utilisé pour alimenter le graphique en barres du dashboard admin.
     *
     * @return array<int, array{month: string, label: string, count: int}>
     */
    public function monthlyTrend(int $months = 6): array
    {
        $rows = $this->rows(
            'SELECT DATE_FORMAT(date_emprunt, \'%Y-%m\') AS month,
                    DATE_FORMAT(date_emprunt, \'%b %Y\')  AS label,
                    COUNT(*) AS cnt
             FROM emprunts
             WHERE date_emprunt >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY month, label
             ORDER BY month ASC',
            [':m' => $months]
        );

        // Garantir que tous les mois sont présents (même avec 0 emprunt).
        $result = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key   = date('Y-m', strtotime("-{$i} months"));
            $label = date('M Y', strtotime("-{$i} months"));
            $result[$key] = ['month' => $key, 'label' => $label, 'count' => 0];
        }

        foreach ($rows as $row) {
            if (isset($result[$row['month']])) {
                $result[$row['month']]['count'] = (int) $row['cnt'];
            }
        }

        return array_values($result);
    }

    /**
     * Top N livres les plus empruntés (tous statuts d'emprunt confondus).
     * Utilisé pour le classement horizontal du dashboard admin.
     *
     * @return array<int, array{livre_id: int, titre: string, auteur: string, total: int}>
     */
    public function topBorrowedBooks(int $limit = 5): array
    {
        return $this->rows(
            'SELECT l.id AS livre_id, l.titre, l.auteur, COUNT(e.id) AS total
             FROM emprunts e
             INNER JOIN livres l ON l.id = e.id_livre
             GROUP BY l.id, l.titre, l.auteur
             ORDER BY total DESC
             LIMIT :lim',
            [':lim' => $limit]
        );
    }
}