<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Modèle Contact : gestion des messages reçus via le formulaire de contact.
 */
class Contact extends AbstractModel
{
    public function __construct()
    {
        parent::__construct();
        $this->ensureTable();
    }

    /**
     * Crée la table contacts si elle n'existe pas.
     */
    private function ensureTable(): void
    {
        $this->db->exec("
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
        ");
    }

    /**
     * Enregistre un nouveau message de contact.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        $sql = "INSERT INTO contacts (nom, email, sujet, categorie, message, lu)
                VALUES (:nom, :email, :sujet, :categorie, :message, 0)";

        $this->execute($sql, [
            'nom'       => $data['nom'],
            'email'     => $data['email'],
            'sujet'     => $data['sujet'],
            'categorie' => $data['categorie'] ?? 'Autre',
            'message'   => $data['message'],
        ]);

        return (int) $this->db->lastInsertId();
    }

    /**
     * Liste tous les messages de contact (triés du plus récent au plus ancien).
     *
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        $sql = "SELECT c.*, DATE_FORMAT(c.created_at, '%d/%m/%Y à %H:%i') as date_formatee
                FROM contacts c
                ORDER BY c.created_at DESC";
        return $this->rows($sql);
    }

    /**
     * Marque un message comme lu.
     */
    public function markAsRead(int $id): void
    {
        $this->execute("UPDATE contacts SET lu = 1 WHERE id = :id", ['id' => $id]);
    }
}
