<?php /** Vue détail d'un livre : couverture, disponibilité, note moyenne, avis. */ ?>

<section class="detail" aria-label="Détail du livre">
    <div class="container">
        <nav aria-label="Fil d'ariane" class="breadcrumb reveal">
            <a href="<?= e(url('')) ?>">Accueil</a>
            <span class="sep" aria-hidden="true">/</span>
            <a href="<?= e(url('book/show', ['id' => $book['id']])) ?>" aria-current="page"><?= e($book['titre']) ?></a>
        </nav>

        <article class="detail-card reveal" style="--h: <?= ($book['id'] * 47) % 360 ?>">
            <div class="detail-grid">
                <!-- Couverture -->
                <div class="detail-cover">
                    <?php if (!empty($book['couverture']) && is_file(UPLOADS_PATH . '/' . $book['couverture'])): ?>
                        <img src="<?= e(APP_BASE_URL) ?>/uploads/<?= e($book['couverture']) ?>" alt="Couverture du livre <?= e($book['titre']) ?>">
                    <?php else: ?>
                        <div class="cover-hero" aria-hidden="true">
                            <span><?= e(mb_strtoupper(mb_substr($book['titre'], 0, 1))) ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Informations -->
                <div class="detail-info">
                    <?php if (!$published): ?>
                        <div class="notice notice--<?= $book['statut'] === \App\Models\Book::STATUT_REFUSE ? 'danger' : 'brand' ?>" role="status">
                            <svg class="icon"><use href="#i-alert"/></svg>
                            <?php if ($book['statut'] === \App\Models\Book::STATUT_REFUSE): ?>
                                <span><strong>Livre refusé.</strong> Cet aperçu n'est visible que par vous et l'admin — corrigez-le et resoumettez-le, ou attendez l'examen de l'administrateur.</span>
                            <?php else: ?>
                                <span><strong>En attente de validation.</strong> Cet aperçu n'est visible que par vous et l'admin — il sera empruntable une fois publié.</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="detail-cats">
                        <?php if (!$published): ?>
                            <?php if ($book['statut'] === \App\Models\Book::STATUT_REFUSE): ?>
                                <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Refusé</span>
                            <?php else: ?>
                                <span class="badge badge--pending"><svg class="icon"><use href="#i-clock"/></svg> En attente</span>
                            <?php endif; ?>
                        <?php elseif ($book['nombre_exemplaire'] > 0): ?>
                            <span class="badge badge--ok"><svg class="icon"><use href="#i-check"/></svg> Disponible</span>
                        <?php else: ?>
                            <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Indisponible — actuellement loué</span>
                        <?php endif; ?>
                        <?php if ($average['count'] > 0): ?>
                            <span class="badge badge--soft"><svg class="icon"><use href="#i-star"/></svg>
                                <?= number_format($average['avg'], 1, ',', ' ') ?> / 5
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="detail-title"><?= e($book['titre']) ?></h1>
                    <p class="detail-author">par <strong><?= e($book['auteur']) ?></strong></p>

                    <?php if (!empty($book['id_auteur']) && isset($book['auteur_prenom'])): ?>
                        <p class="detail-contrib">
                            <svg class="icon"><use href="#i-pen"/></svg>
                            Écrit et déposé par
                            <?= e(trim(($book['auteur_prenom'] ?? '') . ' ' . ($book['auteur_nom'] ?? ''))) ?>
                        </p>
                    <?php endif; ?>

                    <?php if ($average['count'] > 0): ?>
                        <div class="detail-rating">
                            <?php $stars = $average['avg']; $showValue = true; ?>
                            <a href="#avis" class="stars-link"><?php require VIEWS_PATH . '/partials/stars.php'; ?></a>
                            <span class="rating-count"><?= $average['count'] ?> avis</span>
                        </div>
                    <?php endif; ?>

                    <dl class="facts">
                        <div class="fact">
                            <dt>Maison d'édition</dt>
                            <dd><?= e($book['maison_edition']) ?></dd>
                        </div>
                        <div class="fact">
                            <dt>Exemplaires disponibles</dt>
                            <dd><?= (int) $book['nombre_exemplaire'] ?></dd>
                        </div>
                    </dl>

                    <div>
                        <p class="prose-title">Description</p>
                        <p class="prose"><?= nl2br(e($book['description'])) ?></p>
                    </div>

                    <div class="detail-actions">
                        <?php if (!$published): ?>
                            <span class="inline-status inline-status--warn" role="status">
                                <svg class="icon"><use href="#i-clock"/></svg>
                                <?= $book['statut'] === \App\Models\Book::STATUT_REFUSE
                                    ? 'Ce livre ne peut pas être emprunté : il a été refusé.'
                                    : 'Ce livre ne peut pas être emprunté en attendant sa validation.' ?>
                            </span>
                            <?php if ($isOwner): ?>
                                <a class="btn btn--soft" href="<?= e(url('auteur/edit', ['id' => $book['id']])) ?>">
                                    <svg class="icon"><use href="#i-edit"/></svg>
                                    <?= $book['statut'] === \App\Models\Book::STATUT_REFUSE ? 'Corriger et resoumettre' : 'Gérer mon livre' ?>
                                </a>
                            <?php elseif ($isAdmin): ?>
                                <a class="btn btn--soft" href="<?= e(url('admin/books')) ?>">
                                    <svg class="icon"><use href="#i-flag"/></svg>
                                    Modérer le livre
                                </a>
                            <?php endif; ?>
                        <?php elseif ($currentUser === null): ?>
                            <p class="inline-status inline-status--warn" role="status">
                                <svg class="icon"><use href="#i-user"/></svg>
                                Connectez-vous pour emprunter ce livre.
                            </p>
                            <a class="btn btn--brand btn--lg" href="<?= e(url('login')) ?>">
                                <span class="btn-label">
                                    <svg class="icon"><use href="#i-arrow"/></svg>
                                    Se connecter pour emprunter
                                </span>
                            </a>
                        <?php elseif ($myActiveLoan !== null): ?>
                            <span class="inline-status" role="status">
                                <svg class="icon"><use href="#i-check"/></svg>
                                Vous avez emprunté ce livre — à rendre le
                                <?= e((new DateTimeImmutable($myActiveLoan['date_retour_prevue']))->format('d/m/Y')) ?>.
                            </span>
                            <a class="btn btn--soft" href="<?= e(url('profile')) ?>">
                                <svg class="icon"><use href="#i-return"/></svg>
                                Gérer dans mon espace
                            </a>
                        <?php elseif ($canBorrow): ?>
                            <form action="<?= e(url('loan/borrow')) ?>" method="post" data-busy>
                                <?= \App\Core\Csrf::field() ?>
                                <input type="hidden" name="id_livre" value="<?= (int) $book['id'] ?>">
                                <button type="submit" class="btn btn--brand btn--lg">
                                    <span class="btn-label">
                                        <svg class="icon"><use href="#i-book"/></svg>
                                        Emprunter ce livre
                                    </span>
                                    <span class="spinner" aria-hidden="true"></span>
                                </button>
                            </form>
                            <p class="add-hint">
                                <svg class="icon"><use href="#i-cal"/></svg>
                                Rendu au plus tard dans 14 jours.
                            </p>
                        <?php else: ?>
                            <span class="inline-status inline-status--warn" role="status">
                                <svg class="icon"><use href="#i-alert"/></svg>
                                Tous les exemplaires sont actuellement empruntés.
                            </span>
                        <?php endif; ?>
                    </div>

                    <a class="back-link" href="<?= e(url('')) ?>">
                        <svg class="icon"><use href="#i-chev-l"/></svg>
                        Retour au catalogue
                    </a>
                </div>
            </div>
        </article>

        <!-- ===== Avis des lecteurs ===== -->
        <section id="avis" class="reviews reveal">
            <div class="reviews-head">
                <h2 class="reviews-title">Avis des lecteurs</h2>
                <?php if ($average['count'] > 0): ?>
                    <span class="reviews-summary">
                        <?php $stars = $average['avg']; ?>
                        <?php require VIEWS_PATH . '/partials/stars.php'; ?>
                        <?= number_format($average['avg'], 1, ',', ' ') ?> sur 5 · <?= $average['count'] ?> avis
                    </span>
                <?php endif; ?>
            </div>

            <?php if ($currentUser !== null && $published): ?>
                <form action="<?= e(url('review/create')) ?>" method="post" class="review-form form-card" novalidate>
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="id_livre" value="<?= (int) $book['id'] ?>">

                    <div class="star-input" data-star-input>
                        <span class="visually-hidden">Votre note</span>
                        <div class="star-input-icons" role="radiogroup" aria-label="Votre note">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <label class="star-input-label" title="<?= $i ?> étoile<?= $i > 1 ? 's' : '' ?>">
                                    <input type="radio" name="note" value="<?= $i ?>"
                                        <?= isset($myReview) && (int) $myReview['note'] === $i ? 'checked' : '' ?>>
                                    <svg class="icon"><use href="#i-star"/></svg>
                                </label>
                            <?php endfor; ?>
                        </div>
                        <span class="star-input-value" data-star-value>
                            <?= isset($myReview) ? (int) $myReview['note'] . ' étoile(s)' : 'Cliquez pour noter' ?>
                        </span>
                    </div>

                    <div class="form-group form-group--full">
                        <label class="form-label" for="f_commentaire">
                            <?= isset($myReview) ? 'Modifier mon commentaire' : 'Votre commentaire' ?>
                        </label>
                        <textarea class="form-control" id="f_commentaire" name="commentaire" rows="4" required
                                  minlength="3" maxlength="1000"
                                  placeholder="Votre avis sur ce livre…"><?= isset($myReview) ? e($myReview['commentaire']) : '' ?></textarea>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn--brand">
                            <svg class="icon"><use href="#i-star"/></svg>
                            <?= isset($myReview) ? 'Mettre à jour mon avis' : 'Publier mon avis' ?>
                        </button>
                    </div>
                </form>

                <?php if (isset($myReview)): ?>
                    <form action="<?= e(url('review/delete')) ?>" method="post" class="review-delete-form">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int) $myReview['review_id'] ?>">
                        <input type="hidden" name="id_livre" value="<?= (int) $book['id'] ?>">
                        <button type="submit" class="btn btn--soft btn--sm" data-confirm="Supprimer cet avis ?">
                            <svg class="icon"><use href="#i-trash"/></svg>
                            Supprimer mon avis
                        </button>
                    </form>
                <?php endif; ?>
            <?php elseif (!$published): ?>
                <p class="muted">Les avis s'ouvriront une fois ce livre publié dans le catalogue.</p>
            <?php else: ?>
                <p class="reviews-login">
                    <a href="<?= e(url('login')) ?>">Connectez-vous</a> pour laisser une note et un avis.
                </p>
            <?php endif; ?>

            <?php if ($reviews === []): ?>
                <p class="muted">Aucun avis pour le moment. Soyez le premier à partager votre lecture !</p>
            <?php else: ?>
                <ul class="review-grid">
                    <?php foreach ($reviews as $review): ?>
                        <li class="review-card reveal">
                            <div class="review-head">
                                <div>
                                    <span class="review-author">
                                        <?= e($review['user_prenom'] . ' ' . mb_strtoupper($review['user_nom'])) ?>
                                    </span>
                                    <?php $stars = (float) $review['note']; ?>
                                    <?php require VIEWS_PATH . '/partials/stars.php'; ?>
                                </div>
                                <time class="review-date">
                                    <?= e((new DateTimeImmutable($review['date_publication']))->format('d/m/Y')) ?>
                                </time>
                            </div>
                            <p class="review-text"><?= nl2br(e($review['commentaire'])) ?></p>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </div>
</section>