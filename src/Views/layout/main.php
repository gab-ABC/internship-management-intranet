<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Stages') ?> &middot; Stages Jean Rostand</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">

    <link rel="icon" type="image/x-icon" href="/assets/icons/favicon.ico">
    <link rel="icon" href="/assets/icons/favicon.svg" sizes="any" type="image/svg+xml">
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/icons/favicon.png">
    <link rel="shortcut icon" href="/assets/icons/favicon.ico" type="image/x-icon">
    <link rel="apple-touch-icon" sizes="180x180" href="/assets/icons/favicon.png">
</head>
<body class="bg-body-tertiary">

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="<?= url('home') ?>">🎓 Stages Jean Rostand</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainnav"
                aria-controls="mainnav" aria-expanded="false" aria-label="Menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainnav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item active"><a class="nav-link" href="<?= url('home') ?>">Accueil</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('stages') ?>">Stages</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('entreprises') ?>">Entreprises</a></li>
                <?php if (can_edit()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('profs') ?>">Professeurs</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('stage_create') ?>">Ajouter un stage</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('users') ?>">Utilisateurs</a></li>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <?= e(current_user()['full_name'] ?? '') ?>
                        <span class="badge text-bg-<?= role_color(current_role()) ?>"><?= e(current_role()) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (can_edit()): ?>
                            <li><a class="dropdown-item" href="<?= url('account') ?>">Mon compte</a></li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <li>
                            <form method="post" action="<?= url('logout') ?>" class="px-3">
                                <?= \App\Core\Csrf::field() ?>
                                <button class="btn btn-sm btn-outline-danger w-100" type="submit">Se deconnecter</button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container-fluid py-4 px-4">
    <?php foreach (take_flashes() as $f): ?>
        <div class="alert alert-<?= e($f['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($f['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
        </div>
    <?php endforeach; ?>

    <?= $content ?>
</main>

<footer class="text-center text-muted py-4 small">
    Lycee Jean Rostand &middot; Roubaix &mdash; Gestion de l'historique des stages
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
</body>
</html>