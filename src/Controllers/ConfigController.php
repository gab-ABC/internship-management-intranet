<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Models\Etablissement;

/*
*   Controleur pour la page de configuration du site (accessible uniquement aux administrateurs).
*/
class ConfigController extends Controller
{
    public function __construct()
    {
        $this->authorize('admin');
    }

    public function index()
    {
        $this->render('config/index', 
        [
            "nom" => Etablissement::getEtablissementName(),
            "errors"=> [],
        ], "Configuration");
    }

    public function update()
    {
        $nom = trim($_POST['nom_etablissement'] ?? '');
        
        Etablissement::setEtablissementName($nom);
        redirect('home');
    }
}