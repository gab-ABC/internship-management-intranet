<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Models\Stage;

/**
 * Page d'accueil avec quelques statistiques.
 */
class HomeController extends Controller
{
    public function index(): void
    {
        $stats = Stage::stats();
        $this->render('home/index', ['stats' => $stats], 'Accueil');
    }
}
