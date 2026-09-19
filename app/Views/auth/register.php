<?php /** Vue : inscription */ ?>

<section class="auth-wrap">
    <div class="container">
        <div class="auth-card">
            <span class="auth-mark" aria-hidden="true"><svg class="icon"><use href="#i-library"/></svg></span>
            <h1 class="auth-title">Créer un compte</h1>
            <p class="auth-sub">Rejoignez la bibliothèque pour emprunter et partager vos avis.</p>

            <form action="<?= e(url('inscription')) ?>" method="post" class="form-card" novalidate>
                <?= \App\Core\Csrf::field() ?>
                <div class="form-grid">
                    <div class="form-group <?= isset($errors['prenom']) ? 'has-error' : '' ?>">
                        <label class="form-label" for="f_prenom">Prénom <span class="req">*</span></label>
                        <input class="form-control" type="text" id="f_prenom" name="prenom" maxlength="100" required
                               autocomplete="given-name" value="<?= e($old['prenom']) ?>">
                        <?php if (isset($errors['prenom'])): ?>
                            <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['prenom']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($errors['nom']) ? 'has-error' : '' ?>">
                        <label class="form-label" for="f_nom">Nom <span class="req">*</span></label>
                        <input class="form-control" type="text" id="f_nom" name="nom" maxlength="100" required
                               autocomplete="family-name" value="<?= e($old['nom']) ?>">
                        <?php if (isset($errors['nom'])): ?>
                            <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['nom']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group form-group--full <?= isset($errors['email']) ? 'has-error' : '' ?>">
                        <label class="form-label" for="f_email">Adresse email <span class="req">*</span></label>
                        <input class="form-control" type="email" id="f_email" name="email" maxlength="191" required
                               autocomplete="email" value="<?= e($old['email']) ?>"
                               placeholder="vous@exemple.com">
                        <?php if (isset($errors['email'])): ?>
                            <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['email']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($errors['mot_de_passe']) ? 'has-error' : '' ?>">
                        <label class="form-label" for="f_password">Mot de passe <span class="req">*</span></label>
                        <input class="form-control" type="password" id="f_password" name="mot_de_passe" required
                               autocomplete="new-password" minlength="8">
                        <small class="form-hint">8 caractères minimum.</small>
                        <?php if (isset($errors['mot_de_passe'])): ?>
                            <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['mot_de_passe']) ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="form-group <?= isset($errors['mot_de_passe_confirm']) ? 'has-error' : '' ?>">
                        <label class="form-label" for="f_password2">Confirmer le mot de passe <span class="req">*</span></label>
                        <input class="form-control" type="password" id="f_password2" name="mot_de_passe_confirm" required
                               autocomplete="new-password" minlength="8">
                        <?php if (isset($errors['mot_de_passe_confirm'])): ?>
                            <p class="field-error" role="alert"><svg class="icon"><use href="#i-alert"/></svg> <?= e($errors['mot_de_passe_confirm']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn--brand btn--lg">
                        <svg class="icon"><use href="#i-check"/></svg>
                        Créer mon compte
                    </button>
                </div>
            </form>

            <p class="auth-foot">
                Déjà inscrit ?
                <a href="<?= e(url('login')) ?>">Se connecter</a>
            </p>
        </div>
    </div>
</section>