<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Gestion des professeurs</h1>
    <a href="<?= url('prof_create') ?>" class="btn btn-primary">+ Nouveau professeur</a>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle bg-white">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom Professeur</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($professeurs as $p): ?>
            <tr>
                <td><?= e($p['id']) ?></td>
                <td><?= e($p['nom']) ?></td>
                <td class="text-end">
                    <a href="<?= url('prof_edit') ?>&id=<?= (int) $p['id'] ?>" class="btn btn-sm btn-outline-primary">Modifier</a>
                    <?php if ($p['id'] != current_user()['id']): ?>
                        <form method="post" action="<?= url('prof_delete') ?>" class="d-inline"
                              onsubmit="return confirm('Supprimer ce professeur ?');">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
