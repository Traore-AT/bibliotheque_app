<?php /** Vue : connexion */ ?>

<section class="auth-wrap">
    <div class="container">
        <div class="auth-card">
            <span class="auth-mark" aria-hidden="true"><svg class="icon"><use href="#i-library"/></svg></span>
            <h1 class="auth-title">Connexion</h1>
            <p class="auth-sub">Accédez à votre espace personnel, empruntez et déposez vos avis.</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert--error" role="alert">
                    <svg class="icon"><use href="#i-alert"/></svg>
                    <?php if (isset($errors['identifiants'])): ?>
                        <span><?= e($errors['identifiants']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form action="<?= e(url('login')) ?>" method="post" class="form-card" novalidate>
                <?= \App\Core\Csrf::field() ?>
                <div class="form-grid">
                    <div class="form-group form-group--full">
                        <label class="form-label" for="f_email">Adresse email</label>
                        <input class="form-control" type="email" id="f_email" name="email" required
                               autocomplete="email" value="<?= e($old['email']) ?>"
                               placeholder="vous@exemple.com">
                    </div>
                    <div class="form-group form-group--full">
                        <label class="form-label" for="f_password">Mot de passe</label>
                        <input class="form-control" type="password" id="f_password" name="mot_de_passe" required
                               autocomplete="current-password" minlength="8">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn--brand btn--lg">
                        <svg class="icon"><use href="#i-arrow"/></svg>
                        Se connecter
                    </button>
                </div>
            </form>

            <p class="auth-foot">
                Pas encore de compte ?
                <a href="<?= e(url('inscription')) ?>">Créer un compte</a>
            </p>
        </div>
    </div>
</section>