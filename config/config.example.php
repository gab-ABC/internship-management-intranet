<?php
/**
 * Exemple de configuration.
 *
 * COPIEZ ce fichier en  config/config.php  sur le serveur, puis renseignez
 * vos identifiants MySQL (fournis par alwaysdata).
 *
 * Le fichier config/config.php NE DOIT PAS etre versionne (il est ignore
 * par Git) ni deploye : il contient des informations sensibles.
 */

return [
    // --- Connexion a la base de donnees MySQL ---
    'db' => [
        'host'    => 'mysql-VOTRECOMPTE.alwaysdata.net',
        'name'    => 'VOTRECOMPTE_stages',
        'user'    => 'VOTRECOMPTE',
        'pass'    => 'VOTRE_MOT_DE_PASSE',
        'charset' => 'utf8mb4',
    ],

    // Mettre a false en production pour ne pas afficher les erreurs detaillees.
    'debug' => false,
];
