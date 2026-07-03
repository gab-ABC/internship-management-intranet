<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>

<?php
// Lien d'export PDF : reprend les filtres actuellement actifs.
$pdfQuery = http_build_query(array_merge(['p' => 'stages_pdf'], array_filter($filters, fn($v) => $v !== '')));
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h1 class="h3 mb-0">Liste des stages</h1>
    <div class="d-flex gap-2 flex-wrap">
        <a href="index.php?<?= e($pdfQuery) ?>" class="btn btn-danger">Générer un PDF à partir des filtres sélectionnés</a>
        <button id="btn-toggle-select" class="btn btn-warning" data-selecting="false">
            Sélectionner des stages
        </button>
        <?php if (can_edit()): ?>
            <a href="<?= url('stage_create') ?>" class="btn btn-primary">+ Ajouter un stage</a>
        <?php endif; ?>
    </div>
</div>

<!-- Filtres de recherche -->
<form method="get" class="card card-body mb-3">
    <input type="hidden" name="p" value="stages">
    <div class="row g-2 align-items-end">
        <div class="col-12 col-md-5">
            <label class="form-label small">Recherche (entreprise ou étudiant)</label>
            <input type="text" name="q" class="form-control" value="<?= e($filters['q']) ?>"
                   placeholder="Nom d'entreprise, nom ou prénom...">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">Année</label>
            <input type="text" name="annee" class="form-control" value="<?= e($filters['annee']) ?>"
                   placeholder="2026, 2027...">
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small">Formation</label>
            <select name="formation" class="form-select">
                <option value="">Toutes</option>
                <?php foreach ($formations as $f): ?>
                    <option value="<?= e($f) ?>" <?= selected($f, $filters['formation']) ?>><?= e($f) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-12 col-md-3 d-grid">
            <button class="btn btn-outline-primary" type="submit">Filtrer</button>
        </div>
    </div>

    <!-- Bouton d'ouverture de la recherche avancée (sans soulignement) -->
    <div class="mt-2">
        <button class="btn btn-sm btn-link p-0 text-decoration-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#advanced-search" aria-expanded="<?= $advancedActive ? 'true' : 'false' ?>">
            🔍 Recherche avancée
        </button>
    </div>

    <!-- Panneau de recherche avancée -->
    <div class="collapse <?= $advancedActive ? 'show' : '' ?>" id="advanced-search">
        <div class="row g-2 align-items-end mt-1 pt-2 border-top">
            <div class="col-12 col-md-4">
                <label class="form-label small">Ville</label>
                <input type="text" name="ville" class="form-control" value="<?= e($filters['ville']) ?>"
                       placeholder="ex: Lille">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small">Téléphone (étudiant ou tuteur)</label>
                <input type="tel" name="telephone" class="form-control" value="<?= e($filters['telephone']) ?>"
                       placeholder="ex: 0612...">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small">E-mail (étudiant, tuteur, responsable)</label>
                <input type="email" name="email" class="form-control" value="<?= e($filters['email']) ?>"
                       placeholder="ex: @entreprise.fr">
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small">Enseignant référent</label>
                <input type="text" name="prof" class="form-control" value="<?= e($filters['prof']) ?>"
                       placeholder="Nom du professeur référent">
            </div>
        </div>
        <div class="mt-2">
            <a href="<?= url('stages') ?>" class="btn btn-sm btn-outline-secondary">Réinitialiser les filtres</a>
        </div>
    </div>
</form>

<p class="text-muted small"><?= count($stages) ?> stage(s) trouvé(s).</p>

<form method="post" action="<?= url('stage_delete_array') ?>" id="form-bulk-delete"
      onsubmit="return confirm('Supprimer les stages sélectionnés ?');">
    <?= \App\Core\Csrf::field() ?>
</form>

