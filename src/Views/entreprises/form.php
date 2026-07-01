<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<?php
$isEdit = $entreprise !== null;
$val = fn($key, $default = '') => e($old[$key] ?? ($entreprise[$key] ?? $default));
$action = $isEdit ? url('entreprise_update') : url('entreprise_store');
?>

<h1 class="h3 mb-4"><?= $isEdit ? 'Modifier l\'entreprise' : 'Nouvelle entreprise' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" class="card card-body" style="max-width:720px">
    <?= \App\Core\Csrf::field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $entreprise['id'] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Nom de l'entreprise *</label>
        <input type="text" name="nom" class="form-control" required value="<?= $val('nom') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Adresse</label>
        <input type="text" name="adresse" class="form-control" value="<?= $val('adresse') ?>">
    </div>
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Code postal</label>
            <input type="text" name="code_postal" class="form-control" value="<?= $val('code_postal') ?>">
        </div>
        <div class="col-md-8">
            <label class="form-label">Ville</label>
            <input type="text" name="ville" class="form-control" value="<?= $val('ville') ?>">
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Enregistrer les modifications' : 'Créer' ?></button>
        <a href="<?= url('entreprises') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
