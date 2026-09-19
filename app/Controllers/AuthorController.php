<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CoverUploader;
use App\Core\Csrf;
use App\Models\Book;
use App\Models\User;

/**
 * Espace auteur : les utilisateurs connectés peuvent proposer leurs
 * propres livres au catalogue. Chaque soumission démarre en attente
 * de validation ; l'administrateur gère la publication ou le refus.
 */
class AuthorController extends Controller
{
    /**
     * Aperçu de mes livres : `/auteur` ou `/mes-livres`.
     */
    public function indexAction(): void
    {
        $currentUser = $this->user();
        $books = (new Book())->byAuthor((int) $currentUser['id']);

        $counts = [
            Book::STATUT_PUBLIE     => 0,
            Book::STATUT_EN_ATTENTE => 0,
            Book::STATUT_REFUSE     => 0,
        ];

        foreach ($books as $book) {
            if (isset($counts[$book['statut']])) {
                $counts[$book['statut']]++;
            }
        }

        $this->render('author/index', [
            'books'  => $books,
            'counts' => $counts,
            'title'  => 'Espace auteur',
        ]);
    }

    /**
     * Formulaire de dépôt d'un livre : `/auteur/create`.
     */
    public function createAction(): void
    {
        $currentUser = $this->user();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data = $this->collect();
            $errors = $this->validate($data);

            $upload = (new CoverUploader())->process($_FILES['couverture'] ?? []);

            if ($upload['ok']) {
                $data['couverture'] = $upload['name'];
            } elseif ($upload['error'] !== null) {
                $errors['couverture'] = $upload['error'];
            }

            if ($errors === []) {
                (new Book())->create($data, (int) $currentUser['id'], Book::STATUT_EN_ATTENTE);
                $this->session->flash('success', 'Votre livre « ' . $data['titre'] . ' » a été soumis. Il sera visible dès validation par l\'administrateur.');
                $this->redirect(url('auteur'));
            }

            $this->session->flash('error', 'Veuillez corriger les erreurs du formulaire.');

            if ($upload['ok']) {
                (new CoverUploader())->delete($data['couverture']);
                unset($data['couverture']);
            }

            $this->render('author/form', [
                'mode'   => 'create',
                'book'   => $data,
                'errors' => $errors,
                'title'  => 'Publier mon livre',
            ]);
            return;
        }

        $this->render('author/form', [
            'mode'   => 'create',
            'book'   => [
                'titre'            => '',
                'auteur'           => $currentUser['prenom'] . ' ' . mb_strtoupper((string) $currentUser['nom']),
                'description'      => '',
                'maison_edition'   => '',
                'nombre_exemplaire'=> 1,
            ],
            'errors' => [],
            'title'  => 'Publier mon livre',
        ]);
    }

    /**
     * Modification d'un livre : `/auteur/edit?id=X`.
     */
    public function editAction(): void
    {
        $id = (int) $this->get('id', '0');
        $bookModel = new Book();
        $book = $bookModel->findById($id);

        if ($book === null || !$this->canManage($book)) {
            $this->session->flash('error', 'Ce livre ne vous appartient pas.');
            $this->redirect(url('auteur'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data = $this->collect();
            $data['couverture'] = $book['couverture'] ?? null;

            $errors = $this->validate($data);

            $oldCover = $book['couverture'] ?? null;
            $upload = (new CoverUploader())->process($_FILES['couverture'] ?? []);

            if ($upload['ok']) {
                $data['couverture'] = $upload['name'];
            } elseif ($upload['error'] !== null) {
                $errors['couverture'] = $upload['error'];
            } elseif (!empty($_POST['supprimer_couverture'])) {
                $data['couverture'] = null;
            }

            if ($errors === []) {
                $bookModel->update($id, $data);

                $uploader = new CoverUploader();

                if ($data['couverture'] !== $oldCover) {
                    $uploader->delete($oldCover);
                }

                // Un livre refusé est automatiquement resoumis.
                if ($book['statut'] === Book::STATUT_REFUSE) {
                    $bookModel->setStatus($id, Book::STATUT_EN_ATTENTE);
                }

                $this->session->flash('success', 'Votre livre a été mis à jour.');
                $this->redirect(url('auteur'));
            }

            $this->session->flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->render('author/form', [
                'mode'   => 'edit',
                'book'   => $data,
                'errors' => $errors,
                'title'  => 'Modifier mon livre',
            ]);
            return;
        }

        $this->render('author/form', [
            'mode'   => 'edit',
            'book'   => $book,
            'errors' => [],
            'title'  => 'Modifier mon livre',
        ]);
    }

    /**
     * Suppression d'un livre : `/auteur/delete` (POST).
     */
    public function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        $id = (int) $this->post('id', '0');
        $book = (new Book())->findById($id);

        if ($book === null || !$this->canManage($book)) {
            $this->session->flash('error', 'Ce livre ne vous appartient pas.');
            $this->redirect(url('auteur'));
        }

        (new Book())->delete($id);
        (new CoverUploader())->delete($book['couverture'] ?? null);

        $this->session->flash('success', 'Votre livre a été supprimé.');
        $this->redirect(url('auteur'));
    }

    /**
     * L'auteur propriétaire (ou un admin) peut gérer un livre.
     *
     * @param array<string, mixed> $book
     */
    private function canManage(array $book): bool
    {
        $currentUser = $this->user();

        if ($currentUser === null) {
            return false;
        }

        $ownerId = (int) ($book['id_auteur'] ?? 0);

        return $ownerId === (int) $currentUser['id']
            || $currentUser['role'] === User::ROLE_ADMIN;
    }

    /**
     * Collecte et nettoie les champs du formulaire.
     *
     * @return array<string, string|int>
     */
    private function collect(): array
    {
        return [
            'titre'            => $this->post('titre'),
            'auteur'           => $this->post('auteur'),
            'description'      => $this->post('description'),
            'maison_edition'   => $this->post('maison_edition'),
            'nombre_exemplaire'=> max(0, (int) $this->post('nombre_exemplaire', '1')),
        ];
    }

    /**
     * Validation des données d'un livre.
     *
     * @param array<string, mixed> $data
     * @return array<string, string>
     */
    private function validate(array $data): array
    {
        $errors = [];

        if ($data['titre'] === '') {
            $errors['titre'] = 'Le titre est obligatoire.';
        } elseif (mb_strlen($data['titre'] ?? '') > 100) {
            $errors['titre'] = 'Le titre ne doit pas dépasser 100 caractères.';
        }

        if ($data['auteur'] === '') {
            $errors['auteur'] = 'L\'auteur est obligatoire.';
        } elseif (mb_strlen($data['auteur'] ?? '') > 100) {
            $errors['auteur'] = 'L\'auteur ne doit pas dépasser 100 caractères.';
        }

        if ($data['description'] === '') {
            $errors['description'] = 'La description est obligatoire.';
        }

        if ($data['maison_edition'] === '') {
            $errors['maison_edition'] = 'La maison d\'édition est obligatoire.';
        } elseif (mb_strlen($data['maison_edition'] ?? '') > 100) {
            $errors['maison_edition'] = 'La maison d\'édition ne doit pas dépasser 100 caractères.';
        }

        return $errors;
    }
}