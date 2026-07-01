<?php
/**
 * Import d'un fichier CSV de stages dans la base de donnees existante.
 *
 * Format de colonnes attendu (en-tete, separateur virgule) :
 *   Nom,Prénom,Date_Naissance,Groupe,Email,Telephone,Entreprise,Adresse,
 *   Tuteur,Tel_Tuteur,Email_Tuteur,Prof_Referent,Annee
 *
 * L'annee peut etre une annee scolaire ("2025-2026") : on conserve l'annee
 * de debut (2025).
 *
 * Utilisation (en ligne de commande, sur le serveur) :
 *   php scripts/import_csv.php chemin/vers/fichier.csv
 *
 * Les doublons sont evites : entreprises (nom normalise), tuteurs (entreprise
 * + nom), etudiants (nom + prenom), stages (etudiant + annee + entreprise).
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("Ce script doit etre lance en ligne de commande.\n");
}
if ($argc < 2) {
    exit("Usage : php scripts/import_csv.php <fichier.csv>\n");
}
$csvPath = $argv[1];
if (!is_file($csvPath)) {
    exit("Fichier introuvable : $csvPath\n");
}

// --- Mini amorcage (sans session) ---
define('APP_RUNNING', true);
define('SRC_PATH', dirname(__DIR__) . '/src');

$configFile = dirname(__DIR__) . '/config/config.php';
if (!is_file($configFile)) {
    exit("Configuration manquante : creez config/config.php.\n");
}
$config = require $configFile;

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) === 0) {
        $file = SRC_PATH . '/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require $file;
        }
    }
});

use App\Core\Database;
use App\Models\Entreprise;
use App\Models\Tuteur;
use App\Models\Etudiant;
use App\Models\Stage;

Database::setConfig($config['db']);

/** Decoupe "12 rue X-59100 Ville" en [adresse, cp, ville]. */
function decoupe_adresse(string $full): array
{
    $full = trim(preg_replace('/\s+/', ' ', $full));
    if ($full === '') {
        return ['', '', ''];
    }
    if (preg_match('/(.*?)[-,\s]*\b(\d{4,5})\b[\s-]*(.+)$/u', $full, $m)) {
        return [trim($m[1], " ,-"), $m[2], trim($m[3], " ,-")];
    }
    return [$full, '', ''];
}

$in = fopen($csvPath, 'r');
$header = fgetcsv($in);
// Retire un eventuel BOM sur la premiere colonne.
if ($header && isset($header[0])) {
    $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
}
$idx = array_flip($header);

$col = fn(array $r, string $name): string => trim((string) ($r[$idx[$name] ?? -1] ?? ''));

$ajoutes = 0;
$ignores = 0;
$doublons = 0;

while (($r = fgetcsv($in)) !== false) {
    $nom = $col($r, 'Nom');
    $prenom = $col($r, 'Prénom');
    $entNom = $col($r, 'Entreprise');

    // Ligne sans etudiant ou sans entreprise : pas de stage a creer.
    if (($nom === '' && $prenom === '') || $entNom === '') {
        $ignores++;
        continue;
    }

    $anneeRaw = $col($r, 'Annee');
    $annee = preg_match('/(\d{4})/', $anneeRaw, $m) ? (int) $m[1] : null;
    $formation = ($annee !== null && $annee >= 2023) ? 'BTS CIEL'
        : (($annee !== null && $annee >= 2016) ? 'BTS SNIR' : 'BTS IRIS');

    [$adresse, $cp, $ville] = decoupe_adresse($col($r, 'Adresse'));

    $entrepriseId = Entreprise::findOrCreate($entNom, $adresse, $cp, $ville);
    $tuteurId = Tuteur::findOrCreate($entrepriseId, $col($r, 'Tuteur'), $col($r, 'Tel_Tuteur'), $col($r, 'Email_Tuteur'));
    $etudiantId = Etudiant::findOrCreate($nom, $prenom, $col($r, 'Email'), $col($r, 'Telephone'));

    if (Stage::exists($etudiantId, $annee, $entrepriseId)) {
        $doublons++;
        continue;
    }

    Stage::create([
        'etudiant_id'       => $etudiantId,
        'entreprise_id'     => $entrepriseId,
        'tuteur_id'         => $tuteurId,
        'annee'             => $annee,
        'formation'         => $formation,
        'periode'           => null,
        'prof_referent'     => $col($r, 'Prof_Referent'),
        'responsable_nom'   => null,
        'responsable_email' => null,
        'convention_signee' => 0,
    ]);
    $ajoutes++;
}
fclose($in);

echo "Import termine.\n";
echo "  Stages ajoutes  : $ajoutes\n";
echo "  Doublons ignores: $doublons\n";
echo "  Lignes ignorees : $ignores (sans etudiant ou sans entreprise)\n";
