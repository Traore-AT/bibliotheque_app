<?php /** Vue : espace personnel du lecteur */ ?>

<section class="container page-inner">
    <div class="page-head">
        <div>
            <h1>Mon espace</h1>
            <p class="head-meta">
                Bonjour <?= e($user['prenom'] . ' ' . mb_strtoupper($user['nom'])) ?>.
                Vous retrouvez ici vos emprunts et vos avis.
            </p>
        </div>
    </div>

    <?php if ($activeLoans === []): ?>
        <div class="empty-state reveal">
            <span class="empty-icon"><svg class="icon"><use href="#i-book"/></svg></span>
            <h2>Aucun emprunt en cours</h2>
            <p>Parcourez le catalogue et empruntez un livre : il sera réservé pour vous.</p>
            <a class="btn btn--brand" href="<?= e(url('')) ?>">Découvrir le catalogue</a>
        </div>
    <?php else: ?>
        <h2 class="section-title">Emprunts en cours</h2>
        <div class="table-card reveal">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Emprunté le</th>
                            <th>À rendre le</th>
                            <th>Statut</th>
                            <th class="th-actions"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeLoans as $loan): ?>
                            <tr>
                                <td data-label="Livre">
                                    <a class="table-link" href="<?= e(url('book/show', ['id' => $loan['livre_id']])) ?>">
                                        <?= e($loan['titre']) ?>
                                    </a>
                                    <span class="table-sub"><?= e($loan['auteur']) ?></span>
                                </td>
                                <td data-label="Emprunté le"><?= e((new DateTimeImmutable($loan['date_emprunt']))->format('d/m/Y')) ?></td>
                                <td data-label="À rendre le"><?= e((new DateTimeImmutable($loan['date_retour_prevue']))->format('d/m/Y')) ?></td>
                                <td data-label="Statut">
                                    <?php if ($loan['est_retard']): ?>
                                        <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> En retard</span>
                                    <?php else: ?>
                                        <span class="badge badge--ok"><svg class="icon"><use href="#i-check"/></svg> En cours</span>
                                    <?php endif; ?>
                                </td>
                                <td data-label="Action" class="cell-actions">
                                    <form action="<?= e(url('loan/return')) ?>" method="post">
                                        <?= \App\Core\Csrf::field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $loan['loan_id'] ?>">
                                        <button type="submit" class="btn btn--soft btn--sm"
                                                data-confirm="Rendre « <?= e($loan['titre']) ?> » ?"
                                                <?= $loan['est_retard'] ? 'data-retour-retard' : '' ?>>
                                            <svg class="icon"><use href="#i-return"/></svg>
                                            Rendre le livre
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

    <h2 class="section-title">Mes avis</h2>
    <?php if ($myReviews === []): ?>
        <p class="muted">Vous n'avez pas encore déposé d'avis.</p>
    <?php else: ?>
        <ul class="review-grid">
            <?php foreach ($myReviews as $review): ?>
                <li class="review-card reveal">
                    <div class="review-head">
                        <div>
                            <a class="table-link" href="<?= e(url('book/show', ['id' => $review['livre_id']])) ?>">
                                <?= e($review['livre_titre']) ?>
                            </a>
                            <?php $stars = (float) $review['note']; ?>
                            <?php require VIEWS_PATH . '/partials/stars.php'; ?>
                        </div>
                        <form action="<?= e(url('review/delete')) ?>" method="post">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $review['review_id'] ?>">
                            <input type="hidden" name="id_livre" value="<?= (int) $review['livre_id'] ?>">
                            <button type="submit" class="icon-btn" aria-label="Supprimer mon avis" data-confirm="Supprimer cet avis ?"
                                    title="Supprimer mon avis">
                                <svg class="icon"><use href="#i-trash"/></svg>
                            </button>
                        </form>
                    </div>
                    <p class="review-text"><?= e($review['commentaire']) ?></p>
                    <p class="review-date"><?= e((new DateTimeImmutable($review['date_publication']))->format('d/m/Y')) ?></p>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h2 class="section-title">Historique des emprunts</h2>
    <?php if ($history === []): ?>
        <p class="muted">Aucun retour pour le moment.</p>
    <?php else: ?>
        <div class="table-card reveal">
            <div class="table-scroll">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Livre</th>
                            <th>Emprunté le</th>
                            <th>Rendu le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $loan): ?>
                            <tr>
                                <td data-label="Livre">
                                    <a class="table-link" href="<?= e(url('book/show', ['id' => $loan['livre_id']])) ?>">
                                        <?= e($loan['titre']) ?>
                                    </a>
                                    <span class="table-sub"><?= e($loan['auteur']) ?></span>
                                </td>
                                <td data-label="Emprunté le"><?= e((new DateTimeImmutable($loan['date_emprunt']))->format('d/m/Y')) ?></td>
                                <td data-label="Rendu le">
                                    <?= e($loan['date_retour_effective'] !== null
                                        ? (new DateTimeImmutable($loan['date_retour_effective']))->format('d/m/Y H:i')
                                        : '—') ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>