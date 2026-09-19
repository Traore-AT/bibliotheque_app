<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Book;
use App\Models\Review;

/**
 * Avis & notes (étoiles) sur les livres.
 *
 * - `/review/create` (POST) : dépôt ou mise à jour d'un avis
 *   (contrainte : un avis par livre et par utilisateur).
 * - `/review/delete` (POST) : suppression de SON propre avis.
 */
class ReviewController extends Controller
{
    /**
     * Dépôt / modification d'un avis (POST, CSRF, connecté).
     */
    public function createAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check(); // Protection CSRF.

        $bookId = (int) $this->post('id_livre', '0');
        $note = (int) $this->post('note', '0');
        $commentaire = $this->post('commentaire');

        $book = (new Book())->findById($bookId);

        if ($book === null) {
            $this->session->flash('error', 'Ce livre est introuvable.');
            $this->redirect(url(''));
        }

        // Validation : note de 1 à 5 étoiles, commentaire 3..1000 caractères.
        $errors = [];

        if ($note < 1 || $note > 5) {
            $errors['note'] = 'Choisissez une note entre 1 et 5 étoiles.';
        }

        $length = mb_strlen($commentaire);
        if ($length < 3) {
            $errors['commentaire'] = 'Votre commentaire est trop court (3 caractères minimum).';
        } elseif ($length > 1000) {
            $errors['commentaire'] = 'Votre commentaire ne doit pas dépasser 1000 caractères.';
        }

        if ($errors !== []) {
            $this->session->flash('error', $errors['note'] ?? $errors['commentaire']);
            $this->redirect(url('book/show', ['id' => $bookId]));
        }

        $result = (new Review())->save((int) $this->user()['id'], $bookId, $note, $commentaire);

        if ($result === 'created') {
            $this->session->flash('success', 'Votre avis a été publié. Merci !');
        } elseif ($result === 'updated') {
            $this->session->flash('success', 'Votre avis a été mis à jour.');
        } else {
            $this->session->flash('error', 'Une erreur est survenue : impossible d\'enregistrer votre avis.');
        }

        $this->redirect(url('book/show', ['id' => $bookId]));
    }

    /**
     * Suppression de son propre avis (POST, CSRF).
     */
    public function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        $reviewId = (int) $this->post('id', '0');
        $bookId = (int) $this->post('id_livre', '0');

        $deleted = (new Review())->delete($reviewId, (int) $this->user()['id']);

        if ($deleted) {
            $this->session->flash('success', 'Votre avis a été supprimé.');
        } else {
            $this->session->flash('error', 'Impossible de supprimer cet avis.');
        }

        $this->redirect($bookId > 0 ? url('book/show', ['id' => $bookId]) : url('profile'));
    }
}