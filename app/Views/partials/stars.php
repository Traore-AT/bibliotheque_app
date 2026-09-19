<?php
/**
 * Partielle : affichage d'une note en étoiles (accessible).
 * Attend $stars (float 0..5) et, optionnellement, $label (texte aria),
 * $css (classe additionnelle) et $backdrop (couleur sur fond sombre).
 */
$stars = round((float) ($stars ?? 0) * 2) / 2;
$pct = max(0, min(5, $stars)) / 5 * 100;
$label = $label ?? ('Note : ' . number_format($stars, 1, ',', ' ') . ' sur 5');
?>
<div class="stars <?= isset($backdrop) && $backdrop ? 'stars--inv' : '' ?> <?= e($css ?? '') ?>"
     role="img" aria-label="<?= e($label) ?>">
    <span class="stars-inner" aria-hidden="true">
        <span class="stars-base">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <svg class="icon"><use href="#i-star-o"/></svg>
            <?php endfor; ?>
        </span>
        <span class="stars-fill" style="width: <?= round($pct) ?>%">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <svg class="icon"><use href="#i-star"/></svg>
            <?php endfor; ?>
        </span>
    </span>
    <?php if (isset($showValue) && $showValue): ?>
        <span class="stars-value"><?= number_format($stars, 1, ',', ' ') ?></span>
    <?php endif; ?>
</div>