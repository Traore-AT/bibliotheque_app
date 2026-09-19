<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use PDO;

/**
 * Modèle de base : chaque modèle métier partage la connexion PDO.
 */
abstract class AbstractModel
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->pdo();
    }

    /**
     * Prépare et exécute une requête préparée (injection SQL : jamais de
     * concaténation de valeurs brutes dans les requêtes).
     *
     * @param array<string, mixed> $params
     */
    protected function execute(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Retourne toutes les lignes d'une requête préparée.
     * @return array<int, array<string, mixed>>
     */
    protected function rows(string $sql, array $params = []): array
    {
        return $this->execute($sql, $params)->fetchAll();
    }

    /**
     * Retourne une seule ligne (ou null).
     * @return array<string, mixed>|null
     */
    protected function one(string $sql, array $params = []): ?array
    {
        $row = $this->execute($sql, $params)->fetch();
        return $row === false ? null : $row;
    }
}