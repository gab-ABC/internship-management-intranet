<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<?php
$isEdit = $prof !== null;
// Valeurs affichees : anciennes saisies > valeurs existantes > vide.
$val = fn($key, $default = '') => e($old[$key] ?? ($prof[$key] ?? $default));
?>

<h1 class="h3 mb-4"><?= $isEdit ? 'Modifier un professeur' : 'Nouveau professeur' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? url('prof_update') : url('prof_store') ?>" class="card card-body" style="max-width:640px">
    <?= \App\Core\Csrf::field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $prof['id'] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label" for="nom">Nom du professeur *</label>
        <input type="text" name="nom" id="nom" class="form-control" required value="<?= $val('nom') ?>">
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= url('profs') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
