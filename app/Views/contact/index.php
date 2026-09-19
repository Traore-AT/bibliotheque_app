<div class="contact-page">
    <!-- ===== Hero de la page Contact ===== -->
    <section class="contact-hero">
        <div class="container contact-hero-inner reveal">
            <span class="badge badge--brand-lg">
                <svg class="icon"><use href="#i-mail"/></svg> Centre de Contact &amp; Support
            </span>
            <h1 class="contact-hero-title">Comment pouvons-nous vous aider ?</h1>
            <p class="contact-hero-desc">
                Une suggestion littéraire, une question sur notre catalogue, une demande liée au programme D-CLIC ou une assistance technique ? Écrivez-nous en toute simplicité.
            </p>
        </div>
    </section>

    <div class="container contact-container">
        <div class="contact-grid">
            <!-- ===== Colonne Gauche : Formulaire de Contact ===== -->
            <div class="contact-form-col reveal">
                <div class="contact-form-card">
                    <div class="cf-header">
                        <span class="cf-icon"><svg class="icon"><use href="#i-send"/></svg></span>
                        <div>
                            <h2 class="cf-title">Envoyez-nous un message</h2>
                            <p class="cf-sub">Nous répondons généralement sous 24 heures ouvrées.</p>
                        </div>
                    </div>

                    <form action="<?= e(url('contact')) ?>" method="post" class="contact-form" id="contact-form" novalidate>
                        <?= csrf_field() ?>

                        <div class="form-row-2">
                            <div class="form-group <?= isset($errors['nom']) ? 'has-error' : '' ?>">
                                <label for="contact-nom" class="form-label">Nom complet <span class="req">*</span></label>
                                <div class="input-with-icon">
                                    <svg class="icon input-ic"><use href="#i-user"/></svg>
                                    <input type="text" id="contact-nom" name="nom" value="<?= e($data['nom'] ?? '') ?>" placeholder="Ex : Alseny Traoré" required class="form-input">
                                </div>
                                <?php if (isset($errors['nom'])): ?>
                                    <span class="error-msg"><?= e($errors['nom']) ?></span>
                                <?php endif; ?>
                            </div>

                            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                                <label for="contact-email" class="form-label">Adresse email <span class="req">*</span></label>
                                <div class="input-with-icon">
                                    <svg class="icon input-ic"><use href="#i-mail"/></svg>
                                    <input type="email" id="contact-email" name="email" value="<?= e($data['email'] ?? '') ?>" placeholder="vous@exemple.com" required class="form-input">
                                </div>
                                <?php if (isset($errors['email'])): ?>
                                    <span class="error-msg"><?= e($errors['email']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-row-2">
                            <div class="form-group">
                                <label for="contact-cat" class="form-label">Objet de la demande <span class="req">*</span></label>
                                <select id="contact-cat" name="categorie" class="form-select">
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?= e($cat) ?>" <?= ($data['categorie'] ?? '') === $cat ? 'selected' : '' ?>>
                                            <?= e($cat) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group <?= isset($errors['sujet']) ? 'has-error' : '' ?>">
                                <label for="contact-sujet" class="form-label">Sujet précis <span class="req">*</span></label>
                                <input type="text" id="contact-sujet" name="sujet" value="<?= e($data['sujet'] ?? '') ?>" placeholder="Ex : Demande d'information sur un livre" required class="form-input">
                                <?php if (isset($errors['sujet'])): ?>
                                    <span class="error-msg"><?= e($errors['sujet']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="form-group <?= isset($errors['message']) ? 'has-error' : '' ?>">
                            <label for="contact-message" class="form-label">Votre message <span class="req">*</span></label>
                            <textarea id="contact-message" name="message" rows="5" placeholder="Décrivez votre demande en quelques lignes..." required class="form-textarea"><?= e($data['message'] ?? '') ?></textarea>
                            <?php if (isset($errors['message'])): ?>
                                <span class="error-msg"><?= e($errors['message']) ?></span>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn btn--brand btn--lg btn--block" id="btn-submit-contact">
                            <span class="btn-label"><svg class="icon"><use href="#i-send"/></svg> Transmettre mon message</span>
                            <span class="spinner" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- ===== Colonne Droite : Cartes d'informations & FAQ interactive ===== -->
            <div class="contact-info-col reveal">
                <!-- Cartes d'information rapide -->
                <div class="info-cards-grid">
                    <div class="info-card">
                        <div class="ic-icon"><svg class="icon"><use href="#i-mail"/></svg></div>
                        <div class="ic-content">
                            <h4>Email Direct</h4>
                            <p class="ic-detail" id="email-text">contact@bibliotheque.sn</p>
                            <button type="button" class="ic-action-btn" id="btn-copy-email" data-copy="contact@bibliotheque.sn">
                                <svg class="icon"><use href="#i-copy"/></svg> <span>Copier l'adresse</span>
                            </button>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="ic-icon"><svg class="icon"><use href="#i-phone"/></svg></div>
                        <div class="ic-content">
                            <h4>Assistance Téléphonique</h4>
                            <p class="ic-detail">+224 627 97 93 59</p>
                            <span class="ic-sub">Disponible du Lun au Sam (8h - 19h)</span>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="ic-icon"><svg class="icon"><use href="#i-map-pin"/></svg></div>
                        <div class="ic-content">
                            <h4>Campus &amp; Numérique</h4>
                            <p class="ic-detail">Conakry, Guinée</p>
                            <span class="ic-sub">Espace Numérique &amp; Plateforme Web</span>
                        </div>
                    </div>

                    <div class="info-card info-card--dclic">
                        <div class="ic-icon"><svg class="icon"><use href="#i-award"/></svg></div>
                        <div class="ic-content">
                            <h4>Partenaire Pédagogique</h4>
                            <p class="ic-detail"><a href="https://dclic.francophonie.org/" target="_blank">Programme D-CLIC</a></p>
                            <span class="ic-sub">OIF &bull; Métiers du Numérique</span>
                        </div>
                    </div>
                </div>

                <!-- FAQ Accordion -->
                <div class="faq-card">
                    <div class="faq-header">
                        <svg class="icon"><use href="#i-info"/></svg>
                        <h3>Questions fréquentes (FAQ)</h3>
                    </div>
                    <div class="accordion" id="faq-accordion">
                        <div class="accordion-item is-open">
                            <button type="button" class="accordion-trigger" aria-expanded="true">
                                <span>Comment emprunter ou réserver un livre ?</span>
                                <svg class="icon acc-arrow"><use href="#i-chev-r"/></svg>
                            </button>
                            <div class="accordion-content">
                                <p>
                                    Connectez-vous à votre compte lecteur, explorez notre catalogue et cliquez simplement sur le bouton <strong>« Emprunter ce livre »</strong>. La disponibilité est contrôlée instantanément.
                                </p>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-trigger" aria-expanded="false">
                                <span>Comment publier un article sur le blog ?</span>
                                <svg class="icon acc-arrow"><use href="#i-chev-r"/></svg>
                            </button>
                            <div class="accordion-content">
                                <p>
                                    Rendez-vous dans la section <strong>Blog</strong> et cliquez sur <strong>« Rédiger un article »</strong>. Vous pourrez y ajouter un titre, un résumé, votre contenu formaté et une photo d'illustration.
                                </p>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-trigger" aria-expanded="false">
                                <span>Quelle est l'origine du projet Bibliothèque Numérique ?</span>
                                <svg class="icon acc-arrow"><use href="#i-chev-r"/></svg>
                            </button>
                            <div class="accordion-content">
                                <p>
                                    Cette plateforme est le <strong>projet de fin de formation</strong> du niveau intermédiaire en développement web réalisé dans le cadre du <strong>programme D-CLIC</strong>, développé par <strong>Traoré Alseny</strong> sous la direction d'un tuteur/coach dédié.
                                </p>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <button type="button" class="accordion-trigger" aria-expanded="false">
                                <span>Comment proposer mes livres en tant qu'auteur ?</span>
                                <svg class="icon acc-arrow"><use href="#i-chev-r"/></svg>
                            </button>
                            <div class="accordion-content">
                                <p>
                                    Depuis votre compte, accédez à l'<strong>Espace Auteur</strong> pour soumettre vos manuscrits avec résumé, couverture et métadonnées. L'équipe modère puis publie votre livre au catalogue.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
