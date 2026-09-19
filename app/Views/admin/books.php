<?php /** Vue : modération des livres soumis par les auteurs (espace auteur) */ ?>

<section class="container page-inner">
    <div class="page-head reveal">
        <div>
            <h1>Modération des livres</h1>
            <p class="head-meta">
                Validez ou refusez les livres déposés par les membres — la publication
                d'un livre le rend empruntable par tous.
            </p>
        </div>
    </div>

    <!-- Filtres par statut -->
    <div class="tabs reveal" role="tablist" aria-label="Filtrer les livres">
        <?php
        $tabs = [
            ''                => 'Tous (' . $counts[''] . ')',
            'en_attente'      => 'En attente (' . $counts['en_attente'] . ')',
            'publie'          => 'Publiés (' . $counts['publie'] . ')',
            'refuse'          => 'Refusés (' . $counts['refuse'] . ')',
        ];
        ?>
        <?php foreach ($tabs as $key => $label): ?>
            <a class="tab <?= $statut === $key ? 'is-active' : '' ?>"
               href="<?= e(url('admin/books', array_filter(['statut' => $key, 'q' => $search]))) ?>"
               aria-current="<?= $statut === $key ? 'page' : 'false' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form class="search-field admin-search" role="search" action="<?= e(url('admin/books')) ?>" method="get">
        <svg class="icon"><use href="#i-search"/></svg>
        <label class="sr-only" for="books-search-input">Rechercher par titre, auteur ou éditeur</label>
        <input type="search" id="books-search-input" class="search-input" name="q" value="<?= e($search) ?>"
               placeholder="Titre, auteur, éditeur…">
        <button type="submit" class="search-btn">Filtrer</button>
        <?php if ($search !== ''): ?>
            <a class="icon-btn" href="<?= e(url('admin/books', ['statut' => $statut])) ?>" aria-label="Effacer la recherche">
                <svg class="icon"><use href="#i-close"/></svg>
            </a>
        <?php endif; ?>
    </form>

    <?php if (empty($books)): ?>
        <div class="empty-state reveal">
            <span class="empty-icon"><svg class="icon"><use href="#i-pen"/></svg></span>
            <h2>Aucun livre trouvé</h2>
            <p>Modifiez vos filtres ou réinitialisez la recherche.</p>
            <a class="btn btn--soft" href="<?= e(url('admin/books')) ?>">Afficher tout</a>
        </div>
    <?php else: ?>
        <div class="table-panel reveal">
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="sr-only">Liste des livres à modérer</caption>
                    <thead>
                        <tr>
                            <th scope="col">Livre</th>
                            <th scope="col">Déposé par</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="cell-actions">Modération</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($books as $book): ?>
                            <?php $isPublished = $book['statut'] === \App\Models\Book::STATUT_PUBLIE; ?>
                            <tr>
                                <td>
                                    <div class="cell-book">
                                        <strong>
                                            <?php if ($isPublished): ?>
                                                <a href="<?= e(url('book/show', ['id' => $book['id']])) ?>"><?= e($book['titre']) ?></a>
                                            <?php else: ?>
                                                <span><?= e($book['titre']) ?></span>
                                            <?php endif; ?>
                                        </strong>
                                        <span class="table-sub"><?= e($book['auteur'] . ' · ' . $book['maison_edition']) ?></span>
                                    </div>
                                </td>
                                <td class="cell-author">
                                    <?php if (!empty($book['id_auteur'])): ?>
                                        <?= e(($book['auteur_prenom'] ?? '') . ' ' . mb_strtoupper((string) ($book['auteur_nom'] ?? ''))) ?>
                                    <?php else: ?>
                                        <span class="muted">—</span>
                                    <?php endif; ?>
                                </td>
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
                                        <span class="badge badge--ok"><svg class="icon"><use href="#i-check"/></svg> Publié</span>
                                    <?php elseif ($book['statut'] === \App\Models\Book::STATUT_REFUSE): ?>
                                        <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Refusé</span>
                                    <?php else: ?>
                                        <span class="badge badge--pending"><svg class="icon"><use href="#i-clock"/></svg> En attente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <?php if (!$isPublished): ?>
                                        <form action="<?= e(url('admin/status')) ?>" method="post" class="inline"
                                              data-confirm="Publier « <?= e($book['titre']) ?> » dans le catalogue ?">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                            <input type="hidden" name="statut" value="publie">
                                            <input type="hidden" name="filtre_statut" value="<?= e($statut) ?>">
                                            <input type="hidden" name="filtre_q" value="<?= e($search) ?>">
                                            <button type="submit" class="btn btn--brand btn--sm">
                                                <svg class="icon"><use href="#i-check"/></svg> Publier
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($book['statut'] !== \App\Models\Book::STATUT_REFUSE): ?>
                                        <form action="<?= e(url('admin/status')) ?>" method="post" class="inline"
                                              data-confirm="Refuser « <?= e($book['titre']) ?> » ? L'auteur pourra le modifier et le resoumettre.">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                            <input type="hidden" name="statut" value="refuse">
                                            <input type="hidden" name="filtre_statut" value="<?= e($statut) ?>">
                                            <input type="hidden" name="filtre_q" value="<?= e($search) ?>">
                                            <button type="submit" class="btn btn--soft btn--sm">
                                                <svg class="icon"><use href="#i-close"/></svg> Refuser
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($book['statut'] === \App\Models\Book::STATUT_REFUSE): ?>
                                        <form action="<?= e(url('admin/status')) ?>" method="post" class="inline">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $book['id'] ?>">
                                            <input type="hidden" name="statut" value="en_attente">
                                            <input type="hidden" name="filtre_statut" value="<?= e($statut) ?>">
                                            <input type="hidden" name="filtre_q" value="<?= e($search) ?>">
                                            <button type="submit" class="btn btn--soft btn--sm">
                                                <svg class="icon"><use href="#i-flag"/></svg> Remettre en attente
                                            </button>
                                        </form>
                                    <?php endif; ?>
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
                                    <a class="page-link" href="<?= e(url('admin/books', array_filter(['statut' => $statut, 'q' => $search, 'pageNum' => $i]))) ?>"><?= $i ?></a>
                                <?php endif; ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</section>