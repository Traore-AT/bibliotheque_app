<?php
/**
 * Vue : formulaire d'ajout (mode=create) / d'édition (mode=edit).
 * Page autonome de repli (fonctionne sans JavaScript) ;
 * le panneau latéral de l'admin prend le relais côté UX.
 */
$isCreate = $mode === 'create';
?>

<section class="container page-inner">
    <nav aria-label="Fil d'ariane" class="breadcrumb">
        <a href="<?= e(url('admin')) ?>">Administration</a>
        <span class="sep" aria-hidden="true">/</span>
        <span aria-current="page"><?= e($title) ?></span>
    </nav>

    <div class="page-head">
        <div>
            <h1><?= e($title) ?></h1>
            <p class="head-meta">
                <?= $isCreate ? 'Ajoutez un nouveau livre au catalogue.' : 'Mettez à jour les informations du livre.' ?>
            </p>
        </div>
        <a class="btn btn--soft" href="<?= e(url('admin')) ?>">
            <svg class="icon"><use href="#i-chev-l"/></svg> Retour
        </a>
    </div>

    <form action="<?= e($isCreate ? url('admin/create') : url('admin/edit', ['id' => $book['id'] ?? 0])) ?>"
          method="post" class="form-card" enctype="multipart/form-data" novalidate>
        <?= \App\Core\Csrf::field() ?>
        <div class="form-grid">
            <?php require VIEWS_PATH . '/admin/_form_fields.php'; ?>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn--brand btn--lg">
                <svg class="icon"><use href="#i-check"/></svg>
                <?= $isCreate ? 'Enregistrer le livre' : 'Enregistrer les modifications' ?>
            </button>
            <a class="btn btn--soft" href="<?= e(url('admin')) ?>">Annuler</a>
        </div>
    </form>
</section>