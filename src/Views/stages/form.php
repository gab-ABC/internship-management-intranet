<?php defined('APP_RUNNING') or exit('Acces direct interdit.'); ?>
<?php
$isEdit = $stage !== null;
// Valeur affichee : ancienne saisie > valeur du stage existant > defaut.
$val = fn($key, $default = '') => e($old[$key] ?? ($stage[$key] ?? $default));
$action = $isEdit ? url('stage_update') : url('stage_store');
?>

<h1 class="h3 mb-4"><?= $isEdit ? 'Modifier le stage' : 'Ajouter un stage' ?></h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <strong>Veuillez corriger les erreurs suivantes :</strong>
        <ul class="mb-0">
            <?php foreach ($errors as $msg): ?><li><?= e($msg) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= $action ?>" class="card card-body" novalidate>
    <?= \App\Core\Csrf::field() ?>
    <?php if ($isEdit): ?>
        <input type="hidden" name="id" value="<?= (int) $stage['id'] ?>">
    <?php endif; ?>

    <h2 class="h5 border-bottom pb-2">Étudiant (stagiaire)</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Nom *</label>
            <input type="text" name="etudiant_nom" class="form-control" required value="<?= $val('etudiant_nom') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Prénom *</label>
            <input type="text" name="etudiant_prenom" class="form-control" required value="<?= $val('etudiant_prenom') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="etudiant_email" class="form-control" value="<?= $val('etudiant_email') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label phone-format">Téléphone</label>
            <input type="tel" name="etudiant_telephone" class="form-control" maxlength="14"
                   placeholder="06 12 34 56 78" value="<?= $val('etudiant_telephone') ?>" id="etudiant_telephone">
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Entreprise</h2>
    <p class="text-muted small">Choisissez une entreprise existante <strong>ou</strong> saisissez-en une nouvelle ci-dessous.
       Les doublons sont automatiquement évités.</p>
    <div class="row g-3 mb-2">
        <div class="col-md-6">
            <label class="form-label">Entreprise existante</label>
            <select name="entreprise_id" class="form-select" id="select-entreprise">
                <option value="">— Nouvelle entreprise —</option>
                <?php $selEnt = $old['entreprise_id'] ?? ($stage['entreprise_id'] ?? ''); ?>
                <?php foreach ($entreprises as $ent): ?>
                    <option value="<?= (int) $ent['id'] ?>" <?= selected($ent['id'], $selEnt) ?>>
                        <?= e($ent['nom']) ?><?= $ent['ville'] ? ' (' . e($ent['ville']) . ')' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row g-3 mb-4" id="bloc-nouvelle-entreprise">
        <div class="col-md-6">
            <label class="form-label">Nom de la nouvelle entreprise</label>
            <input type="text" name="entreprise_nom" class="form-control" value="<?= $val('entreprise_nom') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">Adresse</label>
            <input type="text" name="entreprise_adresse" class="form-control" value="<?= $val('entreprise_adresse') ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Code postal</label>
            <input type="text" name="entreprise_cp" class="form-control" value="<?= $val('entreprise_cp') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Ville</label>
            <input type="text" name="entreprise_ville" class="form-control" value="<?= $val('entreprise_ville') ?>">
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Stage</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <label class="form-label">Année</label>
            <input type="number" name="annee" class="form-control" min="2000" max="2100"
                   value="<?= $val('annee', date('Y')) ?>">
        </div>
        <div class="col-md-3">
            <label class="form-label">Formation</label>
            <input type="text" name="formation" class="form-control" list="formations-list"
                   value="<?= $val('formation', 'BTS CIEL') ?>">
            <datalist id="formations-list">
                <option value="BTS CIEL"></option>
                <option value="BTS SNIR"></option>
                <option value="BTS IRIS"></option>
            </datalist>
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Professeur</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Professeur référent</label>
            <?php $selProf = $old['prof_referent_id'] ?? ($stage['prof_referent_id'] ?? ''); ?>
            <select name="prof_referent_id" class="form-select" id="select-professeur">
                <option value="">— Nouveau professeur référent —</option>
                <?php foreach ($professeurs as $prof): ?>
                    <option value="<?= (int) $prof['id'] ?>" <?= selected($prof['id'], $selProf) ?>>
                        <?= e($prof['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6 d-none" id="bloc-nouveau-professeur">
            <label class="form-label">Professeur référent</label>
            <input type="text" name="prof_referent" class="form-control" value="<?= $val('prof_referent') ?>">
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Période</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Date de début</label>
            <input type="date" name="date_debut" class="form-control" value="<?= $val('date_debut') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="<?= $val('date_fin') ?>">
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Tuteur en entreprise</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label">Nom du tuteur</label>
            <input type="text" name="tuteur_nom" class="form-control" value="<?= $val('tuteur_nom') ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label phone-format">Téléphone</label>
            <input type="tel" name="tuteur_tel" class="form-control" maxlength="14"
                   placeholder="06 12 34 56 78" value="<?= $val('tuteur_tel') ?>" id="tuteur_telephone">
        </div>
        <div class="col-md-4">
            <label class="form-label">E-mail</label>
            <input type="email" name="tuteur_email" class="form-control" value="<?= $val('tuteur_email') ?>">
        </div>
    </div>

    <h2 class="h5 border-bottom pb-2">Responsable (optionnel)</h2>
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label">Nom du responsable</label>
            <input type="text" name="responsable_nom" class="form-control" value="<?= $val('responsable_nom') ?>">
        </div>
        <div class="col-md-6">
            <label class="form-label">E-mail</label>
            <input type="email" name="responsable_email" class="form-control" value="<?= $val('responsable_email') ?>">
        </div>
    </div>

    <div class="d-flex gap-2">
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Enregistrer les modifications' : 'Enregistrer le stage' ?></button>
        <a href="<?= url('stages') ?>" class="btn btn-outline-secondary">Annuler</a>
    </div>
</form>
<script src="assets/js/select.js"></script>
