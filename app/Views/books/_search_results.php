<?php /** Vue partielle : suggestions de recherche instantanée (AJAX) */ ?>

<?php if (empty($books)): ?>
    <div class="suggest-empty">
        <svg class="icon"><use href="#i-search"/></svg>
        Aucun résultat pour « <?= e($term) ?> »
    </div>
<?php else: ?>
    <ul class="suggest-list">
        <?php foreach ($books as $book): ?>
            <li class="suggest-item">
                <a href="<?= e(url('book/show', ['id' => $book['id']])) ?>">
                    <span class="suggest-cover" style="--h: <?= ($book['id'] * 47) % 360 ?>">
                        <?= e(mb_strtoupper(mb_substr($book['titre'], 0, 1))) ?>
                    </span>
                    <span class="suggest-body">
                        <span class="suggest-title"><?= e($book['titre']) ?></span>
                        <span class="suggest-sub"><?= e($book['auteur']) ?> · <?= e($book['maison_edition']) ?></span>
                    </span>
                    <svg class="icon suggest-icon"><use href="#i-arrow"/></svg>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>