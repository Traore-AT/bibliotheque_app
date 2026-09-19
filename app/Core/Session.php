<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Gestion centralisée des sessions PHP et des messages flash.
 *
 * Fournit également une protection basique contre le détournement
 * de session (fixation) en régénérant l'identifiant.
 */
class Session
{
    private static ?Session $instance = null;

    /**
     * Constructeur privé : démarre la session une seule fois.
     */
    private function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_name(SESSION_NAME);
            session_set_cookie_params([
                'lifetime' => SESSION_LIFETIME,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            ]);
            session_start();
        }
    }

    /**
     * Instance unique (Singleton).
     */
    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Définit une valeur de session.
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Récupère une valeur de session, avec un défaut optionnel.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Vérifie la présence d'une clé.
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Supprime une clé de session.
     */
    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Ajoute un message flash.
     * @param 'success'|'error'|'info'|'warning' $type
     */
    public function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type][] = $message;
    }

    /**
     * Récupère (et purge) tous les messages flash.
     * @return array<string, array<int, string>>
     */
    public function getFlashes(): array
    {
        $flashes = $_SESSION['flash'] ?? [];
        unset($_SESSION['flash']);
        return $flashes;
    }

    /**
     * Tableau des messages flash pour un type donné, sans purge globale.
     * @return array<int, string>
     */
    public function flushType(string $type): array
    {
        $messages = $_SESSION['flash'][$type] ?? [];
        unset($_SESSION['flash'][$type]);
        return $messages;
    }

    /**
     * Détruit la session (déconnexion complète).
     */
    public function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
    }

    /**
     * Régénère l'identifiant de session (à appeler après connexion
     * pour éviter la fixation de session).
     */
    public function regenerate(): void
    {
        session_regenerate_id(true);
    }
}