<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion &middot; Stages Jean Rostand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="/assets/icons/favicon.ico">
    <link rel="icon" href="/assets/icons/favicon.svg" sizes="any" type="image/svg+xml">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon.png">
    <link rel="shortcut icon" href="/assets/icons/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/favicon.png">
</head>
<body class="bg-body-tertiary">
<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-12 col-sm-10 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 text-center mb-1">🎓 Stages Jean Rostand</h1>
                    <p class="text-center text-muted small mb-4">Connectez-vous pour continuer</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= e($error) ?></div>
                    <?php endif; ?>

                    <form method="post" action="<?= url('login') ?>">
                        <?= \App\Core\Csrf::field() ?>
                        <div class="mb-3">
                            <label class="form-label" for="username">Identifiant</label>
                            <input class="form-control" type="text" id="username" name="username"
                                   autofocus required value="<?= e($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-2">
                            <label class="form-label" for="password">Mot de passe</label>
                            <input class="form-control js-password" type="password" id="password" name="password" required>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input js-show-password" type="checkbox" id="show-password">
                            <label class="form-check-label small" for="show-password">Afficher le mot de passe</label>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Se connecter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
