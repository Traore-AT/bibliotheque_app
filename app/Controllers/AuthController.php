<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\User;

/**
 * Authentification (inscription, connexion, déconnexion).
 *
 * - Hachage : password_hash() / password_verify() dans User.
 * - Fixation de session : session_regenerate_id() après connexion.
 * - CSRF : toutes les actions POST.
 */
class AuthController extends Controller
{
    /**
     * Inscription : GET affiche le formulaire / POST crée le compte.
     */
    public function registerAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data = [
                'nom'    => $this->post('nom'),
                'prenom' => $this->post('prenom'),
                'email'  => mb_strtolower($this->post('email')),
                'mot_de_passe' => $this->post('mot_de_passe'),
            ];
            $confirm = $this->post('mot_de_passe_confirm');

            $errors = $this->validateRegistration($data, $confirm);

            if ($errors === []) {
                $created = (new User())->create($data);

                if ($created) {
                    // Connexion immédiate après inscription.
                    $createdUser = (new User())->attempt($data['email'], $data['mot_de_passe']);
                    if ($createdUser !== null) {
                        $this->signIn($createdUser);
                        $this->session->flash('success', 'Bienvenue ' . $createdUser['prenom'] . ' ! Votre compte a été créé.');
                        $this->redirect(url('profile'));
                    }
                }

                $errors['email'] = 'Un compte existe déjà avec cette adresse email.';
            }

            $this->session->flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->render('auth/register', [
                'errors' => $errors,
                'old'    => $data,
                'title'  => 'Créer un compte',
            ]);
            return;
        }

        $this->render('auth/register', [
            'errors' => [],
            'old'    => ['nom' => '', 'prenom' => '', 'email' => ''],
            'title'  => 'Créer un compte',
        ]);
    }

    /**
     * Connexion : GET affiche le formulaire / POST vérifie.
     */
    public function loginAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $email = mb_strtolower($this->post('email'));
            $password = $this->post('mot_de_passe');

            $user = (new User())->attempt($email, $password);

            if ($user === null) {
                $this->session->flash('error', 'Identifiants incorrects. Vérifiez votre email et votre mot de passe.');
                $this->render('auth/login', [
                    'errors' => ['identifiants' => 'Email ou mot de passe invalide.'],
                    'old'    => ['email' => $email],
                    'title'  => 'Connexion',
                ]);
                return;
            }

            $this->signIn($user);
            $this->session->flash('success', 'Bon retour parmi nous, ' . $user['prenom'] . ' !');

            $this->redirect($user['role'] === User::ROLE_ADMIN ? url('admin') : url('profile'));
        }

        $this->render('auth/login', [
            'errors' => [],
            'old'    => ['email' => ''],
            'title'  => 'Connexion',
        ]);
    }

    /**
     * Déconnexion (POST + CSRF).
     */
    public function logoutAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        Session::getInstance()->destroy();
        $this->redirect(url(''));
    }

    /**
     * Persiste la connexion : régénère l'ID de session (anti-fixation)
     * et stocke l'identité + le rôle.
     *
     * @param array<string, mixed> $user
     */
    private function signIn(array $user): void
    {
        // Régénération de l'identifiant de session.
        $this->session->regenerate();

        $this->session->set('user_id', (int) $user['id']);
        $this->session->set('user_role', $user['role']);
        $this->session->set('user_name', $user['prenom'] . ' ' . mb_strtoupper($user['nom']));
    }

    /**
     * Validation de l'inscription.
     *
     * @param array<string, string> $data
     * @return array<string, string>
     */
    private function validateRegistration(array $data, string $confirm): array
    {
        $errors = [];

        if ($data['nom'] === '') {
            $errors['nom'] = 'Le nom est obligatoire.';
        } elseif (mb_strlen($data['nom']) > 100) {
            $errors['nom'] = 'Le nom ne doit pas dépasser 100 caractères.';
        }

        if ($data['prenom'] === '') {
            $errors['prenom'] = 'Le prénom est obligatoire.';
        } elseif (mb_strlen($data['prenom']) > 100) {
            $errors['prenom'] = 'Le prénom ne doit pas dépasser 100 caractères.';
        }

        if ($data['email'] === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Adresse email invalide.';
        } elseif (mb_strlen($data['email']) > 100) {
            $errors['email'] = 'L\'adresse email ne doit pas dépasser 100 caractères.';
        }

        if (mb_strlen($data['mot_de_passe']) < 8) {
            $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }

        if ($data['mot_de_passe'] !== $confirm) {
            $errors['mot_de_passe_confirm'] = 'Les deux mots de passe ne correspondent pas.';
        }

        return $errors;
    }
}