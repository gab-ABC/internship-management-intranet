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
        $old = self::getEtablissementName();
        if ($old === 'Aucun nom d\'établissement défini') {
            Database::run('INSERT INTO etablissement (nom) VALUES (?)', [$nom]);
        } else {
            Database::run('UPDATE etablissement SET nom = ? LIMIT 1', [$nom]);
        }
    }

    public static function getEtablissementCity():string
    {
        $row = Database::run('SELECT ville FROM etablissement LIMIT 1')->fetch();
        return $row['ville'] ?? 'Aucune ville d\'établissement définie';
    }

    public static function setEtablissementCity(string $ville): void
    {
        Database::run('UPDATE etablissement SET ville = ? LIMIT 1', [$ville]);
    }
}