<div class="table-responsive">
    <table class="table table-sm table-striped table-hover align-middle bg-white text-nowrap">
        <thead class="table-dark">
            <tr>
                <th class="text-center stage-checkbox"></th>
                <th>Année</th>
                <th>Étudiant</th>
                <th>Entreprise</th>
                <th>Ville</th>
                <th>Formation</th>
                <th>Historique</th>
                <th>Période</th>
                <th>Tuteur</th>
                <?php if (can_edit()): ?>
                    <th>Prof référent</th>
                    <th class="text-end">Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($stages)): ?>
            <?php $colCount = can_edit() ? 11 : 9; ?>
            <tr><td colspan="<?= $colCount ?>" class="text-center text-muted py-4">Aucun stage ne correspond à la recherche.</td></tr>
        <?php endif; ?>
        <?php foreach ($stages as $s): ?>
            <?php $agg = $aggregates[(int) $s['ent_id']] ?? null; ?>
            <tr class="table-row">
                <td class="text-center stage-checkbox">
                    <input type="checkbox" class="btn-check" name="stages[]"
                        id="stage-<?= e((string) $s['id']) ?>" value="<?= e((string) $s['id']) ?>"
                        form="form-bulk-delete" autocomplete="off">
                    <label class="btn btn-sm btn-outline-danger" for="stage-<?= e((string) $s['id']) ?>" aria-label="Sélectionner"></label>
                </td>
                <td><?= e((string) $s['annee']) ?></td>
                <td>
                    <?= e(strtoupper($s['etudiant_nom']) . ' ' . $s['etudiant_prenom']) ?>
                    <?php if (can_edit()): ?>
                        <?php if (!empty($s['etudiant_telephone'])): ?>
                            <div class="small text-muted">📞 <?= e(trim(chunk_split($s['etudiant_telephone'], 2, ' '))) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($s['etudiant_email'])): ?>
                            <div class="small text-muted">✉️ <?= e($s['etudiant_email']) ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td><?= e($s['entreprise_nom'] ?? '—') ?></td>
                <td>
                    <?= e($s['entreprise_ville'] ?? '—') ?>
                    <?php if(!empty($s['entreprise_adresse'])): ?>
                        <div class="small text-muted"> <?= e($s['entreprise_adresse'])?></div>
                    <?php endif ?>
                </td>
                <td><span class="badge text-bg-light border"><?= e($s['formation'] ?? '') ?></span></td>
                <td>
                    <?php if ($agg): ?>
                        <span class="badge text-bg-info" title="Nombre d'étudiants ayant fait un stage dans cette entreprise">
                            <?= $agg['nb_etudiants'] ?> étudiant(s)
                        </span>
                        <div class="small text-muted">Années : <?= e($agg['annees']) ?></div>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (!empty($s['date_debut']) && !empty($s['date_fin'])):?>
                            <?= date('d/m', strtotime($s['date_debut'])) ?> → <?= date('d/m', strtotime($s['date_fin'])) ?>
                    <?php else: ?>
                        <span class="text-muted">—</span>
                    <?php endif; ?>
                </td>
                <td class="small">
                    <?= e($s['tuteur_nom'] ?? '—') ?>
                    <?php /* Le téléphone du tuteur n'est visible que pour admin/prof. */ ?>
                    <?php if (can_edit() && !empty($s['tuteur_tel'])): ?>
                        <div class="text-muted">📞 <?= e(trim(chunk_split($s['tuteur_tel'], 2, ' '))) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($s['tuteur_email'])): ?>
                        <div class="text-muted">✉️ <?= e($s['tuteur_email']) ?></div>
                    <?php endif; ?>
                </td>
                <?php if (can_edit()): ?>
                    <td class="small"><?= e($s['prof_nom'] ?? '') ?></td>
                    <td class="text-end text-nowrap">
                        <a href="<?= url('stage_edit') ?>&id=<?= (int) $s['id'] ?>"
                        class="btn btn-sm btn-outline-primary" title="Modifier le stage">⚙️</a>
                        <form method="post" action="<?= url('stage_delete') ?>" class="d-inline"
                            onsubmit="return confirm('Supprimer ce stage ?');">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" aria-label="Supprimer" type="submit" title="Supprimer">&times;</button>
                        </form>
                    </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-flex gap-2 mt-2 position-sticky bottom-0 bg-white py-2 border-top">
    <button id="btn-select-all" class="btn btn-outline-secondary" style="display:none">
        Tout sélectionner
    </button>
    <button type="submit" form="form-bulk-delete" id="btn-bulk-delete"
            class="btn btn-danger" style="display:none">
        Supprimer la sélection
    </button>
</div>

<?php if (!can_edit()): ?>
    <p class="text-muted small">
        En tant qu'élève, vous consultez l'historique des stages. L'e-mail du tuteur est affiché,
        mais pas les numéros de téléphone.
    </p>
<?php endif; ?>

<script src="assets/js/can_select.js"></script>
