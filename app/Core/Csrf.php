<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Génère un token lié à la session pour chaque utilisateur et
 * le vérifie sur chaque requête POST (CREATE / UPDATE / DELETE).
 */
class Csrf
{
    private const KEY = 'csrf_tokens';

    /**
     * Retourne (ou génère) le token CSRF courant pour la session.
     */
    public static function token(): string
    {
        $session = Session::getInstance();

        if (!$session->has(self::KEY)) {
            $session->set(self::KEY, bin2hex(random_bytes(32)));
        }

        return (string) $session->get(self::KEY);
    }

    /**
     * Champ caché à insérer dans les formulaires.
     */
    public static function field(): string
    {
        $name = CSRF_TOKEN_NAME;
        $token = self::token();
        return '<input type="hidden" name="' . $name . '" value="' . $token . '">';
    }

    /**
     * Vérifie que le token envoyé (POST) est valide.
     * Compare de manière à constant-time pour éviter le timing attack.
     */
    public static function validate(string $token): bool
    {
        $session = Session::getInstance();
        $stored = (string) $session->get(self::KEY, '');

        if ($stored === '') {
            return false;
        }

        return hash_equals($stored, $token);
    }

    /**
     * Alias pratique : valide le champ POST portant le nom standard.
     */
    public static function validateRequest(): bool
    {
        $token = $_POST[CSRF_TOKEN_NAME] ?? '';
        return is_string($token) && self::validate($token);
    }

    /**
     * Vérifie le token et arrête l'exécution avec une erreur 403
     * si le token est invalide (formulaire expiré / CSRF).
     */
    public static function check(): void
    {
        if (!self::validateRequest()) {
            http_response_code(403);
            exit('Erreur CSRF : jeton invalide ou expiré. Rechargez la page et réessayez.');
        }
    }
}