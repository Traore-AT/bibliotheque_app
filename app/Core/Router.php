<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Routeur central (Front Controller).
 *
 * Analyse l'URL de type `?page=controller/method` ou `/controller/method`
 * via un segment `page`, puis instancie le contrôleur et invoque la méthode.
 *
 * Convention : la méthode devient `method[Action]` au sein du contrôleur.
 */
class Router
{
    private string $controllerName;
    private string $actionName;

    /**
     * Alias de routes : segment d'URL => nom de contrôleur (camelCase).
     * Permet d'accepter `/`, `/books`, `/book/show`, etc.
     */
    private const CONTROLLER_ALIASES = [
        'books'    => 'Book',
        'book'     => 'Book',
        'wishlist' => 'Loan',
        'liste'    => 'Loan',
        'profile'  => 'Loan',
        'mon-espace' => 'Loan',
        'loans'    => 'Loan',
        'emprunts' => 'Loan',
        'review'   => 'Review',
        'avis'     => 'Review',
        'auteur'   => 'Author',
        'author'   => 'Author',
        'mes-livres' => 'Author',
        'register' => 'Auth',
        'inscription' => 'Auth',
        'login'    => 'Auth',
        'connexion'=> 'Auth',
        'logout'   => 'Auth',
        'admin'    => 'Admin',
        'blog'     => 'Blog',
        'articles' => 'Blog',
        'contact'  => 'Contact',
    ];

    /**
     * Contrôleurs dont TOUTES les actions exigent une connexion.
     * 'Admin' exige en plus le rôle 'admin'.
     */
    private const AUTH_REQUIRED = ['Loan', 'Review', 'Author'];
    private const ADMIN_ONLY = ['Admin'];

    /**
     * Middleware d'autorisation (Guest / Lecteur / Admin).
     *
     * - Actions d'authentification (login/register) : accès réservé aux invités.
     * - 'Loan', 'Review', 'Author' et le logout : connexion obligatoire.
     * - 'Admin' : rôle 'admin' obligatoire.
     *
     * Appelé par dispatch() avant l'instanciation du contrôleur.
     */
    private function checkAccess(): void
    {
        $session = Session::getInstance();
        $isLogged = $session->get('user_id') !== null;
        $role = (string) $session->get('user_role', '');

        // Pages d'authentification : uniquement si non connecté.
        if ($this->controllerName === 'Auth'
            && in_array(mb_strtolower($this->actionName), ['login', 'register'], true)) {
            if ($isLogged) {
                self::redirect($role === 'admin' ? url('admin') : url('profile'));
            }
            return;
        }

        if (in_array($this->controllerName, self::AUTH_REQUIRED, true)
            || ($this->controllerName === 'Auth' && mb_strtolower($this->actionName) === 'logout')) {
            if (!$isLogged) {
                $session->flash('error', 'Veuillez vous connecter pour accéder à cet espace.');
                self::redirect(url('login'));
            }
            return;
        }

        if (in_array($this->controllerName, self::ADMIN_ONLY, true)) {
            if (!$isLogged) {
                $session->flash('error', 'Veuillez vous connecter en tant qu\'administrateur.');
                self::redirect(url('login'));
            }
            if ($role !== 'admin') {
                $this->forbidden('Vous n\'avez pas les droits d\'accès à cette section.');
            }
        }
    }

    /**
     * Dispatch la requête vers le bon contrôleur/méthode.
     */
    public function dispatch(): void
    {
        $controllerClass = 'App\\Controllers\\' . $this->controllerName . 'Controller';
        $methodName = $this->actionName . 'Action';

        if (!class_exists($controllerClass)) {
            $this->notFound("Contrôleur introuvable : {$controllerClass}");
        }

        $this->checkAccess();

        $controller = new $controllerClass();

        if (!method_exists($controller, $methodName)) {
            $this->notFound("Méthode introuvable : {$controllerClass}::{$methodName}");
        }

        // Appel de l'action : actionIndex contient la logique du tableau de bord.
        call_user_func([$controller, $methodName]);
    }

    public function __construct()
    {
        // 1. Le paramètre explicite `?page=controller/method` prime.
        $page = (string) ($_GET['page'] ?? '');

        // 2. Sinon, on dérive la route de l'URL (PATH_INFO : /books, /book/show...).
        if ($page === '' && isset($_SERVER['PATH_INFO'])) {
            $page = ltrim($_SERVER['PATH_INFO'], '/');
        }

        // Normalisation : découpe la route "controller/action" en segments.
        $segments = array_values(array_filter(explode('/', $page), static function ($part): bool {
            return $part !== '';
        }));

        // Contrôleur : premier segment (défaut : book).
        $controller = $segments[0] ?? 'book';
        // Méthode : second segment (défaut : index).
        $action = $segments[1] ?? 'index';

        // Résolution de l'alias (sinon on transforme directement le segment).
        $this->controllerName = self::CONTROLLER_ALIASES[mb_strtolower($controller)]
            ?? $this->dashToCamel($controller);
        $this->actionName = $this->dashToCamel($action);

        // Pages d'authentification : `/login`, `/inscription`, `/logout` sont
        // des routes d'un seul segment dont l'alias désigne le contrôleur Auth.
        // On rétablit l'action correspondante (sinon elle vaudrait 'index').
        if ($this->controllerName === 'Auth') {
            $authRoutes = [
                'login'      => 'login',
                'connexion'  => 'login',
                'register'   => 'register',
                'inscription'=> 'register',
                'logout'     => 'logout',
            ];
            $key = mb_strtolower($controller);
            if (isset($authRoutes[$key])) {
                $this->actionName = ucfirst($authRoutes[$key]);
            }
        }
    }

    /**
     * Convertit "liste-lecture" en "ListeLectures".
     */
    private function dashToCamel(string $value): string
    {
        $parts = explode('-', mb_strtolower($value));
        return implode('', array_map('ucfirst', $parts));
    }

    /**
     * Redirige vers une route interne.
     */
    public static function redirect(string $path, int $status = 302): never
    {
        header('Location: ' . $path, true, $status);
        exit;
    }

    /**
     * Affichage d'une erreur 404 propre.
     */
    private function notFound(string $message): never
    {
        http_response_code(404);
        $title = 'Page introuvable';
        $message = APP_ENV === 'dev' ? $message : 'La ressource demandée n\'existe pas.';
        require VIEWS_PATH . '/layouts/errors.php';
        exit;
    }

    /**
     * Affichage d'une erreur 403 (accès refusé).
     */
    private function forbidden(string $message): never
    {
        http_response_code(403);
        $title = 'Accès refusé';
        $message = APP_ENV === 'dev' ? $message : 'Vous n\'avez pas les droits nécessaires.';
        require VIEWS_PATH . '/layouts/errors.php';
        exit;
    }
}