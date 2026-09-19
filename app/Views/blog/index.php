<div class="blog-page">
    <!-- ===== Hero du Blog ===== -->
    <section class="blog-hero">
        <div class="container blog-hero-inner reveal">
            <div class="blog-hero-content">
                <span class="badge badge--brand-lg">
                    <svg class="icon"><use href="#i-newspaper"/></svg> Espace Blog &amp; Chroniques
                </span>
                <h1 class="blog-hero-title">Le Journal de la Bibliothèque &amp; Récits d'Apprentissage</h1>
                <p class="blog-hero-desc">
                    Découvrez nos articles littéraires, nos retours d'expériences technologiques, nos actualités culturelles et les coulisses de nos projets numériques.
                </p>
                <div class="blog-hero-actions">
                    <a href="<?= e(url('blog/create')) ?>" class="btn btn--brand">
                        <svg class="icon"><use href="#i-plus"/></svg> Rédiger un article
                    </a>
                    <a href="#hommage-dclic" class="btn btn--soft">
                        <svg class="icon"><use href="#i-award"/></svg> Découvrir l'Hommage D-CLIC
                    </a>
                </div>
            </div>
            <div class="blog-hero-stats">
                <div class="stat-pill">
                    <span class="stat-num"><?= count($articles) ?></span>
                    <span class="stat-lbl">Articles publiés</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-num">6</span>
                    <span class="stat-lbl">Semaines D-CLIC</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-num">100%</span>
                    <span class="stat-lbl">Passion &amp; Partage</span>
                </div>
            </div>
        </div>
    </section>

    <div class="container blog-body">
        <!-- ===== SECTION SPECIALE : TRIBUNE & HOMMAGE D-CLIC ===== -->
        <section id="hommage-dclic" class="dclic-showcase reveal">
            <div class="dclic-card">
                <div class="dclic-top-header">
                    <div class="dclic-badge-wrap">
                        <span class="dclic-badge">
                            <svg class="icon"><use href="#i-award"/></svg> PROGRAMME D-CLIC • NIVEAU INTERMÉDIAIRE
                        </span>
                        <span class="dclic-duration">
                            <svg class="icon"><use href="#i-clock"/></svg> 6 Semaines Intensives
                        </span>
                    </div>
                    <div class="dclic-partner-logo-box">
                        <img src="<?= e(APP_BASE_URL) ?>/img/OIF_LOGO-BLOC%20MARQUE%20OIF_CMJN.jpg" alt="Logo OIF / D-CLIC - Organisation Internationale de la Francophonie" class="dclic-section-logo">
                    </div>
                </div>

                <div class="dclic-grid">
                    <div class="dclic-text-col">
                        <h2 class="dclic-title">
                            Un Tremplin d'Excellence : Hommage au Programme D-CLIC et à Notre Tuteur
                        </h2>
                        
                        <div class="dclic-lead">
                            « Ce projet de fin de formation concrétise l'ensemble des compétences acquises et symbolise une formidable aventure humaine et technique. »
                        </div>

                        <div class="dclic-paragraphs">
                            <p>
                                Je tiens à exprimer ma plus sincère reconnaissance et mes profonds remerciements au <strong>programme D-CLIC</strong> de m'avoir accordé cette opportunité inestimable de suivre la <strong>formation de niveau intermédiaire en développement web</strong>. Ce programme d'immersion a été une passerelle décisive pour structurer mes acquis et élever mes compétences aux standards du développement logiciel moderne.
                            </p>
                            <p>
                                Un hommage tout particulier et chaleureux est dédié à <strong>mon tuteur et coach</strong>, qui nous a encadrés, guidés et motivés avec une pédagogie bienveillante, une rigueur sans faille et une patience remarquable tout au long de ces <strong>6 semaines intenses</strong>. Ses conseils avisés, ses revues de code et sa disponibilité constante ont constitué une source d'inspiration déterminante.
                            </p>
                            <p>
                                La conception et le développement de cette <strong>Bibliothèque Numérique</strong> (architecture MVC propre, requêtes PDO sécurisées, contrôle strict des emprunts, espace auteur, interface UI/UX soignée et responsive) représentent la <em>concrétisation vivante</em> de tout ce que j'ai appris. C'est le témoignage tangible d'une montée en compétences réussie et durable.
                            </p>
                        </div>

                        <div class="dclic-meta-footer">
                            <div class="dclic-author-info">
                                <div class="dclic-avatar">TA</div>
                                <div>
                                    <div class="dclic-author-name">Traoré Alseny</div>
                                    <div class="dclic-author-role">Bénéficiaire D-CLIC • Développeur Web</div>
                                </div>
                            </div>
                            <?php if ($featured !== null): ?>
                                <a href="<?= e(url('blog/show', ['id' => $featured['id']])) ?>" class="btn btn--dclic">
                                    <span>Lire la tribune complète</span>
                                    <svg class="icon"><use href="#i-arrow"/></svg>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="dclic-features-col">
                        <div class="dclic-highlight-box">
                            <div class="dh-item">
                                <div class="dh-icon"><svg class="icon"><use href="#i-spark"/></svg></div>
                                <div>
                                    <h4>6 Semaines d'Immersion</h4>
                                    <p>Ateliers intensifs, algorithmique avancée et gestion de projet web.</p>
                                </div>
                            </div>
                            <div class="dh-item">
                                <div class="dh-icon"><svg class="icon"><use href="#i-library"/></svg></div>
                                <div>
                                    <h4>Architecture MVC &amp; PHP 8</h4>
                                    <p>Front controller, POO avancée, CSRF, PDO et principes Clean Code.</p>
                                </div>
                            </div>
                            <div class="dh-item">
                                <div class="dh-icon"><svg class="icon"><use href="#i-user"/></svg></div>
                                <div>
                                    <h4>Mentorat &amp; Coaching</h4>
                                    <p>Suivi personnalisé, revues techniques et partage de bonnes pratiques.</p>
                                </div>
                            </div>
                            <div class="dh-item">
                                <div class="dh-icon"><svg class="icon"><use href="#i-check"/></svg></div>
                                <div>
                                    <h4>Projet Final Concrétisé</h4>
                                    <p>Plateforme complète, sécurisée, interactive et déployée.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ===== Barre de recherche et filtres de catégories ===== -->
        <section class="blog-controls reveal">
            <div class="blog-filter-tabs">
                <a href="<?= e(url('blog', ['categorie' => 'tous', 'q' => $searchQuery])) ?>" 
                   class="blog-tab <?= ($selectedCat === 'tous' || $selectedCat === '') ? 'is-active' : '' ?>">
                    Tous les articles
                </a>
                <?php foreach ($categories as $cat): ?>
                    <a href="<?= e(url('blog', ['categorie' => $cat, 'q' => $searchQuery])) ?>" 
                       class="blog-tab <?= $selectedCat === $cat ? 'is-active' : '' ?>">
                        <?= e($cat) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <form class="blog-search-form" action="<?= e(url('blog')) ?>" method="get">
                <input type="hidden" name="page" value="blog">
                <?php if ($selectedCat !== 'tous'): ?>
                    <input type="hidden" name="categorie" value="<?= e($selectedCat) ?>">
                <?php endif; ?>
                <div class="blog-search-input-wrap">
                    <svg class="icon blog-search-icon"><use href="#i-search"/></svg>
                    <input type="search" name="q" value="<?= e($searchQuery) ?>" placeholder="Rechercher un article ou un mot-clé..." class="blog-search-input" aria-label="Rechercher dans les articles">
                    <?php if ($searchQuery !== ''): ?>
                        <a href="<?= e(url('blog', ['categorie' => $selectedCat])) ?>" class="blog-search-clear" title="Effacer la recherche">
                            <svg class="icon"><use href="#i-close"/></svg>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- ===== Grille des articles ===== -->
        <?php if (empty($articles)): ?>
            <div class="blog-empty-state reveal">
                <span class="empty-icon"><svg class="icon"><use href="#i-newspaper"/></svg></span>
                <h3>Aucun article trouvé</h3>
                <p>Aucun article ne correspond à votre recherche ou catégorie sélectionnée.</p>
                <div class="empty-actions">
                    <a href="<?= e(url('blog')) ?>" class="btn btn--soft">Réinitialiser les filtres</a>
                    <a href="<?= e(url('blog/create')) ?>" class="btn btn--brand">Rédiger le premier article</a>
                </div>
            </div>
        <?php else: ?>
            <div class="blog-grid reveal-stagger">
                <?php foreach ($articles as $art): ?>
                    <?php
                        $coverUrl = !empty($art['image']) ? \App\Core\CoverUploader::url($art['image']) : '';
                        $isDclic = str_contains(mb_strtolower($art['categorie']), 'd-clic') || str_contains(mb_strtolower($art['titre']), 'd-clic');
                    ?>
                    <article class="blog-card <?= !empty($art['epingle']) ? 'blog-card--featured' : '' ?>">
                        <div class="blog-card-media">
                            <?php if ($coverUrl !== ''): ?>
                                <img src="<?= e($coverUrl) ?>" alt="<?= e($art['titre']) ?>" class="blog-card-img" loading="lazy">
                            <?php else: ?>
                                <div class="blog-card-placeholder <?= $isDclic ? 'placeholder--dclic' : '' ?>">
                                    <svg class="icon placeholder-icon"><use href="<?= $isDclic ? '#i-award' : '#i-newspaper' ?>"/></svg>
                                    <span class="placeholder-cat"><?= e($art['categorie']) ?></span>
                                </div>
                            <?php endif; ?>
                            
                            <span class="blog-card-badge"><?= e($art['categorie']) ?></span>
                        </div>

                        <div class="blog-card-content">
                            <div class="blog-card-meta">
                                <span class="meta-date"><svg class="icon"><use href="#i-cal"/></svg> <?= e($art['date_courte'] ?? '') ?></span>
                                <span class="meta-read"><svg class="icon"><use href="#i-clock"/></svg> <?= e($art['temps_lecture'] ?? '3 min') ?></span>
                                <span class="meta-views"><svg class="icon"><use href="#i-eye"/></svg> <?= (int) ($art['vues'] ?? 0) ?> vues</span>
                            </div>

                            <h2 class="blog-card-title">
                                <a href="<?= e(url('blog/show', ['id' => $art['id']])) ?>">
                                    <?= e($art['titre']) ?>
                                </a>
                            </h2>

                            <p class="blog-card-excerpt">
                                <?= e(mb_strimwidth((string)$art['chapeau'], 0, 160, '...')) ?>
                            </p>

                            <div class="blog-card-footer">
                                <div class="blog-card-author">
                                    <span class="author-avatar"><?= strtoupper(mb_substr((string)$art['auteur_nom'], 0, 2)) ?></span>
                                    <span class="author-name"><?= e($art['auteur_nom']) ?></span>
                                </div>
                                <a href="<?= e(url('blog/show', ['id' => $art['id']])) ?>" class="blog-card-link" aria-label="Lire l'article <?= e($art['titre']) ?>">
                                    <span>Lire</span>
                                    <svg class="icon"><use href="#i-arrow"/></svg>
                                </a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- ===== Bannière CTA vers la rédaction ===== -->
        <section class="blog-cta-banner reveal">
            <div class="blog-cta-box">
                <div class="blog-cta-text">
                    <span class="cta-subtitle">Une réflexion, une critique de livre ou un tutoriel ?</span>
                    <h3>Partagez votre article avec la communauté des lecteurs</h3>
                    <p>Contribuez au blog de la Bibliothèque Numérique en partageant vos découvertes littéraires et vos projets.</p>
                </div>
                <a href="<?= e(url('blog/create')) ?>" class="btn btn--brand btn--lg">
                    <svg class="icon"><use href="#i-pen"/></svg> Écrire un article avec photo
                </a>
            </div>
        </section>
    </div>
</div>
