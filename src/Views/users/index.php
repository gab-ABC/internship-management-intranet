<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Gestion des utilisateurs</h1>
    <a href="<?= url('user_create') ?>" class="btn btn-primary">+ Nouvel utilisateur</a>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <thead class="table-dark">
            <tr>
                <th>Identifiant</th>
                <th>Nom complet</th>
                <th>Rôle</th>
                <th>Créé le</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $u): ?>
            <tr>
                <td><?= e($u['username']) ?></td>
                <td><?= e($u['full_name']) ?></td>
                <td>
                    <span class="badge text-bg-<?= role_color($u['role']) ?>"><?= e($u['role']) ?></span>
                </td>
                <td class="small text-muted"><?= e($u['created_at']) ?></td>
                <td class="text-end">
                    <a href="<?= url('user_edit') ?>&id=<?= (int) $u['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <?php if ($u['id'] != current_user()['id']): ?>
                        <form method="post" action="<?= url('user_delete') ?>" class="d-inline"
                              onsubmit="return confirm('Supprimer cet utilisateur ?');">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $u['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
