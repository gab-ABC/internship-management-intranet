<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Models\Stage;
use App\Models\Entreprise;
use App\Models\Etudiant;
use App\Models\Tuteur;
use App\Models\Prof;

/**
 * Listing / recherche des stages, ajout, modification et export PDF.
 */
class StageController extends Controller
{
    /** Longueur maximale autorisee pour un numero de telephone. */
    private const TEL_MAX = 20;

    /** Lit les filtres de recherche depuis l'URL. */
    private function readFilters(): array
    {
        return [
            'q'          => trim($_GET['q'] ?? ''),
            'annee'      => trim($_GET['annee'] ?? ''),
            'formation'  => $_GET['formation'] ?? '',
            'ville'      => trim($_GET['ville'] ?? ''),
            'telephone'  => trim($_GET['telephone'] ?? ''),
            'email'      => trim($_GET['email'] ?? ''),
            'prof'       => trim($_GET['prof'] ?? ''),
        ];
    }

    /** Listing avec recherche simple et avancee (tous les roles connectes). */
    public function index(): void
    {
        $filters = $this->readFilters(); // Les filtres sont lus dans l'URL pour etre passes a la vue et au lien PDF.

        $advancedActive = $filters['ville'] !== '' || $filters['telephone'] !== ''
            || $filters['email'] !== '' || $filters['prof'] !== '';

        $this->render('stages/index', [
            'stages'         => Stage::search($filters),
            'aggregates'     => Stage::companyAggregates(),
            'filters'        => $filters,
            'annees'         => Stage::distinctYears(),
            'formations'     => Stage::distinctFormations(),
            'advancedActive' => $advancedActive,
        ], 'Liste des stages');
    }

    /** Genere et telecharge un PDF du tableau filtre. */
    public function pdf(): void
    {
        $filters = $this->readFilters();
        $stages = Stage::search($filters);
        \App\Lib\StagePdf::download($stages, $filters);
    }

    /** Formulaire d'ajout (admin et prof uniquement). */
    public function create(): void
    {
        $this->authorize('admin', 'prof');
        $this->render('stages/form', [
            'stage' => null, 'entreprises' => Entreprise::all(), 'professeurs' => Prof::all(), 'old' => [], 'errors' => [],
        ], 'Ajouter un stage');
    }

    /** Enregistrement d'un nouveau stage. */
    public function store(): void
    {
        $this->authorize('admin', 'prof');

        $in = $this->collectInput();
        $errors = $this->validate($in);

        if ($errors) {
            $this->render('stages/form', 
            [
                'stage' => null, 'entreprises' => Entreprise::all(), 'professeurs' => Prof::all(), 'old' => $in, 'errors' => $errors,
            ], 'Ajouter un stage');
            return;
        }

        $entrepriseId = $this->resolveEntreprise($in);
        $profId       = $this->resolveProf($in); // La fonction n'est pas pertinente mais c'est histoire de montrer que je fais ce que je veux TU VAS FAIRE QUOI
        $tuteurId     = $this->resolveTuteur($in);
        $etudiantId   = Etudiant::findOrCreate($in['etudiant_nom'], $in['etudiant_prenom'], $in['etudiant_email'], $in['etudiant_telephone']);

        Stage::create($this->stageData($in, $etudiantId, $entrepriseId, $tuteurId, $profId));

        flash('Stage ajouté avec succès.');
        redirect('stages');
    }

    /** Formulaire de modification d'un stage. */
    public function edit(): void
    {
        $this->authorize('admin', 'prof');
        $stage = Stage::find((int) ($_GET['id'] ?? 0));
        if (!$stage) {
            redirect('stages');
        }
        $this->render('stages/form', [
            'stage' => $stage, 'entreprises' => Entreprise::all(), 'professeurs' => Prof::all(), 'old' => [], 'errors' => [],
        ], 'Modifier le stage');
    }

    /** Enregistrement des modifications d'un stage. */
    public function update(): void
    {
        $this->authorize('admin', 'prof');
        $id    = (int) ($_POST['id'] ?? 0);
        $stage = Stage::find($id);

        if (!$stage) redirect('stages');

        $in     = $this->collectInput();
        $errors = $this->validate($in);

        if ($errors) 
        {
            $this->render('stages/form', 
            [
                'stage' => $stage, 'entreprises' => Entreprise::all(), 'professeurs' => Prof::all(), 'old' => $in, 'errors' => $errors,
            ], 'Modifier le stage');
            return;
        }

        $entrepriseId = $this->resolveEntreprise($in);
        $tuteurId     = $this->resolveTuteur($in);
        $profId       = $this->resolveProf($in);

        Etudiant::update((int) $stage['etudiant_id'], $in['etudiant_nom'], $in['etudiant_prenom'], $in['etudiant_email'], $in['etudiant_telephone']);
        Stage   ::update($id, $this->stageData($in, (int) $stage['etudiant_id'], $entrepriseId, $tuteurId, $profId));

        flash   ('Stage modifié avec succès.');
        redirect('stages');
    }

