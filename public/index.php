<?php

declare(strict_types=1);

/**
 * ============================================================
 * POINT D'ENTREE UNIQUE (FRONT CONTROLLER)
 * Toutes les requêtes HTTP passent par ce fichier.
 * ============================================================
 */

// Configuration + Autoloader PSR-4 (App\ => /app).
require_once dirname(__DIR__) . '/autoload.php';

// Démarrage de la session (Singleton).
\App\Core\Session::getInstance();

// Création et exécution du routeur central.
$router = new \App\Core\Router();
$router->dispatch();