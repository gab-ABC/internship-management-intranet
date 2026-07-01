<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<h1 class="h3 mb-4">Mon compte</h1>

<div class="card card-body mb-4" style="max-width:640px">
    <p class="mb-1"><strong>Identifiant :</strong> <?= e(current_user()['username']) ?></p>
    <p class="mb-1"><strong>Nom :</strong> <?= e(current_user()['full_name']) ?></p>
    <p class="mb-0"><strong>Role :</strong> <?= e(current_role()) ?></p>
</div>

<div class="card card-body" style="max-width:640px">
    <h2 class="h5">Changer mon mot de passe</h2>
    <form method="post" action="<?= url('account_password') ?>">
        <?= \App\Core\Csrf::field() ?>
        <div class="mb-3">
            <label class="form-label">Mot de passe actuel</label>
            <input type="password" name="current_password" class="form-control js-password" required autocomplete="current-password">
        </div>
        <div class="mb-3">
            <label class="form-label">Nouveau mot de passe</label>
            <input type="password" name="new_password" class="form-control js-password" required autocomplete="new-password">
            <div class="form-text">8 caracteres minimum.</div>
        </div>
        <div class="mb-3">
            <label class="form-label">Confirmer le nouveau mot de passe</label>
            <input type="password" name="confirm_password" class="form-control js-password" required autocomplete="new-password">
        </div>
        <div class="form-check mb-3">
            <input class="form-check-input js-show-password" type="checkbox" id="show-password">
            <label class="form-check-label small" for="show-password">Afficher le mot de passe</label>
        </div>
        <button class="btn btn-primary" type="submit">Mettre a jour</button>
    </form>
</div>
<script src="assets/js/app.js"></script>