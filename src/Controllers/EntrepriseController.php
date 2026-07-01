<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Models\Entreprise;

/**
 * Gestion des entreprises (listing, ajout, modification, suppression).
 * Reserve aux roles admin et prof.
 */
class EntrepriseController extends Controller
{
    public function __construct()
    {
        $this->authorize('admin', 'prof', 'eleve');
    }

    public function index(): void
    {
        $q = trim($_GET['q'] ?? '');
        $sort = $_GET['sort'] ?? '';
        $this->render('entreprises/index', [
            'entreprises' => Entreprise::allWithStageCount($q, $sort),
            'q'           => $q,
            'sort'        => $sort,
        ], 'Entreprises');
    }

    public function create(): void
    {
        $this->render('entreprises/form', ['entreprise' => null, 'old' => [], 'errors' => []], 'Nouvelle entreprise');
    }

    public function store(): void
    {
        $in = $this->collectInput();
        $errors = $this->validate($in, null);

        if ($errors) {
            $this->render('entreprises/form', ['entreprise' => null, 'old' => $in, 'errors' => $errors], 'Nouvelle entreprise');
            return;
        }

        Entreprise::findOrCreate($in['nom'], $in['adresse'], $in['code_postal'], $in['ville']);
        flash('Entreprise ajoutée avec succès.');
        redirect('entreprises');
    }

    public function edit(): void
    {
        $entreprise = Entreprise::find((int) ($_GET['id'] ?? 0));
        if (!$entreprise) {
            redirect('entreprises');
        }
        $this->render('entreprises/form', ['entreprise' => $entreprise, 'old' => [], 'errors' => []], 'Modifier l\'entreprise');
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $entreprise = Entreprise::find($id);
        if (!$entreprise) {
            redirect('entreprises');
        }

        $in = $this->collectInput();
        $errors = $this->validate($in, $id);

        if ($errors) {
            $this->render('entreprises/form', ['entreprise' => $entreprise, 'old' => $in, 'errors' => $errors], 'Modifier l\'entreprise');
            return;
        }

        Entreprise::update($id, $in['nom'], $in['adresse'], $in['code_postal'], $in['ville']);
        flash('Entreprise modifiée avec succès.');
        redirect('entreprises');
    }

    public function pdf(): void
    {
        $q = trim($_GET['q'] ?? '');
        $sort = $_GET['sort'] ?? '';
        $entreprises = Entreprise::allWithStageCount($q, $sort);
        \App\Lib\EntreprisePdf::download($entreprises, $q);
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            Entreprise::delete($id);
            flash('Entreprise supprimée. Les stages associés ne référencent plus d\'entreprise.', 'warning');
        }
        redirect('entreprises');
    }

    /* ----------------------------------------------------------------- */

    private function collectInput(): array
    {
        return [
            'nom'         => trim($_POST['nom'] ?? ''),
            'adresse'     => trim($_POST['adresse'] ?? ''),
            'code_postal' => trim($_POST['code_postal'] ?? ''),
            'ville'       => trim($_POST['ville'] ?? ''),
        ];
    }

    private function validate(array $in, ?int $id): array
    {
        $errors = [];
        if ($in['nom'] === '') {
            $errors['nom'] = 'Le nom de l\'entreprise est obligatoire.';
        } elseif (Entreprise::normalize($in['nom']) === '') {
            $errors['nom'] = 'Le nom de l\'entreprise est invalide.';
        } elseif ($id !== null && Entreprise::nameExists($in['nom'], $id)) {
            $errors['nom'] = 'Une autre entreprise porte déjà ce nom.';
        }
        return $errors;
    }
}
