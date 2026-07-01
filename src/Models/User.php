<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

/**
 * Modele Utilisateur : acces a la table `users`.
 */
class User
{
    public const ROLES = ['admin', 'prof', 'eleve'];

    public static function all(): array
    {
        return Database::run(
            'SELECT id, username, full_name, role, created_at FROM users ORDER BY role, username'
        )->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::run('SELECT * FROM users WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $row = Database::run('SELECT * FROM users WHERE username = ?', [$username])->fetch();
        return $row ?: null;
    }

    public static function usernameExists(string $username, ?int $exceptId = null): bool
    {
        $sql = 'SELECT 1 FROM users WHERE username = ?';
        $params = [$username];
        if ($exceptId !== null) {
            $sql .= ' AND id <> ?';
            $params[] = $exceptId;
        }
        return (bool) Database::run($sql, $params)->fetchColumn();
    }

    public static function create(string $username, string $password, string $fullName, string $role): int
    {
        Database::run(
            'INSERT INTO users (username, password_hash, full_name, role) VALUES (?, ?, ?, ?)',
            [$username, password_hash($password, PASSWORD_DEFAULT), $fullName, $role]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, string $username, string $fullName, string $role): void
    {
        Database::run(
            'UPDATE users SET username = ?, full_name = ?, role = ? WHERE id = ?',
            [$username, $fullName, $role, $id]
        );
    }

    public static function updatePassword(int $id, string $password): void
    {
        Database::run(
            'UPDATE users SET password_hash = ? WHERE id = ?',
            [password_hash($password, PASSWORD_DEFAULT), $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM users WHERE id = ?', [$id]);
    }

    public static function countAdmins(): int
    {
        return (int) Database::run("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
    }
}
