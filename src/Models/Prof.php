<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

class Prof
{
    public static function all()
    {
        return Database::run('SELECT * FROM professeurs ORDER BY nom')->fetchAll(); 
    }

    public static function find(int $id)
    {
        return Database::run('SELECT * FROM professeurs WHERE id = ?', [$id])->fetch();
    }

    public static function update(int $id, string $nom)
    {
        Database::run('UPDATE professeurs SET nom = ? WHERE id = ?', [trim($nom), $id]);
    }

    public static function delete(int $id)
    {
        Database::run('DELETE FROM professeurs WHERE id = ?', [$id]);
    }

    public static function create(string $nom)
    {
        Database::run('INSERT INTO professeurs (nom) VALUES (?)', [trim($nom)]);
        return (int) Database::pdo()->lastInsertId();
    }

    public static function findOrCreate(string $nom)
    {
        $existing = Database::run('SELECT id FROM professeurs WHERE nom = ?', [trim($nom)])->fetchColumn();

        if($existing) { // On vérifie si un résultat a été retourné SI VRAI alors il existe déjà
            return (int) $existing;
        }

        Database::run('INSERT INTO professeurs (nom) VALUES (?)', [trim($nom)]); // trim() retire les caractères spéciaux

        return (int) Database::pdo()->lastInsertId();
    }
}