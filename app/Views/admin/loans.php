<?php /** Vue : gestion globale des emprunts (admin) */ ?>

<section class="container page-inner">
    <div class="page-head reveal">
        <div>
            <h1>Emprunts</h1>
            <p class="head-meta">
                <?= $stats['actives'] ?> en cours, <?= $stats['en_retard'] ?> en retard,
                <?= $stats['total'] ?> au total.
            </p>
        </div>
    </div>

    <!-- Filtres par statut -->
    <div class="tabs reveal" role="tablist" aria-label="Filtrer les emprunts">
        <?php
        $tabs = [
            ''          => 'Tous (' . $stats['total'] . ')',
            'en_cours'  => 'En cours (' . $stats['actives'] . ')',
            'en_retard' => 'En retard (' . $stats['en_retard'] . ')',
            'termine'   => 'Terminés',
        ];
        ?>
        <?php foreach ($tabs as $key => $label): ?>
            <a class="tab <?= $statut === $key ? 'is-active' : '' ?>"
               href="<?= e(url('admin/loans', array_filter(['statut' => $key, 'q' => $search]))) ?>"
               aria-current="<?= $statut === $key ? 'page' : 'false' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <form class="search-field admin-search" role="search" action="<?= e(url('admin/loans')) ?>" method="get">
        <svg class="icon"><use href="#i-search"/></svg>
        <label class="sr-only" for="loans-search-input">Rechercher par titre ou emprunteur</label>
        <input type="search" id="loans-search-input" class="search-input" name="q" value="<?= e($search) ?>"
               placeholder="Livre, nom, prénom…">
        <button type="submit" class="search-btn">Filtrer</button>
    </form>

    <?php if ($loans === []): ?>
        <div class="empty-state reveal">
            <span class="empty-icon"><svg class="icon"><use href="#i-return"/></svg></span>
            <h2>Aucun emprunt trouvé</h2>
            <p>Modifiez vos filtres ou réinitialisez la recherche.</p>
            <a class="btn btn--soft" href="<?= e(url('admin/loans')) ?>">Afficher tout</a>
        </div>
    <?php else: ?>
        <div class="table-panel reveal">
            <div class="table-wrap">
                <table class="data-table">
                    <caption class="sr-only">Liste des emprunts</caption>
                    <thead>
                        <tr>
                            <th scope="col">Livre</th>
                            <th scope="col">Emprunteur</th>
                            <th scope="col">Emprunté le</th>
                            <th scope="col">À rendre</th>
                            <th scope="col">Rendu le</th>
                            <th scope="col">Statut</th>
                            <th scope="col" class="cell-actions">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <a href="<?= e(url('book/show', ['id' => $loan['livre_id']])) ?>"><?= e($loan['livre_titre']) ?></a>
                                    </strong>
                                    <span class="table-sub"><?= e($loan['livre_auteur']) ?></span>
                                </td>
                                <td class="cell-author">
                                    <?= e($loan['user_prenom'] . ' ' . mb_strtoupper($loan['user_nom'])) ?>
                                </td>
                                <td><?= e((new DateTimeImmutable($loan['date_emprunt']))->format('d/m/Y')) ?></td>
                                <td><?= e((new DateTimeImmutable($loan['date_retour_prevue']))->format('d/m/Y')) ?></td>
                                <td><?= $loan['date_retour_effective'] !== null ? e((new DateTimeImmutable($loan['date_retour_effective']))->format('d/m/Y')) : '—' ?></td>
                                <td>
                                    <?php if ($loan['statut'] === 'en_cours' && $loan['est_retard']): ?>
                                        <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> En retard</span>
                                    <?php elseif ($loan['statut'] === 'en_cours'): ?>
                                        <span class="badge badge--ok"><svg class="icon"><use href="#i-check"/></svg> En cours</span>
                                    <?php else: ?>
                                        <span class="badge badge--soft">Terminé</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-actions">
                                    <?php if ($loan['statut'] === 'en_cours'): ?>
                                        <form action="<?= e(url('admin/loan-return')) ?>" method="post">
                                            <?= \App\Core\Csrf::field() ?>
                                            <input type="hidden" name="id" value="<?= (int) $loan['loan_id'] ?>">
                                            <button type="submit" class="btn btn--soft btn--sm"
                                                    data-confirm="Clôturer l'emprunt de « <?= e($loan['livre_titre']) ?> » par <?= e($loan['user_prenom']) ?> ?">
                                                <svg class="icon"><use href="#i-return"/></svg>
                                                Rendre le livre
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>