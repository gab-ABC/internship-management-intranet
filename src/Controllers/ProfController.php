<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Models\Prof;

class ProfController extends Controller
{
    public function __construct()
    {
        $this->authorize('admin', 'prof');
    }

    public function index(): void
    {
        $this->render('profs/index', [
            'professeurs' => Prof::all(),
        ], 'Professeurs');
    }

    public function create(): void
    {
        $this->render('profs/form', [
            'prof' => null,
            'errors' => [],
        ], 'Ajouter un professeur');
    }

    public function edit(): void
    {
        $id = (int) $_GET['id'] ?? 0;
        $prof = Prof::find($id);

        if (!$prof) {
            abort(404);
        }

        $this->render('profs/form', [
            'prof' => $prof,
            'errors' => [],
        ], 'Modifier un professeur');
    }

    public function update(): void
    {
        $id = (int) $_POST['id'] ?? 0;
        $nom = trim($_POST['nom'] ?? '');

        if ($id <= 0 || empty($nom)) {
            $this->render('profs/form', [
                'prof' => ['id' => $id, 'nom' => $nom],
                'errors' => ['Nom du professeur requis.'],
            ], 'Modifier un professeur');
            return;
        }
        Prof::update($id, $nom);

        flash('Professeur modifié.');
        redirect('profs');
    }

    public function store(): void
    {
        $nom = trim($_POST['nom'] ?? '');

        if (empty($nom)) {
            $this->render('profs/form', [
                'prof' => ['nom' => $nom],
                'errors' => ['Nom du professeur requis.'],
            ], 'Ajouter un professeur');
            return;
        }

        Prof::findOrCreate($nom);

        flash('Professeur ajouté.');
        redirect('profs');
    }

    public function destroy(): void
    {
        $id = (int) $_POST['id'] ?? 0;

        if ($id <= 0 || $id === current_user()['id']) {
            abort(400);
        }

        Prof::delete($id);

        flash('Professeur supprimé.');
        redirect('profs');
    }
}