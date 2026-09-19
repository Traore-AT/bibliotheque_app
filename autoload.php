<?php

declare(strict_types=1);

/**
 * Autoloader PSR-4 maison.
 *
 * Mappe le namespace `App\` vers le dossier `/app`.
 * Fonctionne sans Composer ; si Composer est installé, il peut
 * parfaitement être remplacé par `vendor/autoload.php`.
 */
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/app/';

    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

// Chargement de la configuration centralisée.
require_once __DIR__ . '/config/config.php';

// Fonctions utilitaires globales (e, csrf_field, url, ...).
require_once __DIR__ . '/app/Core/helpers.php';