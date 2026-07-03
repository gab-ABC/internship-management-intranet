const btnToggleSelect = document.getElementById('btn-toggle-select');
const btnSelectAll = document.getElementById('btn-select-all');
const btnBulkDelete = document.getElementById('btn-bulk-delete');

// Récupère toujours les checkboxes à jour (utile si le tableau est re-rendu en AJAX)
function getStageCheckboxes() {
    return document.querySelectorAll('input[name="stages[]"]');
}

// --- Bouton "Sélectionner des stages" ---
btnToggleSelect.addEventListener('click', function (e) {
    e.preventDefault();

    let isSelecting = this.getAttribute('data-selecting') === 'true';
    isSelecting = !isSelecting;
    this.setAttribute('data-selecting', isSelecting);

    this.textContent = isSelecting ? 'Annuler les sélections' : 'Sélectionner des stages';

    document.querySelectorAll('.stage-checkbox').forEach(cell => {
        cell.style.display = isSelecting ? 'table-cell' : 'none';
    });

    // Le bouton "Tout sélectionner" n'a de sens que si on est en mode sélection
    btnSelectAll.style.display = isSelecting ? 'inline-block' : 'none';

    // Si on quitte le mode sélection, on décoche tout et on masque "Supprimer"
    if (!isSelecting) {
        getStageCheckboxes().forEach(cb => (cb.checked = false));
        btnBulkDelete.style.display = 'none';
        btnSelectAll.textContent = 'Tout sélectionner';
        btnSelectAll.setAttribute('data-all-selected', 'false');
    }
});

// --- Bouton "Tout sélectionner / Tout désélectionner" ---
btnSelectAll.addEventListener('click', function (e) {
    e.preventDefault();

    let allSelected = this.getAttribute('data-all-selected') === 'true';
    allSelected = !allSelected;
    this.setAttribute('data-all-selected', allSelected);

    this.textContent = allSelected ? 'Tout désélectionner' : 'Tout sélectionner';

    getStageCheckboxes().forEach(cb => {
        cb.checked = allSelected;
    });

    updateBulkDeleteVisibility();
});

// --- Affiche/masque "Supprimer la sélection" selon qu'au moins une case est cochée ---
function updateBulkDeleteVisibility() {
    const anyChecked = Array.from(getStageCheckboxes()).some(cb => cb.checked);
    btnBulkDelete.style.display = anyChecked ? 'inline-block' : 'none';
}

// Écoute chaque checkbox individuellement : si l'utilisateur décoche une case
// à la main, on doit aussi mettre à jour le bouton "Tout sélectionner"
// et l'affichage du bouton de suppression.
getStageCheckboxes().forEach(cb => {
    cb.addEventListener('change', function () {
        updateBulkDeleteVisibility();

        const total = getStageCheckboxes().length;
        const checkedCount = Array.from(getStageCheckboxes()).filter(c => c.checked).length;

        btnSelectAll.setAttribute('data-all-selected', checkedCount === total ? 'true' : 'false');
        btnSelectAll.textContent = checkedCount === total ? 'Tout désélectionner' : 'Tout sélectionner';
    });
});

// --- État initial au chargement de la page ---
document.querySelectorAll('.stage-checkbox').forEach(cell => {
    cell.style.display = 'none';
});