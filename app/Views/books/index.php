<?php /** Vue accueil : catalogue + recherche temps réel */ ?>

<!-- ================= Hero & recherche ================= -->
<section class="hero" aria-labelledby="hero-titre">
    <div class="container hero-inner">
        <span class="hero-eyebrow"><svg class="icon"><use href="#i-spark"/></svg> Lecture &amp; partage</span>
        <h1 class="hero-title" id="hero-titre">Votre bibliothèque,<br>réinventée.</h1>
        <p class="hero-text">Explorez un catalogue soigné, recherchez en un instant et constituez votre liste de lecture préférée.</p>

        <div class="search-panel">
            <form class="search-field" role="search" action="<?= e(url('')) ?>" method="get" data-search-form>
                <svg class="icon"><use href="#i-search"/></svg>
                <label class="sr-only" for="search-input">Rechercher un livre par titre ou auteur</label>
                <input
                    type="search"
                    id="search-input"
                    class="search-input"
                    name="q"
                    value="<?= e($search) ?>"
                    placeholder="Rechercher un titre, un auteur…"
                    autocomplete="off"
                    aria-controls="search-suggest"
                    aria-expanded="false"
                    data-search-input
                >
                <button type="submit" class="search-btn">Rechercher</button>
            </form>

            <!-- Suggestions AJAX -->
            <div class="search-suggest" id="search-suggest" data-search-results hidden></div>

            <!-- Filtres rapides (filtrent la page affichée) -->
            <div class="filter-group" role="group" aria-label="Filtrer les résultats affichés">
                <span class="filter-hint"><svg class="icon" style="width:15px;height:15px;vertical-align:-2px"><use href="#i-filter"/></svg> Filtrer :</span>
                <div class="chips">
                    <button type="button" class="chip is-active" data-filter="all" aria-pressed="true">Tout</button>
                    <button type="button" class="chip" data-filter="titre" aria-pressed="false">Titre</button>
                    <button type="button" class="chip" data-filter="auteur" aria-pressed="false">Auteur</button>
                    <button type="button" class="chip" data-filter="edition" aria-pressed="false">Éditeur</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ================= Catalogue ================= -->
<section class="container catalog" aria-label="Catalogue des livres">
    <div class="section-head reveal">
        <h2 class="section-title">Explorer le <em>catalogue</em></h2>
        <span class="result-count" data-result-count><?= number_format($total) ?> livre<?= $total > 1 ? 's' : '' ?></span>
    </div>

    <?php if (empty($books)): ?>
        <div class="empty-state">
            <span class="empty-icon"><svg class="icon"><use href="#i-search"/></svg></span>
            <h2>Aucun résultat</h2>
            <p>Aucun livre ne correspond<?= $search !== '' ? ' à « ' . e($search) . ' »' : '' ?>.</p>
            <a class="btn btn--brand" href="<?= e(url('')) ?>">Réinitialiser la recherche</a>
        </div>
    <?php else: ?>
        <div class="book-grid reveal-stagger" data-book-grid>
            <?php foreach ($books as $book): ?>
                <article class="book-card" style="--h: <?= ($book['id'] * 47) % 360 ?>"
                         data-book-card
                         data-title="<?= e(mb_strtolower($book['titre'])) ?>"
                         data-author="<?= e(mb_strtolower($book['auteur'])) ?>"
                         data-edition="<?= e(mb_strtolower($book['maison_edition'])) ?>">

                    <a class="book-cover" href="<?= e(url('book/show', ['id' => $book['id']])) ?>" tabindex="-1" aria-hidden="true">
                        <?php if (!empty($book['couverture']) && is_file(UPLOADS_PATH . '/' . $book['couverture'])): ?>
                            <img src="<?= e(APP_BASE_URL) ?>/uploads/<?= e($book['couverture']) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="cover-init"><?= e(mb_strtoupper(mb_substr($book['titre'], 0, 1))) ?></span>
                        <?php endif; ?>
                    </a>

                    <div class="book-card-body">
                        <h3 class="book-title">
                            <a href="<?= e(url('book/show', ['id' => $book['id']])) ?>"><?= e($book['titre']) ?></a>
                        </h3>
                        <p class="book-author"><?= e($book['auteur']) ?></p>
                        <p class="book-edition"><svg class="icon"><use href="#i-library"/></svg> <?= e($book['maison_edition']) ?></p>

                        <div class="book-foot">
                            <?php if ($book['nombre_exemplaire'] > 0): ?>
                                <?php $badge = $book['nombre_exemplaire'] === 1 ? 'low' : 'ok'; ?>
                                <span class="badge badge--<?= $badge ?>">
                                    <svg class="icon"><use href="#i-check"/></svg>
                                    <?= $book['nombre_exemplaire'] === 1 ? 'Dernier exemplaire' : $book['nombre_exemplaire'] . ' disponibles' ?>
                                </span>
                            <?php else: ?>
                                <span class="badge badge--out"><svg class="icon"><use href="#i-alert"/></svg> Rupture</span>
                            <?php endif; ?>
                            <span class="card-arrow" aria-hidden="true"><svg class="icon"><use href="#i-arrow"/></svg></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($pages > 1): ?>
            <nav class="pagination" aria-label="Pagination des résultats">
                <ul class="pagination-list">
                    <?php if ($page > 1): ?>
                        <li>
                            <a class="page-link page-arrow" href="<?= e(url('', array_filter(['q' => $search, 'pageNum' => $page - 1]))) ?>" rel="prev" aria-label="Page précédente">
                                <svg class="icon"><use href="#i-chev-l"/></svg>
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <li>
                            <?php if ($i === $page): ?>
                                <span class="page-current" aria-current="page"><?= $i ?></span>
                            <?php else: ?>
                                <a class="page-link" href="<?= e(url('', array_filter(['q' => $search, 'pageNum' => $i]))) ?>"><?= $i ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $pages): ?>
                        <li>
                            <a class="page-link page-arrow" href="<?= e(url('', array_filter(['q' => $search, 'pageNum' => $page + 1]))) ?>" rel="next" aria-label="Page suivante">
                                <svg class="icon"><use href="#i-chev-r"/></svg>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>