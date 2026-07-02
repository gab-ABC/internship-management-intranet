<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<h1 class="h3 mb-4">Configuration du site</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="card card-body mb-4" style="max-width:640px">
    <p class="mb-1"><strong>Nom de l'établissement :</strong> <?= e($nom) ?></p>
</div>

<div class="card card-body" style="max-width:640px">
    <h2 class="h5">Changer mon mot de passe</h2>
    <form method="post" action="<?= url('config_update') ?>" class="card card-body" style="max-width:640px">
        <?= \App\Core\Csrf::field() ?>

        <div class="mb-3">
            <label class="form-label">Nom de l'établissement</label>
            <input type="text" name="username" class="form-control">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-primary" type="submit">Enregistrer</button>
            <a href="<?= url('home') ?>" class="btn btn-outline-secondary">Annuler</a>
        </div>
    </form>
</div>
