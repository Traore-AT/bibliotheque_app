<?php
/**
 * Partielle : champs du formulaire livre (réutilisée par le panneau latéral
 * de l'admin et la page autonome admin/form.php).
 * Attend $book (array des valeurs) et $errors (array des erreurs).
 */
$errors = $errors ?? [];
$book   = $book ?? [];
?>

<div class="form-group <?= isset($errors['titre']) ? 'has-error' : '' ?>">
    <label class="form-label" for="f_titre">Titre <span class="req">*</span></label>
    <input class="form-control" type="text" id="f_titre" name="titre" maxlength="100" required
           value="<?= e($book['titre'] ?? '') ?>">
    <?php if (isset($errors['titre'])): ?>
        <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['titre']) ?></p>
    <?php endif; ?>
</div>

<div class="form-group <?= isset($errors['auteur']) ? 'has-error' : '' ?>">
    <label class="form-label" for="f_auteur">Auteur <span class="req">*</span></label>
    <input class="form-control" type="text" id="f_auteur" name="auteur" maxlength="100" required
           value="<?= e($book['auteur'] ?? '') ?>">
    <?php if (isset($errors['auteur'])): ?>
        <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['auteur']) ?></p>
    <?php endif; ?>
</div>

<div class="form-group <?= isset($errors['maison_edition']) ? 'has-error' : '' ?>">
    <label class="form-label" for="f_maison">Maison d'édition <span class="req">*</span></label>
    <input class="form-control" type="text" id="f_maison" name="maison_edition" maxlength="100" required
           value="<?= e($book['maison_edition'] ?? '') ?>">
    <?php if (isset($errors['maison_edition'])): ?>
        <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['maison_edition']) ?></p>
    <?php endif; ?>
</div>

<div class="form-group">
    <label class="form-label" for="f_exemplaires">Nombre d'exemplaires</label>
    <input class="form-control" type="number" id="f_exemplaires" name="nombre_exemplaire" min="0" max="9999"
           value="<?= (int) ($book['nombre_exemplaire'] ?? 1) ?>">
    <small class="form-hint">Indiquez le nombre d'exemplaires disponibles (0 = en rupture).</small>
</div>

<div class="form-group form-group--full <?= isset($errors['description']) ? 'has-error' : '' ?>">
    <label class="form-label" for="f_description">Description <span class="req">*</span></label>
    <textarea class="form-control" id="f_description" name="description" rows="6" required><?= e($book['description'] ?? '') ?></textarea>
    <?php if (isset($errors['description'])): ?>
        <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['description']) ?></p>
    <?php endif; ?>
</div>

<?php
$coverFile = $book['couverture'] ?? null;
$coverUrl  = \App\Core\CoverUploader::url($coverFile);
?>

<div class="form-group form-group--full upload-group <?= isset($errors['couverture']) ? 'has-error' : '' ?>" data-upload-group>
    <span class="form-label" id="lg_couverture">Couverture du livre</span>

    <div class="upload-zone" data-upload-zone>
        <input class="visually-hidden" type="file" id="f_couverture" name="couverture"
               accept="image/jpeg,image/png,image/gif,image/webp" data-upload-input aria-describedby="lg_couverture">

        <div class="upload-state" data-upload-state>
            <img class="upload-preview <?= $coverUrl !== '' ? 'is-visible' : '' ?>"
                 data-upload-preview src="<?= e($coverUrl) ?>" alt="Aperçu de la couverture">
            <span class="upload-placeholder <?= $coverUrl === '' ? 'is-visible' : '' ?>" data-upload-placeholder>
                <svg class="icon"><use href="#i-image"/></svg>
            </span>
        </div>

        <div class="upload-actions">
            <label class="btn btn--soft btn--sm" for="f_couverture" data-upload-label role="button" tabindex="0">
                <svg class="icon"><use href="#i-plus"/></svg>
                <span data-upload-label-text><?= $coverUrl !== '' ? 'Remplacer l\'image' : 'Choisir une image' ?></span>
            </label>
            <button type="button" class="btn btn--soft btn--sm" data-upload-remove hidden>
                <svg class="icon"><use href="#i-close"/></svg>
                <span>Annuler la sélection</span>
            </button>
        </div>

        <small class="form-hint" data-upload-hint>JPG, PNG, GIF ou WebP — 2 Mo maximum.</small>
        <small class="upload-filename" data-upload-filename></small>
    </div>

    <label class="check-line" for="f_suppr_cover" data-upload-flag-wrap <?= $coverUrl === '' ? 'hidden' : '' ?>>
        <input type="checkbox" id="f_suppr_cover" name="supprimer_couverture" value="1" data-upload-remove-flag>
        <span>Supprimer la couverture actuelle</span>
    </label>

    <?php if (isset($errors['couverture'])): ?>
        <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['couverture']) ?></p>
    <?php endif; ?>
</div>