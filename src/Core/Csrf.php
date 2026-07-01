<?php
namespace App\Core;

defined('APP_RUNNING') or exit('Acces direct interdit.');

/**
 * Protection CSRF (Cross-Site Request Forgery).
 *
 * Chaque formulaire inclut un champ cache contenant un jeton secret stocke
 * en session. A la reception du formulaire, on verifie que le jeton
 * correspond : cela empeche un site tiers de soumettre des actions a la
 * place de l'utilisateur.
 */
class Csrf
{
    /** Retourne le jeton courant (en le creant si besoin). */
    public static function token(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /** Champ <input> cache a inserer dans les formulaires. */
    public static function field(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::token() . '">';
    }

    /** Verifie le jeton recu (comparaison resistante au timing). */
    public static function check(?string $token): bool
    {
        return is_string($token)
            && !empty($_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $token);
    }

    /** Stoppe l'execution si le jeton d'un POST est invalide. */
    public static function verifyPost(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && !self::check($_POST['csrf_token'] ?? null)) {
            http_response_code(419);
            exit('Jeton de securite invalide. Rechargez la page et reessayez.');
        }
    }
}
