<?php
/**
 * Amorce de l'application (bootstrap).
 *
 * Charge la configuration, demarre une session securisee et met en place
 * un autoloader simple pour les classes du dossier src/.
 *
 * Toutes les classes utilisent l'espace de noms "App\..." qui correspond
 * a l'arborescence du dossier src/ (ex: App\Core\Database -> src/Core/Database.php).
 */

declare(strict_types=1);

// Constante de securite : les fichiers inclus verifient sa presence pour
// refuser tout acces direct par URL.
define('APP_RUNNING', true);

define('ROOT_PATH', dirname(__DIR__));
define('SRC_PATH', __DIR__);

// --- Chargement de la configuration ---
$configFile = ROOT_PATH . '/config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Configuration manquante : copiez config/config.example.php en config/config.php.');
}
$config = require $configFile;

// Affichage des erreurs uniquement en mode debug.
if (!empty($config['debug'])) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

// --- Autoloader PSR-4 minimal pour le namespace App\ ---
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = SRC_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Fonctions utilitaires globales (non gerees par l'autoloader de classes).
require SRC_PATH . '/Core/helpers.php';

// --- Session securisee ---
session_set_cookie_params([
    'httponly' => true,                                   // inaccessible au JavaScript
    'samesite' => 'Lax',                                  // limite les requetes cross-site
    'secure'   => (!empty($_SERVER['HTTPS'])),            // cookie HTTPS uniquement si dispo
]);
session_start();

// Rend la configuration accessible aux classes Core.
\App\Core\Database::setConfig($config['db']);
$GLOBALS['app_config'] = $config;
