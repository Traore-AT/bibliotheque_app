<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Review;
use App\Models\User;

/**
 * Contrôleur des pages publiques : catalogue, recherche AJAX, fiche livre.
 */
class BookController extends Controller
{
    /**
     * Page d'accueil / catalogue avec pagination et recherche.
     * (Route : `/` ou `/books`)
     */
    public function indexAction(): void
    {
        $bookModel = new Book();

        $page   = max(1, (int) $this->get('pageNum', '1'));
        $search = $this->get('q', '');

        $result = $bookModel->paginated($page, BOOKS_PER_PAGE, $search);

        $this->render('books/index', [
            'books'  => $result['books'],
            'total'  => $result['total'],
            'pages'  => $result['pages'],
            'page'   => $result['page'],
            'search' => $search,
        ]);
    }

    /**
     * Recherche en temps réel (AJAX) : `/book/search`.
     * Retourne du HTML partiel consommé par fetch() côté navigateur.
     */
    public function searchAction(): void
    {
        if (!$this->isAjax()) {
            $this->redirect(url(''));
        }

        $term = $this->get('q', '');
        $books = (new Book())->search($term, 8);

        // Rendu d'une petite vue partielle uniquement.
        ob_start();
        extract(['books' => $books, 'term' => $term], EXTR_SKIP);
        require VIEWS_PATH . '/books/_search_results.php';
        $html = ob_get_clean();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['html' => $html, 'count' => count($books)]);
        exit;
    }

    /**
     * Détail d'un livre : `/book/show?id=X`.
     *
     * Charge la disponibilité (stock), la moyenne des notes, les avis,
     * ainsi que — si un lecteur est connecté — son propre avis et l'état
     * de son emprunt éventuel sur ce livre.
     */
    public function showAction(): void
    {
        $id = (int) $this->get('id', '0');
        $book = (new Book())->findById($id);

        if ($book === null) {
            $this->redirect(url(''));
        }

        $currentUser = $this->user();

        // Un livre non publié (en attente/refusé) n'est visible qu'en
        // aperçu par son auteur et par l'administration.
        $published = ($book['statut'] ?? Book::STATUT_PUBLIE) === Book::STATUT_PUBLIE;
        $isAdmin = $currentUser !== null && $currentUser['role'] === User::ROLE_ADMIN;
        $isOwner = $currentUser !== null
            && (int) ($book['id_auteur'] ?? 0) === (int) $currentUser['id'];

        if (!$published && !$isAdmin && !$isOwner) {
            $this->redirect(url(''));
        }

        $reviewModel = new Review();
        $average = $reviewModel->averageFor($id);

        $myReview = null;
        $myActiveLoan = null;

        if ($currentUser !== null) {
            $myReview = $reviewModel->findFor((int) $currentUser['id'], $id);
            $myActiveLoan = (new Loan())->activeLoanFor((int) $currentUser['id'], $id);
        }

        $this->render('books/show', [
            'book'        => $book,
            'average'     => $average,
            'reviews'     => $reviewModel->forBook($id),
            'myReview'    => $myReview,
            'myActiveLoan'=> $myActiveLoan,
            'published'   => $published,
            'isOwner'     => $isOwner,
            'isAdmin'     => $isAdmin,
            'canBorrow'   => $published
                            && $currentUser !== null
                            && (int) $book['nombre_exemplaire'] > 0
                            && $myActiveLoan === null,
        ]);
    }
}