<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Connexion unique à la base de données MySQL via PDO.
 *
 * - Singleton : une seule connexion partagée pour toute l'application.
 * - ERRMODE_EXCEPTION : chaque erreur SQL lève une PDOException.
 * - EMULATE_PREPARES=false : vraies requêtes préparées côté serveur MySQL
 *   (requêtes préparées PDO systématiques => protection injection SQL).
 */
class Database
{
    private static ?Database $instance = null;

    private PDO $pdo;

    /**
     * Constructeur privé : établit la connexion.
     */
    private function __construct()
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            http_response_code(500);
            $message = APP_ENV === 'dev'
                ? 'Échec de la connexion à la base de données : ' . $e->getMessage()
                : 'Le service est temporairement indisponible.';
            exit($message);
        }
    }

    /**
     * Instance unique de la connexion PDO.
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retourne l'objet PDO brut (pour les requêtes des modèles).
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }

    /**
     * Exécute une requête préparée et retourne le PDOStatement.
     *
     * @param array<string, mixed> $params
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Version raccourcie : retourne toutes les lignes.
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Version raccourcie : retourne une seule ligne (ou null).
     * @return array<string, mixed>|null
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    /**
     * Identifiant auto-incrémenté du dernier INSERT.
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Transaction : permet de regrouper plusieurs opérations atomiques.
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * Interdit le clonage du singleton.
     */
    private function __clone()
    {
    }
}