<?php
namespace App\Core;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Models\User;

/**
 * Gestion de l'authentification et des roles.
 *
 * Roles disponibles :
 *   - admin : acces complet (dont la gestion des utilisateurs)
 *   - prof  : tout sauf la gestion des utilisateurs
 *   - eleve : consultation de l'historique uniquement
 */
class Auth
{
    /** Tente de connecter un utilisateur. Retourne true si succes. */
    public static function attempt(string $username, string $password): bool
    {
        $user = User::findByUsername($username);
        if ($user && password_verify($password, $user['password_hash'])) {
            // Regenere l'identifiant de session pour eviter la fixation de session.
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'        => (int) $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'role'      => $user['role'],
            ];
            return true;
        }
        return false;
    }

    /** Deconnecte l'utilisateur courant. */
    public static function logout(): void
    {
        $_SESSION = [];
        session_destroy();
    }

    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }

    /** Retourne l'utilisateur connecte (ou null). */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    public static function id(): ?int
    {
        return $_SESSION['user']['id'] ?? null;
    }

    public static function role(): ?string
    {
        return $_SESSION['user']['role'] ?? null;
    }

    public static function hasRole(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    /** Raccourcis de lisibilite. */
    public static function isAdmin(): bool { return self::role() === 'admin'; }
    public static function isProf(): bool  { return self::role() === 'prof'; }
    public static function isEleve(): bool { return self::role() === 'eleve'; }

    /** Les admins et profs peuvent modifier les donnees (ajouter un stage...). */
    public static function canEdit(): bool
    {
        return self::hasRole('admin', 'prof');
    }
}
