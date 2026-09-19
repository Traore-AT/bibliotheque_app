<?php

declare(strict_types=1);

/**
 * ============================================================
 * FICHIER DE CONFIGURATION CENTRALISE
 * ============================================================
 * Toutes les constantes de l'application (DB, chemins, sécurité).
 */

// ---- Chemins racines (normalisés avec slashes positifs) ----
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', APP_PATH . '/Views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOADS_PATH', PUBLIC_PATH . '/uploads');

// ---- Environnement : 'dev' | 'prod' ----
define('APP_ENV', 'dev');
define('APP_NAME', 'Bibliothèque Numérique');

// ---- Base de données (MySQL) ----
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'bibliotheque_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Sécurité ----
define('CSRF_TOKEN_NAME', '_csrf_token');
define('SESSION_NAME', 'bibliotheque_session');
define('SESSION_LIFETIME', 3600 * 2); // 2 heures

// ---- Application ----
define('APP_BASE_URL', ''); // chemin depuis la racine du vhost (ex : '/bibliotheque_app/public')
define('BOOKS_PER_PAGE', 10);
define('MAX_UPLOAD_SIZE', 2097152); // 2 Mo en octets

// ---- Affichage des erreurs selon l'environnement ----
if (APP_ENV === 'dev') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// ---- Encodage ----
date_default_timezone_set('Europe/Paris');
mb_internal_encoding('UTF-8');