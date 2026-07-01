<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

/**
 * Modele Etudiant : acces a la table `etudiants`.
 */
class Etudiant
{
    public static function find(int $id): ?array
    {
        $row = Database::run('SELECT * FROM etudiants WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    /**
     * Cree un etudiant, ou reutilise un homonyme deja present
     * (meme nom et meme prenom).
     */
    public static function findOrCreate(string $nom, string $prenom, ?string $email, ?string $telephone = null): int
    {
        $existing = Database::run(
            'SELECT id FROM etudiants WHERE nom = ? AND prenom = ?',
            [trim($nom), trim($prenom)]
        )->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        Database::run(
            'INSERT INTO etudiants (nom, prenom, email, telephone) VALUES (?, ?, ?, ?)',
            [trim($nom), trim($prenom), $email ?: null, $telephone ?: null]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, string $nom, string $prenom, ?string $email, ?string $telephone): void
    {
        Database::run(
            'UPDATE etudiants SET nom = ?, prenom = ?, email = ?, telephone = ? WHERE id = ?',
            [trim($nom), trim($prenom), $email ?: null, $telephone ?: null, $id]
        );
    }
}
