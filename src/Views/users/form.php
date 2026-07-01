<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<?php
$isEdit = $user !== null;
// Valeurs affichees : anciennes saisies > valeurs existantes > vide.
$val = fn($key, $default = '') => e($old[$key] ?? ($user[$key] ?? $default));
?>

<h1 class="h3 mb-4"><?= $isEdit ? 'Modifier un utilisateur' : 'Nouvel utilisateur' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= $isEdit ? url('user_update') : url('user_store') ?>" class="card card-body" style="max-width:640px">
    <?= \App\Core\Csrf::field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $user['id'] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label class="form-label">Identifiant *</label>
        <input type="text" name="username" class="form-control" required value="<?= $val('username') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Nom complet *</label>
        <input type="text" name="full_name" class="form-control" required value="<?= $val('full_name') ?>">
    </div>
    <div class="mb-3">
        <label class="form-label">Role *</label>
        <select name="role" class="form-select">
            <?php
            $currentRole = $old['role'] ?? ($user['role'] ?? 'eleve');
            $labels = ['admin' => 'Administrateur', 'prof' => 'Professeur', 'eleve' => 'Eleve'];
            foreach ($labels as $value => $label): ?>
                <option value="<?= $value ?>" <?= selected($value, $currentRole) ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mb-2">
        <label class="form-label">Mot de passe <?= $isEdit ? '(laisser vide pour ne pas changer)' : '*' ?></label>
        <input type="password" name="password" class="form-control js-password" <?= $isEdit ? '' : 'required' ?>
               autocomplete="new-password">
        <div class="form-text">8 caractères minimum.</div>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input js-show-password" type="checkbox" id="show-password">
        <label class="form-check-label small" for="show-password">Afficher le mot de passe</label>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Enregistrer' : 'Créer' ?></button>
        <a href="<?= url('users') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