    /** Suppression d'un stage (admin et prof). */
    public function destroy(): void
    {
        $this->authorize('admin', 'prof');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            Stage::delete($id);
            flash('Stage supprimé.');
        }
        redirect('stages');
    }

    /* ----------------------------------------------------------------- */

    private function resolveEntreprise(array $in): ?int
    {
        if ($in['entreprise_id'] !== '') {
            return (int) $in['entreprise_id'];
        }
        if(empty($in['entreprise_nom']) && empty($in['entreprise_adresse']) && empty($in['entreprise_cp']) && empty($in['entreprise_ville']))
        {
            return null;
        }
        return Entreprise::findOrCreate($in['entreprise_nom'], $in['entreprise_adresse'],$in['entreprise_cp'], $in['entreprise_ville']);
    }
    private function resolveTuteur(array $in): ?int
    {
        if ($in['tuteur_id'] !== '') {
            return (int) $in['tuteur_id'];
        }
        if(empty($in['tuteur_nom']) && empty($in['tuteur_tel']) && empty($in['tuteur_email']))
        {
            return null;
        }
        return Tuteur::findOrCreate($in['tuteur_id'], $in['tuteur_nom'], $in['tuteur_tel'], $in['tuteur_email']);
    }
    private function resolveProf(array $in): ?int
    {
        if ($in['prof_referent_id'] !== '') {
            return (int) $in['prof_referent_id'];
        }
        if (empty($in['prof_referent'])) {
            return null;
        }
        return Prof::findOrCreate($in['prof_referent']);
    }

    private function stageData(array $in, int $etudiantId, ?int $entrepriseId, ?int $tuteurId, ?int $profId): array
    {
        return [
            'etudiant_id'       => $etudiantId,
            'entreprise_id'     => $entrepriseId,
            'tuteur_id'         => $tuteurId,
            'annee'             => $in['annee'],
            'formation'         => $in['formation'],
            'date_debut'        => $in['date_debut'] ?: null,
            'date_fin'          => $in['date_fin'] ?: null,
            'prof_referent_id'  => $profId,
            'responsable_nom'   => $in['responsable_nom'],
            'responsable_email' => $in['responsable_email'],
        ];
    }

    private function collectInput(): array
    {
        $fields = [
            'entreprise_id', 'entreprise_nom', 'entreprise_adresse', 'entreprise_cp',
            'entreprise_ville', 
            
            'etudiant_nom', 'etudiant_prenom', 'etudiant_email',
            'etudiant_telephone', 
            
            'annee', 
            
            'formation', 
            
            'date_debut', 'date_fin',
            
            'prof_referent_id','prof_referent',

            'tuteur_nom', 'tuteur_tel', 'tuteur_email', 'responsable_nom', 'responsable_email',
        ];
        $in = [];
        foreach ($fields as $f) {
            $in[$f] = trim((string) ($_POST[$f] ?? ''));
        }
        $in['etudiant_telephone'] = str_replace([' ', '.', '-', '(', ')'], '', $in['etudiant_telephone']);
        $in['tuteur_tel']         = str_replace([' ', '.', '-', '(', ')'], '', $in['tuteur_tel']);
        return $in;
    }

    private function validate(array $in): array
    {
        $errors = [];

        if ($in['etudiant_nom'] === '' || $in['etudiant_prenom'] === '') {
            $errors['etudiant'] = 'Le nom et le prénom de l\'étudiant sont obligatoires.';
        }
        if ($in['annee'] !== '' && (!ctype_digit($in['annee']) || (int) $in['annee'] < 2000 || (int) $in['annee'] > 2100)) {
            $errors['annee'] = 'Année invalide.';
        }
        foreach (['etudiant_email', 'tuteur_email', 'responsable_email'] as $f) {
            if ($in[$f] !== '' && !filter_var($in[$f], FILTER_VALIDATE_EMAIL)) {
                $errors[$f] = 'Adresse e-mail invalide.';
            }
        }
        foreach (['etudiant_telephone' => 'du stagiaire', 'tuteur_tel' => 'du tuteur'] as $f => $label) {
            if ($in[$f] !== '') {
                if (mb_strlen($in[$f]) > self::TEL_MAX) {
                    $errors[$f] = 'Le numéro de téléphone ' . $label . ' ne doit pas dépasser ' . self::TEL_MAX . ' caractères.';
                } elseif (!preg_match('/^[0-9 .+()\-]{4,' . self::TEL_MAX . '}$/', $in[$f])) {
                    $errors[$f] = 'Le numéro de téléphone ' . $label . ' est invalide (chiffres, espaces, + - . autorisés).';
                }
            }
        }
        return $errors;
    }
}
