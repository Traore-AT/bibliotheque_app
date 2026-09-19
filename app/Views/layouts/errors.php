<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0F172A">
    <title>Page introuvable — Bibliothèque Numérique</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= e(APP_BASE_URL) ?>/css/style.css">
</head>
<body class="error-page">
    <div class="error-wrap">
        <div class="error-box">
            <p class="error-code" aria-hidden="true">404</p>
            <h1 class="error-title"><?= e($title ?? 'Page introuvable') ?></h1>
            <p class="error-text"><?= e($message ?? 'La ressource demandée n\'existe pas ou a été déplacée.') ?></p>
            <a class="btn btn--brand btn--lg" href="<?= e(url('')) ?>">
                <svg class="icon" aria-hidden="true" style="width:18px;height:18px"><use href="#i-chev-l"/></svg>
                Retour à l'accueil
            </a>
        </div>
    </div>
</body>
</html>