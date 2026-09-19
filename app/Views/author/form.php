<?php
/**
 * Vue : formulaire de dépôt (mode=create) / de modification (mode=edit)
 * d'un livre dans l'espace auteur.
 */
$isCreate = $mode === 'create';
?>

<section class="container page-inner">
    <nav aria-label="Fil d'ariane" class="breadcrumb">
        <a href="<?= e(url('auteur')) ?>">Espace auteur</a>
        <span class="sep" aria-hidden="true">/</span>
        <span aria-current="page"><?= e($title) ?></span>
    </nav>

    <div class="page-head">
        <div>
            <h1><?= e($title) ?></h1>
            <p class="head-meta">
                <?= $isCreate
                    ? 'Votre livre sera mis en ligne après validation par un administrateur.'
                    : 'Modifiez les informations : un livre refusé repassera automatiquement en attente d\'examen.' ?>
            </p>
        </div>
        <a class="btn btn--soft" href="<?= e(url('auteur')) ?>">
            <svg class="icon"><use href="#i-chev-l"/></svg> Retour
        </a>
    </div>

    <form action="<?= e($isCreate ? url('auteur/create') : url('auteur/edit', ['id' => $book['id'] ?? 0])) ?>"
          method="post" class="form-card" enctype="multipart/form-data" novalidate data-busy>
        <?= \App\Core\Csrf::field() ?>
        <div class="form-grid">
            <?php require VIEWS_PATH . '/admin/_form_fields.php'; ?>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn--brand btn--lg">
                <svg class="icon"><use href="#i-check"/></svg>
                <?= $isCreate ? 'Soumettre mon livre' : 'Enregistrer les modifications' ?>
                <span class="spinner" aria-hidden="true"></span>
            </button>
            <a class="btn btn--soft" href="<?= e(url('auteur')) ?>">Annuler</a>
        </div>
    </form>
</section>