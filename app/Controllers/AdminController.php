<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CoverUploader;
use App\Core\Csrf;
use App\Core\Session;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Review;
use App\Models\User;

/**
 * Panneau d'administration (accès réservé au rôle 'admin' par le Router).
 *
 * - Tableau de bord : statistiques globales + CRUD des livres.
 * - Gestion des utilisateurs (consultation, changement de rôle).
 * - Gestion globale des emprunts (vue d'ensemble, retour manuel).
 */
class AdminController extends Controller
{
    /**
     * Tableau de bord : `/admin`.
     */
    public function indexAction(): void
    {
        $bookModel = new Book();

        $page   = max(1, (int) $this->get('pageNum', '1'));
        $search = $this->get('q', '');

        $result = $bookModel->paginated($page, BOOKS_PER_PAGE, $search, '');
        $loanStats = (new Loan())->stats();

        $stats = [
            'livres'          => (int) $bookModel->count(),
            'utilisateurs'    => (int) (new User())->count(),
            'emprunts_total'  => (int) $loanStats['total'],
            'emprunts_actifs' => (int) $loanStats['actives'],
            'retards'         => (int) $loanStats['en_retard'],
            'utilisateurs_actifs' => (int) $loanStats['utilisateurs_actifs'],
            'avis'            => (int) (new Review())->count(),
            'en_attente'      => (int) $bookModel->countByStatus(Book::STATUT_EN_ATTENTE),
        ];

        $this->render('admin/index', [
            'books'      => $result['books'],
            'totalBooks' => $stats['livres'],
            'stats'      => $stats,
            'totalPages' => $result['pages'],
            'page'       => $result['page'],
            'search'     => $search,
        ]);
    }

    /**
     * Gestion des utilisateurs : `/admin/users`.
     */
    public function usersAction(): void
    {
        $users = (new User())->all();

        $this->render('admin/users', [
            'users'       => $users,
            'totalUsers'  => count($users),
            'currentUser' => $this->user(),
            'title'       => 'Utilisateurs',
        ]);
    }

    /**
     * Modification du rôle d'un utilisateur (POST) : `/admin/user-role`.
     */
    public function roleAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        $userId = (int) $this->post('id', '0');
        $role = $this->post('role', '');

        // Interdiction de se dégrader soi-même depuis le dernier admin :
        // si l'utilisateur est le seul admin et qu'on le passe en lecteur.
        $target = (new User())->findById($userId);

        if ($target === null) {
            $this->session->flash('error', 'Utilisateur introuvable.');
            $this->redirect(url('admin/users'));
        }

        $admins = (new User())->all();
        $adminCount = count(array_filter($admins, static fn ($u) => $u['role'] === User::ROLE_ADMIN));

        $degradeDernierAdmin = $target['role'] === User::ROLE_ADMIN
            && $role !== User::ROLE_ADMIN
            && $adminCount <= 1;

        if ($degradeDernierAdmin) {
            $this->session->flash('error', 'Impossible : il doit rester au moins un administrateur.');
            $this->redirect(url('admin/users'));
        }

        if ((new User())->updateRole($userId, $role)) {
            $this->session->flash('success', 'Le rôle de ' . $target['prenom'] . ' ' . $target['nom'] . ' a été mis à jour.');
        } else {
            $this->session->flash('error', 'Rôle invalide.');
        }

