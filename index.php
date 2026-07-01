<?php
/**
 * Point d'entree unique de l'application (front controller).
 *
 * Toutes les requetes passent par ce fichier : il charge l'application,
 * declare les routes puis delegue au routeur.
 *
 * Une route = une cle "p" dans l'URL (ex: index.php?p=stages), un controleur,
 * une methode, et la liste des roles autorises ([] = page publique).
 */

require __DIR__ . '/src/bootstrap.php';

use App\Core\Router;

$router = new Router();

// --- Routes publiques (pas de connexion requise) ---
$router->add('login',  'AuthController', 'login');
$router->add('logout', 'AuthController', 'logout', ['admin', 'prof', 'eleve']);

// --- Accueil & compte (tous les roles connectes) ---
$router->add('home',             'HomeController', 'index',          ['admin', 'prof', 'eleve']);
$router->add('account',          'AuthController', 'account',        ['admin', 'prof']);
$router->add('account_password', 'AuthController', 'updatePassword', ['admin', 'prof']);

// --- Stages : consultation pour tous, ajout/modif/suppression pour admin+prof ---
$router->add('stages',       'StageController', 'index',   ['admin', 'prof', 'eleve']);
$router->add('stages_pdf',   'StageController', 'pdf',     ['admin', 'prof', 'eleve']);
$router->add('stage_create', 'StageController', 'create',  ['admin', 'prof']);
$router->add('stage_store',  'StageController', 'store',   ['admin', 'prof']);
$router->add('stage_edit',   'StageController', 'edit',    ['admin', 'prof']);
$router->add('stage_update', 'StageController', 'update',  ['admin', 'prof']);
$router->add('stage_delete', 'StageController', 'destroy', ['admin', 'prof']);

// --- Entreprises : gestion reservee a admin+prof ---
$router->add('entreprises',       'EntrepriseController', 'index',   ['admin', 'prof', 'eleve']);
$router->add('entreprise_create', 'EntrepriseController', 'create',  ['admin', 'prof']);
$router->add('entreprise_store',  'EntrepriseController', 'store',   ['admin', 'prof']);
$router->add('entreprise_edit',   'EntrepriseController', 'edit',    ['admin', 'prof']);
$router->add('entreprise_update', 'EntrepriseController', 'update',  ['admin', 'prof']);
$router->add('entreprise_delete', 'EntrepriseController', 'destroy', ['admin', 'prof']);
$router->add('entreprises_pdf',   'EntrepriseController', 'pdf',     ['admin', 'prof', 'eleve']);

// --- Professeurs : gestion reservee a admin+prof ---
$router->add('profs',       'ProfController', 'index',   ['admin', 'prof']);
$router->add('prof_create', 'ProfController', 'create',  ['admin', 'prof']);
$router->add('prof_store',  'ProfController', 'store',   ['admin', 'prof']);
$router->add('prof_edit',   'ProfController', 'edit',    ['admin', 'prof']);
$router->add('prof_update', 'ProfController', 'update',  ['admin', 'prof']);
$router->add('prof_delete', 'ProfController', 'destroy', ['admin', 'prof']);

// --- Utilisateurs : administrateur uniquement ---
$router->add('users',       'UserController', 'index',   ['admin']);
$router->add('user_create', 'UserController', 'create',  ['admin']);
$router->add('user_store',  'UserController', 'store',   ['admin']);
$router->add('user_edit',   'UserController', 'edit',    ['admin']);
$router->add('user_update', 'UserController', 'update',  ['admin']);
$router->add('user_delete', 'UserController', 'destroy', ['admin']);

$router->dispatch();
