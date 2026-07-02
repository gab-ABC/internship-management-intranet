<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

class Etablissement
{
    public static function getEtablissementName(): string
    {
        $row = Database::run('SELECT nom FROM etablissement LIMIT 1')->fetch();
        return $row['nom'] ?? 'Aucun nom d\'établissement défini';
    }

    public static function setEtablissementName(string $nom): void
    {
        Database::run('UPDATE etablissement SET nom = ? LIMIT 1', [$nom]);
    }
}