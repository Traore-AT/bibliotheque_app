<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\CoverUploader;
use App\Core\Csrf;
use App\Models\Blog;

/**
 * Contrôleur du Blog et de l'Hommage D-CLIC.
 */
class BlogController extends Controller
{
    private Blog $blogModel;

    public function __construct()
    {
        parent::__construct();
        $this->blogModel = new Blog();
    }

    /**
     * Page principale du Blog : `/blog` ou `?page=blog`.
     */
    public function indexAction(): void
    {
        $selectedCat = $this->get('categorie', 'tous');
        $searchQuery = $this->get('q', '');

        $articles = $this->blogModel->all(
            $selectedCat !== 'tous' ? $selectedCat : null,
            $searchQuery !== '' ? $searchQuery : null
        );

        $categories = $this->blogModel->getCategories();
        $featured = $this->blogModel->getFeatured();

        $this->render('blog/index', [
            'title'          => 'Blog & Actualités Littéraires',
            'articles'       => $articles,
            'categories'     => $categories,
            'selectedCat'    => $selectedCat,
            'searchQuery'    => $searchQuery,
            'featured'       => $featured,
        ]);
    }

    /**
     * Détail d'un article : `/blog/show?id=1` ou `?page=blog/show&id=1` (ou via slug).
     */
    public function showAction(): void
    {
        $id = (int) $this->get('id', '0');
        $slug = $this->get('slug', '');

        $article = null;
        if ($id > 0) {
            $article = $this->blogModel->findById($id);
        } elseif ($slug !== '') {
            $article = $this->blogModel->findBySlug($slug);
        }

        if ($article === null) {
            $this->session->flash('error', 'L\'article demandé n\'existe pas ou a été retiré.');
            $this->redirect(url('blog'));
        }

        // Incrémentation des vues
        $this->blogModel->incrementViews((int) $article['id']);

        // Articles similaires / récents
        $related = $this->blogModel->getRelated((int) $article['id'], (string) $article['categorie']);

        $this->render('blog/show', [
            'title'   => $article['titre'],
            'article' => $article,
            'related' => $related,
        ]);
    }

    /**
     * Publication d'un nouvel article (avec photo) : `/blog/create`.
     */
    public function createAction(): void
    {
        $currentUser = $this->user();
        $errors = [];
        $data = [
            'titre'      => '',
            'auteur_nom' => $currentUser ? trim(($currentUser['prenom'] ?? '') . ' ' . ($currentUser['nom'] ?? '')) : '',
            'categorie'  => 'Actualités',
            'chapeau'    => '',
            'contenu'    => '',
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Csrf::check();

            $data['titre']      = $this->post('titre');
            $data['auteur_nom'] = $this->post('auteur_nom');
            $data['categorie']  = $this->post('categorie');
            $data['chapeau']    = $this->post('chapeau');
            $data['contenu']    = $this->post('contenu');
            $data['epingle']    = $currentUser && ($currentUser['role'] ?? '') === 'admin' && isset($_POST['epingle']);

            // Validation
            if ($data['titre'] === '' || mb_strlen($data['titre']) < 5) {
                $errors['titre'] = 'Le titre doit comporter au moins 5 caractères.';
            }
            if ($data['auteur_nom'] === '') {
                $errors['auteur_nom'] = 'Veuillez renseigner le nom de l\'auteur.';
            }
            if ($data['chapeau'] === '' || mb_strlen($data['chapeau']) < 10) {
                $errors['chapeau'] = 'Le résumé introductif doit comporter au moins 10 caractères.';
            }
            if ($data['contenu'] === '' || mb_strlen($data['contenu']) < 30) {
                $errors['contenu'] = 'Le contenu de l\'article doit comporter au moins 30 caractères.';
            }

            // Gestion de la photo / image de couverture de l'article
            $upload = (new CoverUploader())->process($_FILES['photo'] ?? []);
            if ($upload['ok']) {
                $data['image'] = $upload['name'];
            } elseif ($upload['error'] !== null) {
                $errors['photo'] = $upload['error'];
            }

            if ($errors === []) {
                $userId = $currentUser ? (int) $currentUser['id'] : null;
                $newId = $this->blogModel->create($data, $userId);

                $this->session->flash('success', 'Votre article a été publié avec succès !');
                $this->redirect(url('blog/show', ['id' => $newId]));
            } else {
                $this->session->flash('error', 'Veuillez vérifier les informations saisies.');
                if (isset($data['image'])) {
                    (new CoverUploader())->delete($data['image']);
                    unset($data['image']);
                }
            }
        }

        $categories = ['Actualités', 'Formation D-CLIC', 'Culture & Numérique', 'Tech & Architecture', 'Coup de Cœur', 'Tutoriels & Astuces'];

        $this->render('blog/create', [
            'title'      => 'Publier un article de blog',
            'data'       => $data,
            'errors'     => $errors,
            'categories' => $categories,
        ]);
    }
}
