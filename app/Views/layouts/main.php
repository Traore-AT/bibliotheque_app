<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($metaDescription ?? 'Bibliothèque numérique en ligne : catalogue de livres, recherche, liste de lecture et administration.') ?>">
    <meta name="theme-color" content="#0F172A">
    <title><?= e($title ?? 'Bibliothèque Numérique') ?> — <?= e(APP_NAME) ?></title>

    <!-- Typographie : Plus Jakarta Sans (font-display: swap) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Style principal -->
    <link rel="stylesheet" href="<?= e(APP_BASE_URL) ?>/css/style.css">

    <!-- Favicon SVG léger (éco-conception) -->
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Crect width='16' height='16' rx='4' fill='%230F766E'/%3E%3Ctext x='8' y='12' font-size='10' text-anchor='middle' fill='white' font-family='sans-serif' font-weight='bold'%3EB%3C/text%3E%3C/svg%3E">
</head>
<body data-app-base="<?= e(APP_BASE_URL) ?>">

    <!-- ===== Sprite d'icônes SVG (éco-conception : une seule définition, réutilisable) ===== -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display:none" aria-hidden="true">
        <symbol id="i-book" viewBox="0 0 24 24"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H11v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM13 3h4.5A2.5 2.5 0 0 1 20 5.5v15A2.5 2.5 0 0 0 17.5 18H13V3Z"/></symbol>
        <symbol id="i-heart" viewBox="0 0 24 24"><path d="M12 21s-7.5-4.7-9.6-9C.8 8 2.6 4.5 6 4.5c2 0 3.4 1 4.2 2.2l1.8 2.6 1.8-2.6c.8-1.2 2.2-2.2 4.2-2.2 3.4 0 5.2 3.5 3.6 7.5-2.1 4.3-9.6 9-9.6 9Z"/></symbol>
        <symbol id="i-plus" viewBox="0 0 24 24"><path d="M11 11V5h2v6h6v2h-6v6h-2v-6H5v-2h6Z"/></symbol>
        <symbol id="i-edit" viewBox="0 0 24 24"><path d="M16.5 4.1a1.9 1.9 0 0 1 2.7 0l.7.7a1.9 1.9 0 0 1 0 2.7L8.4 19H5v-3.4L16.5 4.1ZM3.5 19l.5-1.7.7.6-.3 1.6-1.4.3.5-.8ZM4 21h17v-1.5H4V21Z"/></symbol>
        <symbol id="i-trash" viewBox="0 0 24 24"><path d="M9 2h6l1 2h4v2H4V4h4l1-2ZM5.5 8H19l-1 13.2A2 2 0 0 1 16 23H8a2 2 0 0 1-2-1.8L5.5 8Zm4 3h-1.5v8H9.5v-8Zm4 0h-1.5v8h1.5v-8Zm4 0H15v8h1.5v-8Z"/></symbol>
        <symbol id="i-close" viewBox="0 0 24 24"><path d="m6.7 5.3 5.3 5.3 5.3-5.3 1.4 1.4-5.3 5.3 5.3 5.3-1.4 1.4-5.3-5.3-5.3 5.3-1.4-1.4 5.3-5.3-5.3-5.3 1.4-1.4Z"/></symbol>
        <symbol id="i-menu" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6Zm0 5h18v2H3v-2Zm0 5h12v2H3v-2Z"/></symbol>
        <symbol id="i-search" viewBox="0 0 24 24"><path d="m16.3 14.9 3.6 3.6-1.4 1.4-3.6-3.6a7.5 7.5 0 1 1 1.4-1.4Zm-1-1.3a5.5 5.5 0 1 0-7.8-7.8 5.5 5.5 0 0 0 7.8 7.8Z"/></symbol>
        <symbol id="i-check" viewBox="0 0 24 24"><path d="M9.6 16.2 5.3 12l-1.4 1.4 5.7 5.6L20.4 8l-1.4-1.4-9.4 9.6Z"/></symbol>
        <symbol id="i-info" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v2h-2V7Zm0 4h2v6h-2v-6Z"/></symbol>
        <symbol id="i-warn" viewBox="0 0 24 24"><path d="M12 2 1 21h22L12 2Zm1 14h-2v2h2v-2Zm0-7h-2v5h2V9Z"/></symbol>
        <symbol id="i-alert" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm-1 5h2v6h-2V7Zm0 8h2v2h-2v-2Z"/></symbol>
        <symbol id="i-arrow" viewBox="0 0 24 24"><path d="M13.5 5 21 12l-7.5 7-1.4-1.4 5-5H3v-2h14.1l-5-5 1.4-1.6Z"/></symbol>
        <symbol id="i-chev-l" viewBox="0 0 24 24"><path d="m14.5 5 1.4 1.4L10.3 12l5.6 5.6-1.4 1.4L7.5 12l7-7Z"/></symbol>
        <symbol id="i-chev-r" viewBox="0 0 24 24"><path d="m9.5 5-1.4 1.4 5.6 5.6-5.6 5.6L9.5 19l7-7-7-7Z"/></symbol>
        <symbol id="i-library" viewBox="0 0 24 24"><path d="M3 3h3v17H3V3Zm6 0h3v17H9V3Zm8 0 4 17h-2.8l-1-5.2h-3L17.4 3H17Z"/></symbol>
        <symbol id="i-user" viewBox="0 0 24 24"><path d="M12 2a5 5 0 1 0 0 10 5 5 0 0 0 0-10ZM4 20c0-3.5 3.6-6 8-6s8 2.5 8 6v1H4v-1Z"/></symbol>
        <symbol id="i-cal" viewBox="0 0 24 24"><path d="M7 2h2v3h6V2h2v3h3v16H4V5h3V2Zm-1 9h12v-4H6v4Zm0 2v5h12v-5H6Z"/></symbol>
        <symbol id="i-clock" viewBox="0 0 24 24"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Zm5 13.5-6-3.5V7h1.5v4l5 3-1.5.5Z"/></symbol>
        <symbol id="i-filter" viewBox="0 0 24 24"><path d="M4 6h16l-6 6.6V19l-4 3v-9.4L4 6Z"/></symbol>
        <symbol id="i-cart" viewBox="0 0 24 24"><path d="M7 18a2 2 0 1 1 0 4 2 2 0 0 1 0-4Zm10 0a2 2 0 1 1 0 4 2 2 0 0 1 0-4ZM6.2 4h16.2l-2.2 10H7.7L5.4 2H2V0h4.5l1.7 4Zm1.3 2 1.3 6h11l1.5-6H7.5Z"/></symbol>
        <symbol id="i-flag" viewBox="0 0 24 24"><path d="M4 2h15l-2 4 2 4H6v12H4V2Z"/></symbol>
        <symbol id="i-spark" viewBox="0 0 24 24"><path d="M12 2 13.7 8.6 20 10l-6.3 1.4L12 18l-1.7-6.6L4 10l6.3-1.4L12 2Zm7 13 1 3.5 3.5 1-3.5 1L19 22l-1-3.5-3.5-1 3.5-1 1-3.5Z"/></symbol>
        <symbol id="i-star" viewBox="0 0 24 24"><path d="M12 17.3 6.2 20l1.1-6.4L2.5 9l6.4-.9L12 2.5l3.1 5.6 6.4.9-4.8 4.6L17.8 20 12 17.3Z"/></symbol>
        <symbol id="i-star-half" viewBox="0 0 24 24"><path d="M12 2.5l3.1 5.6 6.4.9-4.8 4.6L17.8 20 12 17.3 6.2 20l1.1-6.4L2.5 9l6.4-.9L12 2.5Zm0 3.3-2 3.6-4.1.6 3 2.9-.7 4.1L12 15.7V5.8Z"/></symbol>
        <symbol id="i-star-o" viewBox="0 0 24 24"><path d="M12 17.3 6.2 20l1.1-6.4L2.5 9l6.4-.9L12 2.5l3.1 5.6 6.4.9-4.8 4.6L17.8 20 12 17.3Zm0-2.6 3.4 1.7-.9-5.1 3.7-3.6-5.1-.7-1.9-4.2L10.3 8 7.5 11.6 6.6 16.7 10 15.9l2-1.3Z"/></symbol>
        <symbol id="i-logout" viewBox="0 0 24 24"><path d="M10 3v2.5H5V18.5h5V21H3V3h7Zm11.2 9-4.3-4.2-1.4 1.4 2 2H10v2h7.5l-2 2 1.4 1.4 4.3-4.3a1 1 0 0 0 0-1.3Z"/></symbol>
        <symbol id="i-users" viewBox="0 0 24 24"><path d="M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm0 2.5c-3.5 0-6.3 2-6.3 4.3V20h8.2v-2.2c0-1 .3-1.9.8-2.7A6.6 6.6 0 0 0 9 13.5Zm6-2a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm0 2c-2.8 0-5.2 1.6-5.2 3.6V20H22v-3.9c0-2-2.4-3.6-5.2-3.6h-1.8Z"/></symbol>
        <symbol id="i-return" viewBox="0 0 24 24"><path d="M5 4h8v2H7v12h10v-5h2v7H5V4Zm13.6 2h-2.5V4h6v6h-2v-2.5L13 14.6l-1.4-1.4 7.9-8Z"/></symbol>
        <symbol id="i-pen" viewBox="0 0 24 24"><path d="M15.9 2.9a2.4 2.4 0 0 1 3.4 0l1.8 1.8a2.4 2.4 0 0 1 0 3.4L8.3 20.9l-5.8 1.6 1.6-5.8L15.9 2.9ZM14 5.4l4.6 4.6 1.9-1.9a.9.9 0 0 0 0-1.3l-3.3-3.3a.9.9 0 0 0-1.3 0L14 5.4Zm-9 9.7 8.5-8.5 1.9 1.9-8.5 8.5-3.2.9.3-2.8Z"/></symbol>
        <symbol id="i-image" viewBox="0 0 24 24"><path d="M4 3h16a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm0 2v14h16V5H4Zm2 10 4-4 2.5 2.5L15 9l3 4-.5.6H18l-3-4-1.5 2-2.5-2.5L6 17v-2Z"/></symbol>
        <symbol id="i-newspaper" viewBox="0 0 24 24"><path d="M19 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2Zm-7 3h6v2h-6V6Zm0 4h6v2h-6v-2Zm-7 8V6h5v12H5Zm14 0h-7v-2h7v2Zm0-4h-7v-2h7v2Z"/></symbol>
        <symbol id="i-mail" viewBox="0 0 24 24"><path d="M20 4H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z"/></symbol>
        <symbol id="i-send" viewBox="0 0 24 24"><path d="M2.01 21 23 12 2.01 3 2 10l15 2-15 2z"/></symbol>
        <symbol id="i-award" viewBox="0 0 24 24"><path d="m12 15.4 3.7 2.3-1-4.2 3.3-2.9-4.3-.4L12 6.2l-1.7 4-4.3.4 3.3 2.9-1 4.2 3.7-2.3ZM12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z"/></symbol>
        <symbol id="i-map-pin" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Zm0 9.5a2.5 2.5 0 0 1 0-5 2.5 2.5 0 0 1 0 5Z"/></symbol>
        <symbol id="i-phone" viewBox="0 0 24 24"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24 11.72 11.72 0 0 0 3.67.59 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.72 11.72 0 0 0 .59 3.67 1 1 0 0 1-.25 1.02l-2.22 2.1Z"/></symbol>
        <symbol id="i-eye" viewBox="0 0 24 24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5ZM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5Zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6Z"/></symbol>
        <symbol id="i-copy" viewBox="0 0 24 24"><path d="M16 1H4a2 2 0 0 0-2 2v14h2V3h12V1Zm3 4H8a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h11a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2Zm0 16H8V7h11v14Z"/></symbol>
    </svg>

    <!-- Saut de navigation : accessibilité WCAG -->
    <a class="skip-link" href="#contenu">Aller au contenu principal</a>

    <!-- ===== TOP BAR SUPÉRIEURE (SaaS Modern UI) ===== -->
    <div class="site-topbar">
        <div class="container topbar-inner">
            <div class="topbar-left">
                <a href="mailto:contact@bibliotheque.sn" class="topbar-item" title="Écrivez-nous par email">
                    <svg class="icon"><use href="#i-mail"/></svg>
                    <span>contact@bibliotheque.sn</span>
                </a>
                <span class="topbar-divider"></span>
                <a href="tel:+224627979359" class="topbar-item" title="Assistance téléphonique">
                    <svg class="icon"><use href="#i-phone"/></svg>
                    <span>+224 627 97 93 59</span>
                </a>
            </div>

            <div class="topbar-center">
                <a href="<?= e(url('blog', ['categorie' => 'Formation D-CLIC'])) ?>" class="topbar-announce">
                    <span class="topbar-pulse"></span>
                    <svg class="icon"><use href="#i-award"/></svg>
                    <span class="topbar-announce-text">Projet Final D-CLIC • Niveau Intermédiaire Web</span>
                    <span class="topbar-arrow">&rarr;</span>
                </a>
            </div>

            <div class="topbar-right">
                <span class="topbar-status">
                    <span class="status-dot"></span>
                    <span>Accès 24/7</span>
                </span>
                <span class="topbar-divider"></span>
                <a href="<?= e(url('contact')) ?>" class="topbar-link">Aide &amp; FAQ</a>
                <?php if ($currentUser !== null): ?>
                    <span class="topbar-divider"></span>
                    <a href="<?= e(url('profile')) ?>" class="topbar-user-badge" title="Accéder à mon espace">
                        <svg class="icon"><use href="#i-user"/></svg>
                        <span><?= e($currentUser['prenom'] ?? 'Membre') ?></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <header class="site-header" role="banner">
        <div class="container header-inner">
            <a class="brand" href="<?= e(url('')) ?>" aria-label="Accueil — Bibliothèque Numérique">
                <span class="brand-mark" aria-hidden="true"><svg class="icon"><use href="#i-library"/></svg></span>
                <span>
                    <span class="brand-name"><?= e(APP_NAME) ?></span>
                    <span class="brand-sub">Catalogue &amp; lecture</span>
                </span>
            </a>

            <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="nav-principale" aria-label="Ouvrir la navigation">
                <svg class="icon"><use href="#i-menu"/></svg>
            </button>

            <nav id="nav-principale" class="main-nav" aria-label="Navigation principale">
                <?php $currentUserRole = $currentUser['role'] ?? ''; ?>
                <ul class="nav-list">
                    <li>
                        <a class="nav-link <?= ($currentPage ?? '') === 'books/index' ? 'is-active' : '' ?>" <?= ($currentPage ?? '') === 'books/index' ? 'aria-current="page"' : '' ?> href="<?= e(url('')) ?>">
                            <svg class="icon"><use href="#i-book"/></svg> Accueil
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?= str_starts_with($currentPage ?? '', 'blog/') ? 'is-active' : '' ?>" <?= str_starts_with($currentPage ?? '', 'blog/') ? 'aria-current="page"' : '' ?> href="<?= e(url('blog')) ?>">
                            <svg class="icon"><use href="#i-newspaper"/></svg> Blog
                        </a>
                    </li>
                    <li>
                        <a class="nav-link <?= str_starts_with($currentPage ?? '', 'contact') ? 'is-active' : '' ?>" <?= str_starts_with($currentPage ?? '', 'contact') ? 'aria-current="page"' : '' ?> href="<?= e(url('contact')) ?>">
                            <svg class="icon"><use href="#i-mail"/></svg> Contact
                        </a>
                    </li>

                    <?php if ($currentUser === null): ?>
                        <li>
                            <a class="nav-link <?= ($currentPage ?? '') === 'auth/login' ? 'is-active' : '' ?>" href="<?= e(url('login')) ?>">
                                <svg class="icon"><use href="#i-user"/></svg> Connexion
                            </a>
                        </li>
                        <li>
                            <a class="nav-link <?= ($currentPage ?? '') === 'auth/register' ? 'is-active' : '' ?>" href="<?= e(url('inscription')) ?>">
                                <svg class="icon"><use href="#i-plus"/></svg> Créer un compte
                            </a>
                        </li>
                    <?php else: ?>
                        <li>
                            <a class="nav-link <?= ($currentPage ?? '') === 'profile/index' ? 'is-active' : '' ?>" href="<?= e(url('profile')) ?>">
                                <svg class="icon"><use href="#i-user"/></svg> Mon espace
                            </a>
                        </li>

                        <li>
                            <a class="nav-link <?= str_starts_with($currentPage ?? '', 'author/') ? 'is-active' : '' ?>" href="<?= e(url('auteur')) ?>">
                                <svg class="icon"><use href="#i-pen"/></svg> Espace auteur
                            </a>
                        </li>

                        <?php if ($currentUserRole === 'admin'): ?>
                            <li>
                                <a class="nav-link <?= ($currentPage ?? '') === 'admin/index' ? 'is-active' : '' ?>" href="<?= e(url('admin')) ?>">
                                    <svg class="icon"><use href="#i-cart"/></svg> Administration
                                </a>
                            </li>
                            <li>
                                <a class="nav-link <?= ($currentPage ?? '') === 'admin/users' ? 'is-active' : '' ?>" href="<?= e(url('admin/users')) ?>">
                                    <svg class="icon"><use href="#i-users"/></svg> Utilisateurs
                                </a>
                            </li>
                            <li>
                                <a class="nav-link <?= ($currentPage ?? '') === 'admin/loans' ? 'is-active' : '' ?>" href="<?= e(url('admin/loans')) ?>">
                                    <svg class="icon"><use href="#i-return"/></svg> Emprunts
                                </a>
                            </li>
                            <li>
                                <a class="nav-link <?= ($currentPage ?? '') === 'admin/books' ? 'is-active' : '' ?>" href="<?= e(url('admin/books')) ?>">
                                    <svg class="icon"><use href="#i-flag"/></svg> Modération
                                </a>
                            </li>
                        <?php endif; ?>

                        <li>
                            <form class="nav-form" action="<?= e(url('logout')) ?>" method="post">
                                <?= \App\Core\Csrf::field() ?>
                                <button type="submit" class="nav-link nav-btn">
                                    <svg class="icon"><use href="#i-logout"/></svg> Déconnexion
                                </button>
                            </form>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Conteneur des messages flash => affichés en toasts -->
    <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="false">
        <?php if (!empty($flashMessages)): ?>
            <?php foreach ($flashMessages as $type => $messages): ?>
                <?php foreach ($messages as $message): ?>
                    <?php
                        $icon = 'i-info';
                        if ($type === 'success') { $icon = 'i-check'; }
                        elseif ($type === 'error') { $icon = 'i-alert'; }
                        elseif ($type === 'warning') { $icon = 'i-warn'; }
                    ?>
                    <div class="toast toast--<?= e($type) ?>" role="status">
                        <span class="toast-icon"><svg class="icon"><use href="#<?= $icon ?>"/></svg></span>
                        <p class="toast-msg"><?= e($message) ?></p>
                        <span class="toast-bar" aria-hidden="true"></span>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Conteneur du panneau latéral (drawer) — utilisé uniquement par l'admin -->
    <div class="drawer-backdrop" data-drawer-close aria-hidden="true"></div>

    <!-- Modale de confirmation globale -->
    <div class="modal" id="confirm-modal" role="dialog" aria-modal="true" aria-labelledby="confirm-title" aria-describedby="confirm-text">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-dialog">
            <span class="modal-ic"><svg class="icon"><use href="#i-alert"/></svg></span>
            <h2 class="modal-title" id="confirm-title">Confirmer</h2>
            <p class="modal-text" id="confirm-text">Êtes-vous sûr de vouloir continuer ?</p>
            <div class="modal-actions">
                <button type="button" class="btn btn--soft" data-modal-close>Annuler</button>
                <button type="button" class="btn btn--danger" data-modal-confirm>Supprimer</button>
            </div>
        </div>
    </div>

    <main id="contenu" class="site-main">
        <?= $content ?>
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-brand-side">
                <a class="brand brand--footer" href="<?= e(url('')) ?>">
                    <span class="brand-mark" aria-hidden="true"><svg class="icon"><use href="#i-library"/></svg></span>
                    <span>
                        <span class="brand-name"><?= e(APP_NAME) ?></span>
                        <span class="brand-sub">Catalogue &amp; lecture numérique</span>
                    </span>
                </a>
                <p class="footer-desc">
                    Plateforme web moderne développée comme projet de fin de formation de niveau intermédiaire en développement web (Programme D-CLIC).
                </p>
                <div class="footer-partner-badge">
                    <span class="fpb-label">Partenaire &bull; Programme D-CLIC</span>
                    <a href="<?= e(url('blog', ['categorie' => 'Formation D-CLIC'])) ?>" class="fpb-logo-link" title="Découvrir le programme D-CLIC">
                        <img src="<?= e(APP_BASE_URL) ?>/img/OIF_LOGO-BLOC%20MARQUE%20OIF_CMJN.jpg" alt="Logo OIF / D-CLIC - Organisation Internationale de la Francophonie" class="footer-oif-logo">
                    </a>
                </div>
            </div>
            <div class="footer-nav-side">
                <div class="footer-col">
                    <h4 class="footer-heading">Navigation</h4>
                    <ul class="footer-links">
                        <li><a href="<?= e(url('')) ?>">Accueil</a></li>
                        <li><a href="<?= e(url('blog')) ?>">Blog &amp; Articles</a></li>
                        <li><a href="<?= e(url('contact')) ?>">Contact &amp; Support</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h4 class="footer-heading">Espace Membres</h4>
                    <ul class="footer-links">
                        <?php if ($currentUser === null): ?>
                            <li><a href="<?= e(url('login')) ?>">Connexion</a></li>
                            <li><a href="<?= e(url('inscription')) ?>">Créer un compte</a></li>
                        <?php else: ?>
                            <li><a href="<?= e(url('profile')) ?>">Mon espace</a></li>
                            <li><a href="<?= e(url('auteur')) ?>">Espace auteur</a></li>
                        <?php endif; ?>
                        <li><a href="<?= e(url('blog', ['categorie' => 'Formation D-CLIC'])) ?>">Hommage D-CLIC</a></li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="container footer-bottom">
            <span>&copy; <?= date('Y') ?> — <?= e(APP_NAME) ?>. Tous droits réservés.</span>
            <span class="footer-credit">
                <svg class="icon"><use href="#i-spark"/></svg> Projet de fin de formation D-CLIC — <strong>Traoré Alseny</strong> &amp; Mentorat
            </span>
        </div>
    </footer>

    <!-- Fallback : sans JS, le contenu reste visible -->
    <noscript><style>.reveal,.reveal-stagger>*, .reveal-stagger > *{opacity:1 !important;transform:none !important}</style></noscript>

    <!-- Script principal : recherche AJAX, toasts, modale, drawer, micro-interactions -->
    <script src="<?= e(APP_BASE_URL) ?>/js/app.js" defer></script>
</body>
</html>