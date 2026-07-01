<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<h1 class="h3 mb-4">Tableau de bord</h1>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-4">
        <div class="card text-center h-100 border-danger">
            <div class="card-body">
                <div class="display-5 fw-bold text-danger"><?= (int) $stats['total_stages'] ?></div>
                <div class="text-muted">Stages enregistrés</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card text-center h-100 border-danger">
            <div class="card-body">
                <div class="display-5 fw-bold text-danger"><?= (int) $stats['total_entreprises'] ?></div>
                <div class="text-muted">Entreprises partenaires</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-4">
        <div class="card text-center h-100 border-danger">
            <div class="card-body">
                <div class="display-5 fw-bold text-danger"><?= (int) $stats['total_etudiants'] ?></div>
                <div class="text-muted">Étudiants</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card h-100">
            <div class="card-header">Stages par année</div>
            <div class="card-body">
                <?php if (empty($stats['par_annee'])): ?>
                    <p class="text-muted mb-0">Aucune donnée.</p>
                <?php else: ?>
                    <?php
                    $max = max(array_map(fn($r) => (int) $r['nb'], $stats['par_annee']));
                    foreach ($stats['par_annee'] as $r):
                        $pct = $max > 0 ? round((int) $r['nb'] / $max * 100) : 0;
                    ?>
                        <div class="d-flex align-items-center mb-2">
                            <div style="width:60px" class="text-muted small"><?= e((string) $r['annee']) ?></div>
                            <div class="progress flex-grow-1" role="progressbar" style="height:18px">
                                <div class="progress-bar" style="width: <?= $pct ?>%"><?= (int) $r['nb'] ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header">Entreprises les plus sollicitées</div>
            <ul class="list-group list-group-flush">
                <?php foreach ($stats['top_entreprises'] as $r): ?>
                    <li class="list-group-item d-flex justify-content-between">
                        <span><?= e($r['nom']) ?></span>
                        <span class="badge text-bg-primary rounded-pill"><?= (int) $r['nb'] ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (empty($stats['top_entreprises'])): ?>
                    <li class="list-group-item text-muted">Aucune donnée.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>
