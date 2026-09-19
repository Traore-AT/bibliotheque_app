<div class="container article-form-page">
    <nav class="breadcrumb" aria-label="Fil d'Ariane">
        <a href="<?= e(url('')) ?>">Accueil</a>
        <span class="bc-sep">/</span>
        <a href="<?= e(url('blog')) ?>">Blog</a>
        <span class="bc-sep">/</span>
        <span class="bc-current" aria-current="page">Rédiger un article</span>
    </nav>

    <div class="form-container-card reveal">
        <header class="form-header">
            <span class="form-badge">
                <svg class="icon"><use href="#i-pen"/></svg> Espace Rédaction
            </span>
            <h1 class="form-title">Publier un nouvel article sur le Blog</h1>
            <p class="form-desc">
                Partagez un retour d'expérience, une recommandation littéraire ou une analyse technique. Vous pouvez joindre une photo d'illustration pour valoriser votre texte.
            </p>
        </header>

        <form action="<?= e(url('blog/create')) ?>" method="post" enctype="multipart/form-data" class="blog-edit-form" novalidate>
            <?= csrf_field() ?>

            <!-- Ligne 1 : Titre & Catégorie -->
            <div class="form-row-2">
                <div class="form-group <?= isset($errors['titre']) ? 'has-error' : '' ?>">
                    <label for="titre" class="form-label">Titre de l'article <span class="req">*</span></label>
                    <input type="text" id="titre" name="titre" value="<?= e($data['titre'] ?? '') ?>" placeholder="Ex : 6 semaines pour devenir développeur web avec D-CLIC..." required class="form-input">
                    <?php if (isset($errors['titre'])): ?>
                        <span class="error-msg"><?= e($errors['titre']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group <?= isset($errors['categorie']) ? 'has-error' : '' ?>">
                    <label for="categorie" class="form-label">Catégorie thématique <span class="req">*</span></label>
                    <select id="categorie" name="categorie" class="form-select" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= e($cat) ?>" <?= ($data['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                                <?= e($cat) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Ligne 2 : Auteur & Photo -->
            <div class="form-row-2">
                <div class="form-group <?= isset($errors['auteur_nom']) ? 'has-error' : '' ?>">
                    <label for="auteur_nom" class="form-label">Nom de l'auteur / Signature <span class="req">*</span></label>
                    <input type="text" id="auteur_nom" name="auteur_nom" value="<?= e($data['auteur_nom'] ?? '') ?>" placeholder="Votre nom complet" required class="form-input">
                    <?php if (isset($errors['auteur_nom'])): ?>
                        <span class="error-msg"><?= e($errors['auteur_nom']) ?></span>
                    <?php endif; ?>
                </div>

                <!-- Zone d'upload photo avec prévisualisation dynamique -->
                <div class="form-group <?= isset($errors['photo']) ? 'has-error' : '' ?>">
                    <label for="photo-input" class="form-label">Photo d'illustration (optionnelle)</label>
                    <div class="photo-dropzone" id="photo-dropzone">
                        <input type="file" id="photo-input" name="photo" accept="image/jpeg,image/png,image/webp,image/gif" class="photo-file-input">
                        <div class="dropzone-empty" id="dropzone-empty">
                            <svg class="icon dropzone-icon"><use href="#i-image"/></svg>
                            <span class="dropzone-text"><strong>Cliquez pour choisir une photo</strong> ou glissez-déposez ici</span>
                            <span class="dropzone-hint">JPG, PNG, WebP jusqu'à 2 Mo</span>
                        </div>
                        <div class="dropzone-preview" id="dropzone-preview" style="display:none;">
                            <img id="preview-img" src="" alt="Aperçu de la photo" class="preview-thumb">
                            <div class="preview-meta">
                                <span id="preview-name" class="preview-filename">image.jpg</span>
                                <button type="button" id="btn-remove-photo" class="btn-remove-preview" title="Retirer l'image">
                                    <svg class="icon"><use href="#i-trash"/></svg> Supprimer
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php if (isset($errors['photo'])): ?>
                        <span class="error-msg"><?= e($errors['photo']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Chapeau introductif -->
            <div class="form-group <?= isset($errors['chapeau']) ? 'has-error' : '' ?>">
                <label for="chapeau" class="form-label">Résumé introductif (chapeau) <span class="req">*</span></label>
                <textarea id="chapeau" name="chapeau" rows="3" placeholder="Une courte synthèse accrocheuse de 2-3 phrases résumant l'essentiel de l'article..." required class="form-textarea"><?= e($data['chapeau'] ?? '') ?></textarea>
                <span class="form-help">Ce texte apparaîtra en avant-première sur les cartes du blog.</span>
                <?php if (isset($errors['chapeau'])): ?>
                    <span class="error-msg"><?= e($errors['chapeau']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Contenu complet -->
            <div class="form-group <?= isset($errors['contenu']) ? 'has-error' : '' ?>">
                <label for="contenu" class="form-label">Contenu intégral de l'article <span class="req">*</span></label>
                <textarea id="contenu" name="contenu" rows="12" placeholder="Développez votre idée, vos sections (utilisez ## pour les sous-titres, **texte** pour le gras, et - pour les listes)..." required class="form-textarea form-textarea--tall"><?= e($data['contenu'] ?? '') ?></textarea>
                <div class="form-help-markdown">
                    <span><strong>Mise en page rapide :</strong> <code>## Titre de section</code> &bull; <code>**Gras**</code> &bull; <code>*Italique*</code> &bull; <code>- Liste à puces</code></span>
                </div>
                <?php if (isset($errors['contenu'])): ?>
                    <span class="error-msg"><?= e($errors['contenu']) ?></span>
                <?php endif; ?>
            </div>

            <!-- Boutons de soumission -->
            <div class="form-actions-bar">
                <a href="<?= e(url('blog')) ?>" class="btn btn--soft">Annuler</a>
                <button type="submit" class="btn btn--brand btn--lg">
                    <svg class="icon"><use href="#i-send"/></svg> Publier l'article
                </button>
            </div>
        </form>
    </div>
</div>
