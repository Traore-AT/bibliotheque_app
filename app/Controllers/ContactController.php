<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Contact;

/**
 * Contrôleur de la page Contact et interactions.
 */
class ContactController extends Controller
{
    private Contact $contactModel;

    public function __construct()
    {
        parent::__construct();
        $this->contactModel = new Contact();
    }

    /**
     * Page de contact & traitement du formulaire : `/contact` ou `?page=contact`.
     */
    public function indexAction(): void
    {
        $currentUser = $this->user();
        $errors = [];
        $data = [
            'nom'       => $currentUser ? trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) : '',
            'email'     => $currentUser ? (string) ($currentUser['email'] ?? '') : '',
            'sujet'     => '',
            'categorie' => 'Suggestion de livre',
            'message'   => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data['nom']       = $this->post('nom');
            $data['email']     = $this->post('email');
            $data['sujet']     = $this->post('sujet');
            $data['categorie'] = $this->post('categorie');
            $data['message']   = $this->post('message');

            // Validation
            if ($data['nom'] === '' || mb_strlen($data['nom']) < 2) {
                $errors['nom'] = 'Veuillez saisir votre nom et prénom (au moins 2 caractères).';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $errors['email'] = 'Veuillez saisir une adresse email valide.';
            }
            if ($data['sujet'] === '' || mb_strlen($data['sujet']) < 3) {
                $errors['sujet'] = 'Veuillez préciser l\'objet de votre message.';
            }
            if ($data['message'] === '' || mb_strlen($data['message']) < 10) {
                $errors['message'] = 'Votre message doit comporter au moins 10 caractères.';
            }

            if ($errors === []) {
                $this->contactModel->create($data);

                if ($this->isAjax()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'ok'      => true,
                        'message' => 'Votre message a été transmis avec succès ! Nous vous répondrons dans les plus brefs délais.'
                    ]);
                    exit;
                }

                $this->session->flash('success', 'Votre message a été transmis avec succès ! Nous vous répondrons dans les meilleurs délais.');
                $this->redirect(url('contact'));
            } else {
                if ($this->isAjax()) {
                    http_response_code(422);
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode([
                        'ok'     => false,
                        'errors' => $errors
                    ]);
                    exit;
                }

                $this->session->flash('error', 'Veuillez corriger les erreurs signalées dans le formulaire.');
            }
        }

        $categories = [
            'Suggestion de livre',
            'Question D-CLIC / Formation',
            'Assistance technique',
            'Partenariat & Auteur',
            'Autre demande'
        ];

        $this->render('contact/index', [
            'title'      => 'Contact & Support — Bibliothèque Numérique',
            'data'       => $data,
            'errors'     => $errors,
            'categories' => $categories,
        ]);
    }
}
