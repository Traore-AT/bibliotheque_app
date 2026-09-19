<?php /** Vue : tableau de bord d'administration — Charte graphique premium + Charts */ ?>

<?php
/* Calcul du max pour le classement des livres */
$topMax = 0;
if (!empty($chartTopBooks)) {
    $topBooksArr = json_decode($chartTopBooks, true) ?: [];
    foreach ($topBooksArr as $tb) { $topMax = max($topMax, (int)($tb['total'] ?? 0)); }
} else {
    $topBooksArr = [];
}
?>

<section class="admin-dashboard">

    <!-- ====== EN-TÊTE ====== -->
    <div class="admin-page-head reveal">
        <div class="admin-page-head__text">
            <div class="admin-badge">
                <svg class="icon"><use href="#i-spark"/></svg>
                Administration
            </div>
            <h1>Tableau de bord</h1>
            <p class="admin-page-head__sub">Vue d'ensemble de la bibliothèque numérique en temps réel</p>
        </div>
        <div class="admin-page-head__actions">
            <a href="<?= e(url('admin/books', ['statut' => 'en_attente'])) ?>" class="btn btn--soft btn--sm">
                <svg class="icon"><use href="#i-clock"/></svg>
                <?= number_format($stats['en_attente']) ?> en attente
            </a>
            <button type="button" class="btn btn--brand btn--lg" data-open-create>
                <svg class="icon"><use href="#i-plus"/></svg> Ajouter un livre
            </button>
        </div>
    </div>

    <!-- ====== STAT CARDS PREMIUM ====== -->
    <div class="stat-cards reveal">

        <div class="stat-card stat-card--teal">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-library"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['livres'] ?>">0</p>
                <p class="stat-card__label">Livres au catalogue</p>
            </div>
            <div class="stat-card__bar" style="--bar-pct: 100%"></div>
        </div>

        <div class="stat-card stat-card--blue">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-users"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['utilisateurs'] ?>">0</p>
                <p class="stat-card__label">Utilisateurs inscrits</p>
            </div>
            <div class="stat-card__bar" style="--bar-pct: 100%"></div>
        </div>

        <div class="stat-card stat-card--amber">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-clock"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['emprunts_actifs'] ?>">0</p>
                <p class="stat-card__label">Emprunts en cours</p>
            </div>
            <?php
                $pctEmprunts = $stats['emprunts_total'] > 0
                    ? round($stats['emprunts_actifs'] / $stats['emprunts_total'] * 100)
                    : 0;
            ?>
            <div class="stat-card__bar" style="--bar-pct: <?= $pctEmprunts ?>%"></div>
        </div>

        <div class="stat-card <?= $stats['retards'] > 0 ? 'stat-card--danger' : 'stat-card--teal' ?>">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-flag"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['retards'] ?>">0</p>
                <p class="stat-card__label">Emprunts en retard</p>
            </div>
            <?php $pctRetard = $stats['emprunts_actifs'] > 0
                ? round($stats['retards'] / $stats['emprunts_actifs'] * 100) : 0; ?>
            <div class="stat-card__bar" style="--bar-pct: <?= $pctRetard ?>%"></div>
        </div>

        <div class="stat-card stat-card--purple">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-star"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['avis'] ?>">0</p>
                <p class="stat-card__label">Avis publiés</p>
            </div>
            <div class="stat-card__bar" style="--bar-pct: 100%"></div>
        </div>

        <a class="stat-card stat-card--orange stat-card--link"
           href="<?= e(url('admin/books', ['statut' => 'en_attente'])) ?>"
           title="Voir les livres en attente">
            <div class="stat-card__icon">
                <svg class="icon"><use href="#i-clock"/></svg>
            </div>
            <div class="stat-card__body">
                <p class="stat-card__num" data-countup="<?= (int) $stats['en_attente'] ?>">0</p>
                <p class="stat-card__label">Livres à modérer</p>
            </div>
            <div class="stat-card__bar" style="--bar-pct: 100%"></div>
        </a>

    </div>

    <!-- ====== ZONE GRAPHIQUES ====== -->
    <div class="charts-grid reveal">

        <!-- Graphique 1 : Emprunts par mois (barres) -->
        <div class="chart-card chart-card--wide">
            <div class="chart-card__head">
                <div>
                    <h2 class="chart-card__title">Activité des emprunts</h2>
                    <p class="chart-card__sub">6 derniers mois</p>
                </div>
                <span class="chart-legend-dot chart-legend-dot--teal"></span>
            </div>
            <div class="chart-wrap">
                <canvas id="chart-loans" aria-label="Graphique emprunts par mois" role="img"></canvas>
            </div>
        </div>

        <!-- Graphique 2 : Répartition des livres (donut) -->
        <div class="chart-card">
            <div class="chart-card__head">
                <div>
                    <h2 class="chart-card__title">Statut des livres</h2>
                    <p class="chart-card__sub">Répartition du catalogue</p>
                </div>
            </div>
            <div class="chart-wrap chart-wrap--donut">
                <canvas id="chart-books" aria-label="Répartition des livres par statut" role="img"></canvas>
            </div>
            <!-- Légende donut -->
            <ul class="donut-legend">
                <li><span class="donut-legend__dot" style="background:#0F766E"></span>Publiés <strong><?= $stats['livres'] - $stats['en_attente'] ?></strong></li>
                <li><span class="donut-legend__dot" style="background:#D97706"></span>En attente <strong><?= $stats['en_attente'] ?></strong></li>
                <li><span class="donut-legend__dot" style="background:#DC2626"></span>Refusés <strong><?= number_format((int)\App\Models\Book::STATUT_REFUSE === 'refuse' ? 0 : 0) ?></strong></li>
            </ul>
        </div>

        <!-- Graphique 3 : Croissance utilisateurs (ligne) -->
        <div class="chart-card">
            <div class="chart-card__head">
                <div>
                    <h2 class="chart-card__title">Nouvelles inscriptions</h2>
                    <p class="chart-card__sub">Croissance sur 6 mois</p>
                </div>
                <span class="chart-legend-dot chart-legend-dot--blue"></span>
            </div>
            <div class="chart-wrap">
                <canvas id="chart-users" aria-label="Croissance des inscriptions" role="img"></canvas>
            </div>
        </div>

    </div>

    <!-- ====== TOP LIVRES ====== -->
    <?php if (!empty($topBooksArr)): ?>
    <div class="top-books reveal">
        <div class="section-head">
            <h2 class="section-title">Top <em>livres</em> empruntés</h2>
            <a href="<?= e(url('admin/loans')) ?>" class="link-more">Voir tous les emprunts →</a>
        </div>
        <div class="top-books__list">
            <?php foreach ($topBooksArr as $rank => $tb): ?>
            <?php $pct = $topMax > 0 ? round(((int)$tb['total'] / $topMax) * 100) : 0; ?>
            <div class="top-book-item">
                <span class="top-book-rank">#<?= $rank + 1 ?></span>
                <div class="top-book-info">
                    <strong class="top-book-title"><?= e($tb['titre']) ?></strong>
                    <span class="top-book-author"><?= e($tb['auteur']) ?></span>
                </div>
                <div class="top-book-bar-wrap">
                    <div class="top-book-bar" style="--bar-w: <?= $pct ?>%"></div>
                </div>
                <span class="top-book-count"><?= (int)$tb['total'] ?> emprunt<?= $tb['total'] > 1 ? 's' : '' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- ====== QUICK LINKS ADMIN ====== -->
    <div class="admin-quick-links reveal">
        <a href="<?= e(url('admin/users')) ?>" class="admin-quick-card">
            <span class="admin-quick-card__icon admin-quick-card__icon--blue">
                <svg class="icon"><use href="#i-users"/></svg>
            </span>
            <div>
                <strong>Gérer les utilisateurs</strong>
                <span><?= number_format($stats['utilisateurs']) ?> inscrits</span>
            </div>
            <svg class="icon admin-quick-card__arrow"><use href="#i-arrow"/></svg>
        </a>
        <a href="<?= e(url('admin/loans')) ?>" class="admin-quick-card">
            <span class="admin-quick-card__icon admin-quick-card__icon--teal">
                <svg class="icon"><use href="#i-return"/></svg>
            </span>
            <div>
                <strong>Gérer les emprunts</strong>
                <span><?= number_format($stats['emprunts_actifs']) ?> en cours</span>
            </div>
            <svg class="icon admin-quick-card__arrow"><use href="#i-arrow"/></svg>
        </a>
        <a href="<?= e(url('admin/books', ['statut' => 'en_attente'])) ?>" class="admin-quick-card <?= $stats['en_attente'] > 0 ? 'admin-quick-card--alert' : '' ?>">
            <span class="admin-quick-card__icon admin-quick-card__icon--amber">
                <svg class="icon"><use href="#i-flag"/></svg>
            </span>
            <div>
                <strong>Modération des livres</strong>
                <span><?= number_format($stats['en_attente']) ?> en attente de validation</span>
            </div>
            <svg class="icon admin-quick-card__arrow"><use href="#i-arrow"/></svg>
        </a>
    </div>

    <!-- ====== TOOLBAR + RECHERCHE ====== -->
    <div class="section-head reveal" style="margin-top:2.5rem">
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

<!-- ---- Données JSON pour Chart.js ---- -->
<script id="admin-chart-data" type="application/json">
{
    "loanTrend":  <?= $chartLoanTrend ?? '[]' ?>,
    "bookStatus": <?= $chartBookStatus ?? '[]' ?>,
    "topBooks":   <?= $chartTopBooks ?? '[]' ?>,
    "userGrowth": <?= $chartUserGrowth ?? '[]' ?>
}
</script>

<!-- ---- Panneau latéral (slide-over) ---- -->
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