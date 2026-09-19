<?php
    $coverUrl = !empty($article['image']) ? \App\Core\CoverUploader::url($article['image']) : '';
    $isDclic = str_contains(mb_strtolower($article['categorie']), 'd-clic') || str_contains(mb_strtolower($article['titre']), 'd-clic');

    // Simple markdown-to-html helper for formatted blog posts
    $contentHtml = e($article['contenu']);
    // Headings ##
    $contentHtml = preg_replace('/^## (.*)$/m', '<h2 class="post-h2">$1</h2>', $contentHtml);
    // Headings ###
    $contentHtml = preg_replace('/^### (.*)$/m', '<h3 class="post-h3">$1</h3>', $contentHtml);
    // Bold **text**
    $contentHtml = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $contentHtml);
    // Italic *text*
    $contentHtml = preg_replace('/(?<!\*)\*(?!\*)(.*?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $contentHtml);
    // Bullet lists - item
    $contentHtml = preg_replace('/^[•\-\*] (.*)$/m', '<li class="post-li">$1</li>', $contentHtml);
    $contentHtml = preg_replace('/(<li class="post-li">.*<\/li>(\n|$) )+/m', '<ul class="post-ul">$0</ul>', $contentHtml);
    // Paragraphs with nl2br
    $contentHtml = nl2br($contentHtml);
?>

<div class="article-single-page">
    <!-- ===== Fil d'ariane ===== -->
    <div class="container article-breadcrumb-wrap">
        <nav class="breadcrumb" aria-label="Fil d'Ariane">
            <a href="<?= e(url('')) ?>">Accueil</a>
            <span class="bc-sep">/</span>
            <a href="<?= e(url('blog')) ?>">Blog</a>
            <span class="bc-sep">/</span>
            <a href="<?= e(url('blog', ['categorie' => $article['categorie']])) ?>"><?= e($article['categorie']) ?></a>
            <span class="bc-sep">/</span>
            <span class="bc-current" aria-current="page"><?= e(mb_strimwidth($article['titre'], 0, 40, '...')) ?></span>
        </nav>
    </div>

    <!-- ===== Article Header ===== -->
    <header class="article-header">
        <div class="container article-header-inner reveal">
            <div class="article-meta-top">
                <span class="badge badge--brand"><?= e($article['categorie']) ?></span>
                <?php if ($isDclic): ?>
                    <span class="badge badge--dclic"><svg class="icon"><use href="#i-award"/></svg> Hommage D-CLIC</span>
                <?php endif; ?>
                <span class="article-readtime"><svg class="icon"><use href="#i-clock"/></svg> <?= e($article['temps_lecture'] ?? '4 min') ?> de lecture</span>
                <span class="article-views"><svg class="icon"><use href="#i-eye"/></svg> <?= (int) $article['vues'] ?> consultations</span>
            </div>

            <h1 class="article-main-title"><?= e($article['titre']) ?></h1>

            <div class="article-author-card">
                <div class="article-author-avatar">
                    <?= strtoupper(mb_substr((string)$article['auteur_nom'], 0, 2)) ?>
                </div>
                <div class="article-author-details">
                    <div class="author-name"><?= e($article['auteur_nom']) ?></div>
                    <div class="author-date">Publié le <?= e($article['date_formatee'] ?? date('d/m/Y')) ?></div>
                </div>
            </div>
        </div>
    </header>

    <!-- ===== Corps de l'article ===== -->
    <main class="container article-container">
        <article class="article-card-main reveal">
            <?php if ($coverUrl !== ''): ?>
                <div class="article-cover-frame">
                    <img src="<?= e($coverUrl) ?>" alt="<?= e($article['titre']) ?>" class="article-cover-img">
                </div>
            <?php elseif ($isDclic): ?>
                <div class="article-dclic-banner">
                    <div class="dclic-banner-logo-wrap">
                        <img src="<?= e(APP_BASE_URL) ?>/img/OIF_LOGO-BLOC%20MARQUE%20OIF_CMJN.jpg" alt="Logo OIF / D-CLIC - Organisation Internationale de la Francophonie" class="dclic-banner-logo">
                    </div>
                    <div class="dclic-banner-content">
                        <div class="dclic-banner-badge"><svg class="icon"><use href="#i-award"/></svg> PROGRAMME D-CLIC • 6 SEMAINES D'IMMERSION</div>
                        <div class="dclic-banner-title">Formation Niveau Intermédiaire en Développement Web</div>
                        <div class="dclic-banner-sub">Projet final : Conception &amp; Réalisation de la Bibliothèque Numérique</div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="article-body-content">
                <!-- Chapeau introductif -->
                <div class="article-lead">
                    <?= nl2br(e($article['chapeau'])) ?>
                </div>

                <hr class="article-divider">

                <!-- Contenu mis en forme -->
                <div class="article-prose">
                    <?= $contentHtml ?>
                </div>

                <!-- Boîte de remerciement spéciale si article D-CLIC -->
                <?php if ($isDclic): ?>
                    <div class="article-tribute-box">
                        <div class="atb-icon"><svg class="icon"><use href="#i-spark"/></svg></div>
                        <div class="atb-content">
                            <h4>Mention spéciale au Programme D-CLIC &amp; à l'équipe pédagogique</h4>
                            <p>
                                Ce projet reflète l'exigence et la bienveillance insufflées par notre formateur et tuteur pendant les 6 semaines d'apprentissage intensif. Un grand merci à l'Organisation Internationale de la Francophonie (OIF) pour ce tremplin vers l'autonomie et l'excellence numérique.
                            </p>
                            <div class="atb-tags">
                                <span>#DCLIC</span>
                                <span>#DeveloppementWeb</span>
                                <span>#PHP_MVC</span>
                                <span>#CleanCode</span>
                                <span>#FullStack</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Barre de partage & retour -->
                <div class="article-footer-actions">
                    <div class="share-label">
                        <svg class="icon"><use href="#i-share"/></svg> Partager cet article :
                    </div>
                    <div class="share-buttons">
                        <button type="button" class="btn-share" id="btn-copy-link" title="Copier le lien de l'article" data-copy-url="<?= e(url('blog/show', ['id' => $article['id']])) ?>">
                            <svg class="icon"><use href="#i-copy"/></svg> Copier le lien
                        </button>
                        <a href="https://api.whatsapp.com/send?text=<?= urlencode($article['titre'] . ' — ' . url('blog/show', ['id' => $article['id']])) ?>" target="_blank" rel="noopener noreferrer" class="btn-share btn-share--wa" title="Partager sur WhatsApp">
                            WhatsApp
                        </a>
                        <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode(url('blog/show', ['id' => $article['id']])) ?>" target="_blank" rel="noopener noreferrer" class="btn-share btn-share--li" title="Partager sur LinkedIn">
                            LinkedIn
                        </a>
                    </div>
                </div>
            </div>
        </article>

        <!-- ===== Auteur & Actions ===== -->
        <div class="article-author-box reveal">
            <div class="aab-avatar"><?= strtoupper(mb_substr((string)$article['auteur_nom'], 0, 2)) ?></div>
            <div class="aab-info">
                <h3>À propos de <?= e($article['auteur_nom']) ?></h3>
                <p>
                    Passionné par le développement web, les architectures logicielles robustes et la démocratisation des savoirs à travers les outils numériques modernes.
                </p>
            </div>
            <div class="aab-action">
                <a href="<?= e(url('blog')) ?>" class="btn btn--soft">
                    <svg class="icon"><use href="#i-newspaper"/></svg> Tous les articles
                </a>
            </div>
        </div>

        <!-- ===== Articles associés ===== -->
        <?php if (!empty($related)): ?>
            <section class="related-section reveal">
                <div class="section-head">
                    <h2 class="section-title">Articles dans la même thématique</h2>
                    <a href="<?= e(url('blog', ['categorie' => $article['categorie']])) ?>" class="section-link">Voir la catégorie &rarr;</a>
                </div>
                <div class="related-grid">
                    <?php foreach ($related as $rel): ?>
                        <div class="related-card">
                            <span class="related-cat"><?= e($rel['categorie']) ?></span>
                            <h3 class="related-title">
                                <a href="<?= e(url('blog/show', ['id' => $rel['id']])) ?>">
                                    <?= e($rel['titre']) ?>
                                </a>
                            </h3>
                            <div class="related-meta">
                                <span><?= e($rel['date_courte'] ?? '') ?></span> • <span><?= e($rel['temps_lecture'] ?? '3 min') ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>
</div>
