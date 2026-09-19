<?php /** Vue : tableau de bord d'administration (CRUD + panneau latéral) */ ?>

<section class="container page-inner">
    <div class="page-head reveal">
        <div>
            <h1>Administration</h1>
            <p class="head-meta">Gérez le catalogue : ajout, modification, suppression.</p>
        </div>
        <button type="button" class="btn btn--brand btn--lg" data-open-create>
            <svg class="icon"><use href="#i-plus"/></svg> Ajouter un livre
        </button>
    </div>

    <!-- ---- Statistiques ---- -->
    <div class="stats reveal">
        <div class="stat">
            <span class="stat-icon stat-icon--teal"><svg class="icon"><use href="#i-library"/></svg></span>
            <div>
                <p class="stat-num"><?= number_format($stats['livres']) ?></p>
                <p class="stat-label">Livres au catalogue</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon stat-icon--blue"><svg class="icon"><use href="#i-users"/></svg></span>
            <div>
                <p class="stat-num"><?= number_format($stats['utilisateurs']) ?></p>
                <p class="stat-label">Utilisateurs inscrits</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon stat-icon--amber"><svg class="icon"><use href="#i-clock"/></svg></span>
            <div>
                <p class="stat-num"><?= number_format($stats['emprunts_actifs']) ?></p>
                <p class="stat-label">Emprunts en cours</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon <?= $stats['retards'] > 0 ? 'stat-icon--red' : 'stat-icon--teal' ?>">
                <svg class="icon"><use href="#i-flag"/></svg>
            </span>
            <div>
                <p class="stat-num"><?= number_format($stats['retards']) ?></p>
                <p class="stat-label">Emprunts en retard</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon stat-icon--amber"><svg class="icon"><use href="#i-star"/></svg></span>
            <div>
                <p class="stat-num"><?= number_format($stats['avis']) ?></p>
                <p class="stat-label">Avis publiés</p>
            </div>
        </div>
        <a class="stat stat--link" href="<?= e(url('admin/books', ['statut' => 'en_attente'])) ?>" title="Voir les livres en attente de validation">
            <span class="stat-icon <?= $stats['en_attente'] > 0 ? 'stat-icon--red' : 'stat-icon--blue' ?>">
                <svg class="icon"><use href="#i-clock"/></svg>
            </span>
            <div>
                <p class="stat-num"><?= number_format($stats['en_attente']) ?></p>
                <p class="stat-label">Livres en attente</p>
            </div>
        </a>
    </div>

    <!-- ---- Toolbar + recherche back-office ---- -->
    <div class="section-head reveal">
        <h2 class="section-title">Liste des <em>livres</em></h2>
        <form class="search-field" style="max-width:380px;display:inline-flex" role="search" action="<?= e(url('admin')) ?>" method="get">
            <svg class="icon"><use href="#i-search"/></svg>
            <label class="sr-only" for="admin-search-input">Rechercher un livre</label>
            <input type="search" id="admin-search-input" class="search-input" name="q" value="<?= e($search) ?>" placeholder="Rechercher…">
            <button type="submit" class="search-btn">Filtrer</button>
            <?php if ($search !== ''): ?>
                <a class="icon-btn" href="<?= e(url('admin')) ?>" aria-label="Effacer la recherche">
                    <svg class="icon"><use href="#i-close"/></svg>
                </a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (empty($books)): ?>
        <div class="empty-state">
            <span class="empty-icon"><svg class="icon"><use href="#i-search"/></svg></span>
            <h2>Aucun livre trouvé</h2>
            <p>Modifiez vos critères ou réinitialisez la recherche.</p>
            <a class="btn btn--soft" href="<?= e(url('admin')) ?>">Afficher tout</a>
        </div>
    <?php else: ?>
        <div class="table-panel reveal">
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="sr-only">Liste des livres du catalogue</caption>
                    <thead>
                        <tr>
                            <th scope="col">Livre</th>
                            <th scope="col">Auteur</th>
                            <th scope="col">Édition</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="cell-actions"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <tr data-book-row
                                data-id="<?= (int) $book['id'] ?>"
                                data-edit-url="<?= e(url('admin/edit', ['id' => $book['id']])) ?>"
                                data-edit-cover="<?= e(\App\Core\CoverUploader::url($book['couverture'] ?? null)) ?>"
                                data-edit-title="<?= e($book['titre']) ?>"
                                data-edit-sub="Fiche « <?= e($book['titre']) ?> » — ID <?= (int) $book['id'] ?>"
                                data-edit-titre="<?= e($book['titre']) ?>"
                                data-edit-auteur="<?= e($book['auteur']) ?>"
                                data-edit-maison="<?= e($book['maison_edition']) ?>"
                                data-edit-exemplaires="<?= (int) $book['nombre_exemplaire'] ?>"
                                data-edit-description="<?= e($book['description']) ?>">

                                <td>
                                    <div class="cell-book">
                                        <?php if (!empty($book['couverture']) && is_file(UPLOADS_PATH . '/' . $book['couverture'])): ?>
                                            <span class="cell-thumb"><img src="<?= e(APP_BASE_URL) ?>/uploads/<?= e($book['couverture']) ?>" alt=""></span>
                                        <?php else: ?>
                                            <span class="cell-thumb"><svg class="icon" style="width:16px;height:16px"><use href="#i-book"/></svg></span>
                                        <?php endif; ?>
                                        <strong>
                                            <a href="<?= e(url('book/show', ['id' => $book['id']])) ?>"><?= e($book['titre']) ?></a>
                                        </strong>
                                        <span class="id-pill">#<?= (int) $book['id'] ?></span>
                                    </div>
                                </td>
                                <td class="cell-author"><?= e($book['auteur']) ?></td>
                                <td class="cell-author"><?= e($book['maison_edition']) ?></td>
                                <td class="cell-stock">
                                    <?php if ($book['nombre_exemplaire'] > 0): ?>
                                        <?php $badge = $book['nombre_exemplaire'] === 1 ? 'low' : 'ok'; ?>
                                        <span class="badge badge--<?= $badge ?>">
                                            <svg class="icon"><use href="#i-check"/></svg>
                                            <span class="num"><?= (int) $book['nombre_exemplaire'] ?></span>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Rupture</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($book['statut'] === \App\Models\Book::STATUT_PUBLIE): ?>
                                        <span class="badge badge--soft">Publié</span>
                                    <?php elseif ($book['statut'] === \App\Models\Book::STATUT_REFUSE): ?>
                                        <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Refusé</span>
                                    <?php else: ?>
                                        <span class="badge badge--pending"><svg class="icon"><use href="#i-clock"/></svg> En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <button type="button" class="icon-btn icon-btn--primary" data-open-edit aria-label="Modifier <?= e($book['titre']) ?>">
                                        <svg class="icon"><use href="#i-edit"/></svg>
                                    </button>
                                    <form action="<?= e(url('admin/delete')) ?>" method="post" class="inline"
                                          data-confirm="Supprimer définitivement « <?= e($book['titre']) ?> » ?"
                                          data-confirm-title="Supprimer ce livre ?">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                        <button type="submit" class="icon-btn icon-btn--danger" aria-label="Supprimer <?= e($book['titre']) ?>">
                                            <svg class="icon"><use href="#i-trash"/></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Pagination des résultats">
                    <ul class="pagination-list">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li>
                                <?php if ($i === $page): ?>
                                    <span class="page-current" aria-current="page"><?= $i ?></span>
                                <?php else: ?>
                                    <a class="page-link" href="<?= e(url('admin', array_filter(['q' => $search, 'pageNum' => $i]))) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ---- Panneau latéral (slide-over) : ajout / édition sans rechargement ---- -->
<aside class="drawer" data-drawer aria-hidden="true" aria-labelledby="drawer-title">
    <div class="drawer-header">
        <div>
            <h2 class="drawer-title" id="drawer-title" data-drawer-title>Ajouter un livre</h2>
            <p class="drawer-sub" data-drawer-sub>Renseignez les informations du nouveau livre.</p>
        </div>
        <button type="button" class="icon-btn" data-drawer-close aria-label="Fermer le panneau">
            <svg class="icon"><use href="#i-close"/></svg>
        </button>
    </div>

    <div class="drawer-body">
        <form action="<?= e(url('admin/create')) ?>" method="post" class="form-card" style="max-width:none;box-shadow:none;border:0;padding:0" enctype="multipart/form-data" data-book-form novalidate>
            <?= \App\Core\Csrf::field() ?>
            <div class="form-grid">
                <?php require VIEWS_PATH . '/admin/_form_fields.php'; ?>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn--brand btn--lg" data-book-submit>
                    <svg class="icon"><use href="#i-check"/></svg> Enregistrer
                </button>
                <button type="button" class="btn btn--soft" data-drawer-close>Annuler</button>
            </div>
        </form>
    </div>
</aside>