<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<?php
$pdfQuery = http_build_query(['p' => 'entreprises_pdf', 'q' => $q,'sort' => $sort]);
?>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Entreprises</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="index.php?<?= e($pdfQuery) ?>" class="btn btn-danger">Générer un PDF à partir des filtres sélectionnés</a>
        <?php if (can_edit()): ?>
            <a href="<?= url('entreprise_create') ?>" class="btn btn-primary">+ Nouvelle entreprise</a>
        <?php endif; ?>
    </div>
</div>

<form method="get" class="card card-body mb-3">
    <input type="hidden" name="p" value="entreprises">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-9">
            <label class="form-label small">Recherche (nom ou ville)</label>
            <input type="text" name="q" class="form-control" value="<?= e($q) ?>" placeholder="Nom d'entreprise, ville...">
        </div>
        <div class="col-12 col-md-3 d-grid">
            <button class="btn btn-outline-primary" type="submit">Filtrer</button>
        </div>
    </div>
</form>

<p class="text-muted small"><?= count($entreprises) ?> entreprise(s).</p>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle bg-white">
        <thead class="table-dark">
            <tr>
                <th>Nom</th>
                <th>Adresse</th>
                <th>CP</th>
                <th>Ville</th>
                <?php
                // Tri dynamique sur le nombre de stages (croissant / decroissant).
                $nextSort = ($sort === 'stages_desc') ? 'stages_asc' : 'stages_desc';
                $arrow = ($sort === 'stages_asc') ? '▲' : (($sort === 'stages_desc') ? '▼' : '⇅');
                $sortUrl = 'index.php?p=entreprises&sort=' . $nextSort
                    . ($q !== '' ? '&q=' . urlencode($q) : '');
                ?>
                <th>
                    <a href="<?= e($sortUrl) ?>" class="text-white text-decoration-none"
                       title="Trier par nombre de stages">Stages <?= $arrow ?></a>
                </th>
                <?php if (can_edit()): ?>
                    <th class="text-end">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($entreprises)): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">Aucune entreprise.</td></tr>
        <?php endif; ?>
        <?php foreach ($entreprises as $en): ?>
            <tr>
                <td><?= e($en['nom']) ?></td>
                <td class="small"><?= e($en['adresse'] ?? '') ?></td>
                <td class="small"><?= e($en['code_postal'] ?? '') ?></td>
                <td><?= e($en['ville'] ?? '') ?></td>
                <td><span class="badge text-bg-info"><?= (int) $en['nb_stages'] ?></span></td>
                <?php if (can_edit()): ?>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('entreprise_edit') ?>&id=<?= (int) $en['id'] ?>"
                        class="btn btn-sm btn-outline-primary" title="Modifier">⚙️</a>
                        <form method="post" action="<?= url('entreprise_delete') ?>" class="d-inline"
                            onsubmit="return confirm('Supprimer cette entreprise ? Les stages associés ne référenceront plus d\'entreprise.');">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $en['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" title="Supprimer">&times;</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
