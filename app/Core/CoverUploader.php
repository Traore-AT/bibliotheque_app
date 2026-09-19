<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gestion sécurisée des couvertures de livres.
 */
final class CoverUploader
{
    public const MIME_JPEG = 'image/jpeg';
    public const MIME_PNG  = 'image/png';
    public const MIME_GIF  = 'image/gif';
    public const MIME_WEBP = 'image/webp';

    private const MIME_EXT = [
        self::MIME_JPEG => 'jpg',
        self::MIME_PNG  => 'png',
        self::MIME_GIF  => 'gif',
        self::MIME_WEBP => 'webp',
    ];

    /**
     * Traite un fichier uploadé.
     *
     * @return array{ok: bool, name: string|null, error: string|null}
     */
    public function process(array $file): array
    {
        if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['ok' => false, 'name' => null, 'error' => null];
        }

        if (!is_int($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            return $this->fail('Erreur lors de l\'envoi du fichier.');
        }

        if ($file['size'] > MAX_UPLOAD_SIZE) {
            $max = round(MAX_UPLOAD_SIZE / 1048576, 1);
            return $this->fail('Le fichier dépasse ' . $max . ' Mo.');
        }

        if (!is_uploaded_file($file['tmp_name'])) {
            return $this->fail('Fichier non autorisé.');
        }

        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        if (!isset(self::MIME_EXT[$mime])) {
            return $this->fail('Format non accepté. Utilisez JPG, PNG, GIF ou WebP.');
        }

        $ext = self::MIME_EXT[$mime];
        $name = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!is_dir(UPLOADS_PATH) && !mkdir(UPLOADS_PATH, 0755, true)) {
            return $this->fail('Impossible de créer le dossier de stockage.');
        }

        $dest = UPLOADS_PATH . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return $this->fail('Impossible d\'enregistrer l\'image.');
        }

        return ['ok' => true, 'name' => $name, 'error' => null];
    }

    /**
     * Supprime un fichier de couverture existant.
     */
    public function delete(?string $filename): void
    {
        if ($filename === null || $filename === '') {
            return;
        }

        $path = UPLOADS_PATH . '/' . basename($filename);

        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Construit l'URL publique d'un fichier de couverture.
     */
    public static function url(?string $filename): string
    {
        if ($filename === null || $filename === '') {
            return '';
        }

        $safe = basename($filename);

        if (!is_file(UPLOADS_PATH . '/' . $safe)) {
            return '';
        }

        return APP_BASE_URL . '/uploads/' . $safe;
    }

    private function fail(string $error): array
    {
        return ['ok' => false, 'name' => null, 'error' => $error];
    }
}
