<?php /** Vue : gestion des utilisateurs (admin) */ ?>

<section class="container page-inner">
    <div class="page-head reveal">
        <div>
            <h1>Utilisateurs</h1>
            <p class="head-meta"><?= $totalUsers ?> compte(s) inscrit(s). Gérez les rôles : lecteur ou administrateur.</p>
        </div>
    </div>

    <div class="table-panel reveal">
        <div class="table-wrap">
            <table class="data-table">
                <caption class="sr-only">Liste des utilisateurs</caption>
                <thead>
                    <tr>
                        <th scope="col">Utilisateur</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rôle</th>
                        <th scope="col">Inscrit le</th>
                        <th scope="col" class="cell-actions">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?= e($user['prenom'] . ' ' . mb_strtoupper($user['nom'])) ?></strong>
                                <?php if ((int) $user['id'] === (int) $currentUser['id']): ?>
                                    <span class="id-pill">Vous</span>
                                <?php endif; ?>
                            </td>
                            <td class="cell-author"><?= e($user['email']) ?></td>
                            <td>
                                <span class="badge <?= $user['role'] === 'admin' ? 'badge--brand' : 'badge--ok' ?>">
                                    <svg class="icon"><use href="#<?= $user['role'] === 'admin' ? 'i-users' : 'i-user' ?>"/></svg>
                                    <?= $user['role'] === 'admin' ? 'Administrateur' : 'Lecteur' ?>
                                </span>
                            </td>
                            <td><?= e((new DateTimeImmutable($user['date_creation']))->format('d/m/Y')) ?></td>
                            <td class="cell-actions">
                                <form action="<?= e(url('admin/role')) ?>" method="post" class="inline">
                                    <?= \App\Core\Csrf::field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
                                    <select class="role-select" name="role" aria-label="Rôle de <?= e($user['prenom']) ?>">
                                        <option value="lecteur" <?= $user['role'] === 'lecteur' ? 'selected' : '' ?>>Lecteur</option>
                                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Administrateur</option>
                                    </select>
                                    <button type="submit" class="btn btn--soft btn--sm">
                                        <svg class="icon"><use href="#i-check"/></svg>
                                        Appliquer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>