<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;

/**
 * Modèle métier des utilisateurs (table `utilisateurs`).
 *
 * - Mot de passe : haché avec password_hash() (Argon2id/bcrypt),
 *   vérifié avec password_verify().
 * - Rôles : 'lecteur' | 'admin'.
 */
final class User extends AbstractModel
{
    public const ROLE_LECTEUR = 'lecteur';
    public const ROLE_ADMIN = 'admin';

    /**
     * Tous les utilisateurs (espace admin), triés par date d'inscription.
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        return $this->rows(
            'SELECT id, nom, prenom, email, role, date_creation,
                    (SELECT COUNT(*) FROM emprunts e
                     WHERE e.id_utilisateur = utilisateurs.id
                       AND e.statut = \'en_cours\') AS emprunts_en_cours
             FROM utilisateurs
             ORDER BY date_creation ASC, nom ASC'
        );
    }

    /**
     * Fiche d'un utilisateur par identifiant.
     * @return array<string, mixed>|null
     */
    public function findById(int $id): ?array
    {
        return $this->one('SELECT * FROM utilisateurs WHERE id = :id', [':id' => $id]);
    }

    /**
     * Recherche d'un utilisateur par email (contrainte UNIQUE).
     * @return array<string, mixed>|null
     */
    public function findByEmail(string $email): ?array
    {
        return $this->one(
            'SELECT * FROM utilisateurs WHERE email = :email',
            [':email' => mb_strtolower(trim($email))]
        );
    }

    /**
     * Vérifie un couple (email, mot de passe) ; retourne l'utilisateur
     * (sans son hachage) si les identifiants sont corrects, sinon null.
     *
     * @return array<string, mixed>|null
     */
    public function attempt(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if ($user === null || !password_verify($password, (string) $user['mot_de_passe'])) {
            return null;
        }

        // Re-hachage si l'algorithme évolue (coût/bcrypt -> argon2id).
        if (password_needs_rehash((string) $user['mot_de_passe'], PASSWORD_DEFAULT)) {
            $this->setPassword((int) $user['id'], $password);
        }

        unset($user['mot_de_passe']);
        return $user;
    }

    /**
     * Crée un nouvel utilisateur (mot de passe haché).
     *
     * @param array{nom: string, prenom: string, email: string, mot_de_passe: string, role?: string} $data
     * @return bool true si créé ; false si l'email existe déjà (UNIQUE).
     */
    public function create(array $data): bool
    {
        try {
            $this->execute(
                'INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
                 VALUES (:nom, :prenom, :email, :mot_de_passe, :role)',
                [
                    ':nom'           => $data['nom'],
                    ':prenom'        => $data['prenom'],
                    ':email'         => mb_strtolower(trim($data['email'])),
                    ':mot_de_passe'  => password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
                    ':role'          => $data['role'] ?? self::ROLE_LECTEUR,
                ]
            );
            return true;
        } catch (PDOException $e) {
            // Violation de la contrainte UNIQUE sur l'email.
            return false;
        }
    }

    /**
     * Modifie le rôle d'un utilisateur (admin).
     */
    public function updateRole(int $id, string $role): bool
    {
        if (!in_array($role, [self::ROLE_LECTEUR, self::ROLE_ADMIN], true)) {
            return false;
        }

        $stmt = $this->execute(
            'UPDATE utilisateurs SET role = :role WHERE id = :id',
            [':role' => $role, ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Met à jour le mot de passe haché d'un utilisateur.
     */
    public function setPassword(int $id, string $password): bool
    {
        $stmt = $this->execute(
            'UPDATE utilisateurs SET mot_de_passe = :hash WHERE id = :id',
            [':hash' => password_hash($password, PASSWORD_DEFAULT), ':id' => $id]
        );

        return $stmt->rowCount() > 0;
    }

    /**
     * Nombre d'utilisateurs inscrits.
     */
    public function count(): int
    {
        return (int) $this->one('SELECT COUNT(*) AS n FROM utilisateurs')['n'];
    }

    /**
     * Nombre d'utilisateurs ayant au moins un emprunt en cours.
     */
    public function countActive(): int
    {
        return (int) $this->one(
            'SELECT COUNT(DISTINCT id_utilisateur) AS n
             FROM emprunts WHERE statut = \'en_cours\''
        )['n'];
    }

    /**
     * Croissance mensuelle des inscriptions sur les N derniers mois.
     * Utilisé pour le graphique de tendance du dashboard admin.
     *
     * @return array<int, array{month: string, label: string, count: int}>
     */
    public function monthlyGrowth(int $months = 6): array
    {
        $rows = $this->rows(
            'SELECT DATE_FORMAT(date_creation, \'%Y-%m\') AS month,
                    DATE_FORMAT(date_creation, \'%b %Y\')  AS label,
                    COUNT(*) AS cnt
             FROM utilisateurs
             WHERE date_creation >= DATE_SUB(CURDATE(), INTERVAL :m MONTH)
             GROUP BY month, label
             ORDER BY month ASC',
            [':m' => $months]
        );

        // Garantir la présence de chaque mois même si aucune inscription.
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
}