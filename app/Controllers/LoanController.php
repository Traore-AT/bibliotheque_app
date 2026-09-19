<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Csrf;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Review;
use App\Models\User;

/**
 * Emprunts et espace personnel du lecteur.
 *
 * - `/profile` (index) : emprunts en cours, historique, mes avis.
 * - `/loan/borrow` (POST) : emprunt avec verrouillage de disponibilité.
 * - `/loan/return` (POST) : restitution d'un livre personnel.
 */
class LoanController extends Controller
{
    /**
     * Espace personnel : `/profile` (alias : /my-loans, /mon-espace).
     */
    public function indexAction(): void
    {
        $user = $this->user();
        $loanModel = new Loan();
        $reviewModel = new Review();

        $activeLoans = $loanModel->activeForUser((int) $user['id']);
        $history = $loanModel->historyForUser((int) $user['id']);
        $myReviews = $reviewModel->forUser((int) $user['id']);

        $this->render('profile/index', [
            'user'        => $user,
            'activeLoans' => $activeLoans,
            'history'     => $history,
            'myReviews'   => $myReviews,
            'title'       => 'Mon espace',
        ]);
    }

    /**
     * Emprunt d'un livre : `/loan/borrow` (POST, CSRF).
     * La règle d'exclusivité est appliquée dans Loan::borrow()
     * (transaction + décrément atomique du stock).
     */
    public function borrowAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check(); // Protection CSRF.

        $bookId = (int) $this->post('id_livre', '0');
        $book = (new Book())->findById($bookId);

        if ($book === null) {
            $this->session->flash('error', 'Ce livre est introuvable.');
            $this->redirect(url(''));
        }

        $result = (new Loan())->borrow((int) $this->user()['id'], $bookId);

        if ($result['ok']) {
            $this->session->flash('success', $result['message']);
        } else {
            $this->session->flash('warning', $result['message']);
        }

        $this->redirect(url('book/show', ['id' => $bookId]));
    }

    /**
     * Restitution d'un emprunt personnel : `/loan/return` (POST, CSRF).
     */
    public function returnAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check(); // Protection CSRF.

        $loanId = (int) $this->post('id', '0');
        $returned = (new Loan())->returnOwnBorrow($loanId, (int) $this->user()['id']);

        $returned
            ? $this->session->flash('success', 'Merci ! Le livre est remis à disposition.')
            : $this->session->flash('error', 'Impossible de restituer cet emprunt (emprunt introuvable ou déjà rendu).');

        $this->redirect(url('profile'));
    }
}