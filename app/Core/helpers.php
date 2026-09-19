<?php

declare(strict_types=1);

/**
 * Fonctions utilitaires globales de l'application.
 */

if (!function_exists('e')) {
    /**
     * Échappe une valeur pour un rendu HTML sûr (protection XSS).
     * À utiliser systématiquement dans toutes les vues.
     */
    function e(?string $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Raccourci d'affichage du champ CSRF dans un formulaire.
     */
    function csrf_field(): string
    {
        return \App\Core\Csrf::field();
    }
}

if (!function_exists('url')) {
    /**
     * Construit une URL interne propre (avec paramètre 'page').
     */
    function url(string $path = '', array $query = []): string
    {
        $base = rtrim(APP_BASE_URL, '/');
        $url = ($path === '' || $path === '/')
            ? $base . '/index.php'
            : $base . '/index.php?page=' . ltrim($path, '/');

        if ($query !== []) {
            $url .= '&' . http_build_query($query);
        }

        return $url;
    }
}

if (!function_exists('redirect')) {
    /**
     * Redirection rapide depuis les vues (rare, mais pratique).
     */
    function redirect(string $path): never
    {
        \App\Core\Router::redirect($path);
    }
}