        $this->redirect(url('admin/users'));
    }

    /**
     * Vue globale des emprunts : `/admin/loans`.
     */
    public function loansAction(): void
    {
        $statut = $this->get('statut', '');
        $search = $this->get('q', '');

        $loans = (new Loan())->all($statut, $search);
        $loanStats = (new Loan())->stats();

        $this->render('admin/loans', [
            'loans'   => $loans,
            'stats'   => $loanStats,
            'statut'  => $statut,
            'search'  => $search,
            'title'   => 'Emprunts',
        ]);
    }

    /**
     * Modération de la bibliothèque (espace auteur) : `/admin/books`.
     * L'admin publie/refuse les livres soumis par les utilisateurs.
     */
    public function booksAction(): void
    {
        $bookModel = new Book();

        $statut = $this->get('statut', '');
        $search = $this->get('q', '');
        $page   = max(1, (int) $this->get('pageNum', '1'));

        $result = $bookModel->paginated($page, 20, $search, $statut);

        $counts = [
            ''            => (int) $bookModel->count(),
            Book::STATUT_EN_ATTENTE => (int) $bookModel->countByStatus(Book::STATUT_EN_ATTENTE),
            Book::STATUT_PUBLIE     => (int) $bookModel->countByStatus(Book::STATUT_PUBLIE),
            Book::STATUT_REFUSE     => (int) $bookModel->countByStatus(Book::STATUT_REFUSE),
        ];

        $this->render('admin/books', [
            'books'      => $result['books'],
            'statut'     => $statut,
            'search'     => $search,
            'counts'     => $counts,
            'totalPages' => $result['pages'],
            'page'       => $result['page'],
            'title'      => 'Modération des livres',
        ]);
    }

    /**
     * Changement du statut d'un livre (publication / refus) : `/admin/status` (POST).
     */
    public function statusAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        $id = (int) $this->post('id', '0');
        $statut = $this->post('statut', '');

        $allowed = [Book::STATUT_EN_ATTENTE, Book::STATUT_PUBLIE, Book::STATUT_REFUSE];

        if (!in_array($statut, $allowed, true)) {
            $this->session->flash('error', 'Statut invalide.');
            $this->redirect(url('admin/books'));
        }

        if ((new Book())->setStatus($id, $statut)) {
            if ($statut === Book::STATUT_PUBLIE) {
                $this->session->flash('success', 'Le livre est désormais publié dans le catalogue.');
            } elseif ($statut === Book::STATUT_REFUSE) {
                $this->session->flash('success', 'Le livre a été refusé : l\'auteur en est notifié par son espace.');
            } else {
                $this->session->flash('success', 'Le livre est de nouveau en attente de validation.');
            }
        } else {
            $this->session->flash('error', 'Impossible de modifier ce livre.');
        }

        // Conservation des filtres courants de la modération.
        $this->redirect(url('admin/books', array_filter([
            'statut' => $this->post('filtre_statut'),
            'q'      => $this->post('filtre_q'),
        ])));
    }

    /**
     * Retour manuel d'un emprunt par l'administration (POST) :
     * `/admin/loan-return`.
     */
    public function loanReturnAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check();

        $loanId = (int) $this->post('id', '0');
        $returned = (new Loan())->returnAdmin($loanId);

        if ($returned) {
            $this->session->flash('success', 'Emprunt clôturé : le livre est de nouveau disponible.');
        } else {
            $this->session->flash('error', 'Impossible de clôturer cet emprunt (déjà rendu ou introuvable).');
        }

        $this->redirect(url('admin/loans'));
    }

    /**
     * Formulaire d'ajout d'un livre (GET affiche / POST crée).
     */
    public function createAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data = [
                'titre'            => $this->post('titre'),
                'auteur'           => $this->post('auteur'),
                'description'      => $this->post('description'),
                'maison_edition'   => $this->post('maison_edition'),
                'nombre_exemplaire'=> max(0, (int) $this->post('nombre_exemplaire', '1')),
            ];

            $errors = $this->validate($data);

            $upload = (new CoverUploader())->process($_FILES['couverture'] ?? []);

            if ($upload['ok']) {
                $data['couverture'] = $upload['name'];
            } elseif ($upload['error'] !== null) {
                $errors['couverture'] = $upload['error'];
            }

            if ($errors === []) {
                (new Book())->create($data);
                $this->session->flash('success', 'Le livre "' . $data['titre'] . '" a été ajouté.');
                $this->redirect(url('admin'));
            }

            $this->session->flash('error', 'Veuillez corriger les erreurs du formulaire.');

            if ($upload['ok']) {
                (new CoverUploader())->delete($data['couverture']);
                unset($data['couverture']);
            }

            $this->render('admin/form', [
                'mode'   => 'create',
                'book'   => $data,
                'errors' => $errors,
                'title'  => 'Ajouter un livre',
            ]);
            return;
        }

        $this->render('admin/form', [
            'mode'   => 'create',
            'book'   => ['titre' => '', 'auteur' => '', 'description' => '', 'maison_edition' => '', 'nombre_exemplaire' => 1],
            'errors' => [],
            'title'  => 'Ajouter un livre',
        ]);
    }

    /**
     * Formulaire d'édition d'un livre (GET affiche / POST enregistre).
     */
    public function editAction(): void
    {
        $id = (int) $this->get('id', '0');
        $bookModel = new Book();
        $book = $bookModel->findById($id);

        if ($book === null) {
            $this->redirect(url('admin'));
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data = [
                'titre'            => $this->post('titre'),
                'auteur'           => $this->post('auteur'),
                'description'      => $this->post('description'),
                'maison_edition'   => $this->post('maison_edition'),
                'nombre_exemplaire'=> max(0, (int) $this->post('nombre_exemplaire', '1')),
                'couverture'       => $book['couverture'] ?? null,
            ];

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

                $this->session->flash('success', 'Le livre a été modifié.');
                $this->redirect(url('admin'));
            }

            $this->session->flash('error', 'Veuillez corriger les erreurs du formulaire.');
            $this->render('admin/form', [
                'mode'   => 'edit',
                'book'   => $data,
                'errors' => $errors,
                'title'  => 'Modifier le livre',
            ]);
            return;
        }

        $this->render('admin/form', [
            'mode'   => 'edit',
            'book'   => $book,
            'errors' => [],
            'title'  => 'Modifier le livre',
        ]);
    }

    /**
     * Suppression d'un livre (POST) : `/admin/delete` avec id.
     */
    public function deleteAction(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Méthode non autorisée.');
        }

        Csrf::check(); // Protection CSRF.

        $id = (int) $this->post('id', '0');
        $book = (new Book())->findById($id);
        $deleted = $book === null ? false : (new Book())->delete($id);

        if ($deleted) {
            (new CoverUploader())->delete($book['couverture'] ?? null);
            $this->session->flash('success', 'Le livre a bien été supprimé.');
        } else {
            $this->session->flash('error', 'Impossible de supprimer ce livre.');
        }

        $this->redirect(url('admin'));
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