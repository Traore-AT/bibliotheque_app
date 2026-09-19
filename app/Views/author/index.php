<?php /** Vue : espace auteur — mes livres soumis au catalogue */ ?>

<section class="container page-inner">
    <div class="page-head reveal">
        <div>
            <h1>Espace auteur</h1>
            <p class="head-meta">
                Vous écrivez des livres ? Mettez-les à la disposition des lecteurs :
                chaque dépôt est validé par l'administration avant publication.
            </p>
        </div>
        <a class="btn btn--brand btn--lg" href="<?= e(url('auteur/create')) ?>">
            <svg class="icon"><use href="#i-plus"/></svg> Publier mon livre
        </a>
    </div>

    <!-- ---- Bilan de mes dépôts ---- -->
    <div class="stats reveal">
        <div class="stat">
            <span class="stat-icon stat-icon--teal"><svg class="icon"><use href="#i-book"/></svg></span>
            <div>
                <p class="stat-num"><?= (int) $counts['publie'] ?></p>
                <p class="stat-label">Livres publiés</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon stat-icon--amber"><svg class="icon"><use href="#i-clock"/></svg></span>
            <div>
                <p class="stat-num"><?= (int) $counts['en_attente'] ?></p>
                <p class="stat-label">En attente de validation</p>
            </div>
        </div>
        <div class="stat">
            <span class="stat-icon <?= $counts['refuse'] > 0 ? 'stat-icon--red' : 'stat-icon--blue' ?>">
                <svg class="icon"><use href="#i-flag"/></svg>
            </span>
            <div>
                <p class="stat-num"><?= (int) $counts['refuse'] ?></p>
                <p class="stat-label">Refusés à corriger</p>
            </div>
        </div>
    </div>

    <div class="section-head reveal">
        <h2 class="section-title">Mes <em>livres</em></h2>
    </div>

    <?php if (empty($books)): ?>
        <div class="empty-state reveal">
            <span class="empty-icon"><svg class="icon"><use href="#i-pen"/></svg></span>
            <h2>Aucun dépôt pour le moment</h2>
            <p>Publiez votre premier livre : il rejoindra le catalogue après validation.</p>
            <a class="btn btn--brand" href="<?= e(url('auteur/create')) ?>">
                <svg class="icon"><use href="#i-plus"/></svg> Publier mon livre
            </a>
        </div>
    <?php else: ?>
        <div class="table-panel reveal">
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="sr-only">Mes livres</caption>
                    <thead>
                        <tr>
                            <th scope="col">Livre</th>
                            <th scope="col">Statut</th>
                            <th scope="col">Stock</th>
                            <th scope="col" class="cell-actions"><span class="sr-only">Actions</span></th>
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
                                        <span class="table-sub">
                                            <?= e($book['auteur'] . ' · ' . $book['maison_edition']) ?>
                                        </span>
                                    </div>
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
                                <td class="cell-actions">
                                    <a class="icon-btn icon-btn--primary"
                                       href="<?= e(url('book/show', ['id' => $book['id']])) ?>"
                                       aria-label="Voir <?= e($book['titre']) ?>">
                                        <svg class="icon"><use href="#i-search"/></svg>
                                    </a>
                                    <a class="icon-btn icon-btn--primary"
                                       href="<?= e(url('auteur/edit', ['id' => $book['id']])) ?>"
                                       aria-label="Modifier <?= e($book['titre']) ?>">
                                        <svg class="icon"><use href="#i-edit"/></svg>
                                    </a>
                                    <form action="<?= e(url('auteur/delete')) ?>" method="post" class="inline"
                                          data-confirm="Supprimer définitivement « <?= e($book['titre']) ?> » de votre espace ?"
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
        </div>
    <?php endif; ?>
</section>