<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Classe de base de tous les contrôleurs.
 *
 * Fournit l'accès à la session (messages flash) et le rendu des vues
 * à l'intérieur du layout global.
 */
abstract class Controller
{
    protected Session $session;
    protected Database $db;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->db = Database::getInstance();
    }

    /**
     * Rendu d'une vue : exécute la vue puis l'affiche dans le layout.
     *
     * @param array<string, mixed> $data Variables extraites pour la vue.
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        $flashMessages = $this->session->getFlashes();
        $currentPage = $view;
        $currentUser = $this->session->get('user_id') !== null ? $this->user() : null;

        ob_start();
        require VIEWS_PATH . '/' . $view . '.php';
        $content = ob_get_clean();

        require VIEWS_PATH . '/layouts/main.php';
    }

    /**
     * Utilisateur actuellement connecté (rechargé frais depuis la base).
     *
     * @return array<string, mixed>|null
     */
    protected function user(): ?array
    {
        $id = (int) $this->session->get('user_id', 0);

        if ($id <= 0) {
            return null;
        }

        $user = (new \App\Models\User())->findById($id);

        if ($user === null) {
            // Utilisateur supprimé entre-temps : purge de la session.
            $this->session->remove('user_id');
            $this->session->remove('user_role');
            return null;
        }

        unset($user['mot_de_passe']);
        return $user;
    }

    /**
     * Rôle de l'utilisateur connecté ('' si invité).
     */
    protected function role(): string
    {
        return (string) $this->session->get('user_role', '');
    }

    /**
     * true si le visiteur est connecté.
     */
    protected function isGuest(): bool
    {
        return $this->session->get('user_id') === null;
    }

    /**
     * Redirection courte vers une route (avec messages flash conservés).
     */
    protected function redirect(string $path, int $status = 302): never
    {
        Router::redirect($path, $status);
    }

    /**
     * Lit une valeur POST nettoyée (trim), ou une valeur par défaut.
     */
    protected function post(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /**
     * Lit une valeur GET nettoyée, ou une valeur par défaut.
     */
    protected function get(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? $default;
        return is_string($value) ? trim($value) : $default;
    }

    /**
     * Indique si la requête est une requête AJAX (Fetch API / XHR).
     */
    protected function isAjax(): bool
    {
        return strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
            || strtolower($_SERVER['HTTP_ACCEPT'] ?? '') === 'application/json';
    }
}