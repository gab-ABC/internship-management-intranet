<?php
defined('APP_RUNNING') or exit('Acces direct interdit.');

/**
 * Petites fonctions utilitaires globales.
 */

/** Echappe une chaine pour un affichage HTML sur (protection XSS). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Redirige vers une route interne puis stoppe le script. */
function redirect(string $route): void
{
    header('Location: ' . url($route));
    exit;
}

/** Construit une URL interne a partir d'une cle de route (ex: 'stages'). */
function url(string $route = 'home'): string
{
    return 'index.php?p=' . urlencode($route);
}

/** Stocke un message flash affiche une seule fois (apres redirection). */
function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'][] = ['message' => $message, 'type' => $type];
}

/** Recupere et vide les messages flash. */
function take_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

/* --- Raccourcis d'authentification pour les vues (lisibilite) --- */

function current_user(): ?array { return \App\Core\Auth::user(); }
function current_role(): ?string { return \App\Core\Auth::role(); }
function is_admin(): bool { return \App\Core\Auth::isAdmin(); }
function can_edit(): bool { return \App\Core\Auth::canEdit(); }

/** Marque l'attribut "selected" d'une option si la valeur correspond. */
function selected($a, $b): string { return (string) $a === (string) $b ? 'selected' : ''; }

/** Couleur Bootstrap associee a un role (admin=rouge, prof=bleu, eleve=gris). */
function role_color(?string $role): string
{
    $colors = ['admin' => 'danger', 'prof' => 'primary', 'eleve' => 'secondary'];
    return $colors[$role] ?? 'secondary';
}
