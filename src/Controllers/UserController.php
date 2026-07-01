<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;

/**
 * Administration des utilisateurs (reserve au role admin).
 */
class UserController extends Controller
{
    public function __construct()
    {
        // Double securite : meme si la route est mal configuree, on verifie ici.
        $this->authorize('admin');
    }

    public function index(): void
    {
        $this->render('users/index', ['users' => User::all()], 'Utilisateurs');
    }

    public function create(): void
    {
        $this->render('users/form', [
            'user'   => null,
            'old'    => [],
            'errors' => [],
        ], 'Nouvel utilisateur');
    }

    public function store(): void
    {
        $in = [
            'username'  => trim($_POST['username'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role'      => $_POST['role'] ?? 'eleve',
            'password'  => $_POST['password'] ?? '',
        ];
        $errors = $this->validate($in, null);

        if ($errors) {
            $this->render('users/form', ['user' => null, 'old' => $in, 'errors' => $errors], 'Nouvel utilisateur');
            return;
        }

        User::create($in['username'], $in['password'], $in['full_name'], $in['role']);
        flash('Utilisateur créé.');
        redirect('users');
    }

    public function edit(): void
    {
        $user = User::find((int) ($_GET['id'] ?? 0));
        if (!$user) {
            redirect('users');
        }
        $this->render('users/form', ['user' => $user, 'old' => [], 'errors' => []], 'Modifier l\'utilisateur');
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $user = User::find($id);
        if (!$user) {
            redirect('users');
        }

        $in = [
            'username'  => trim($_POST['username'] ?? ''),
            'full_name' => trim($_POST['full_name'] ?? ''),
            'role'      => $_POST['role'] ?? 'eleve',
            'password'  => $_POST['password'] ?? '',
        ];
        $errors = $this->validate($in, $id);

        // Empeche de retirer le dernier administrateur du systeme.
        if ($user['role'] === 'admin' && $in['role'] !== 'admin' && User::countAdmins() <= 1) {
            $errors['role'] = 'Impossible : il doit rester au moins un administrateur.';
        }

        if ($errors) {
            $this->render('users/form', ['user' => $user, 'old' => $in, 'errors' => $errors], 'Modifier l\'utilisateur');
            return;
        }

        User::update($id, $in['username'], $in['full_name'], $in['role']);
        if ($in['password'] !== '') {
            User::updatePassword($id, $in['password']);
        }
        flash('Utilisateur mis à jour.');
        redirect('users');
    }

    public function destroy(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $user = User::find($id);

        if (!$user) {
            redirect('users');
        }
        if ($id === Auth::id()) {
            flash('Vous ne pouvez pas supprimer votre propre compte.', 'danger');
            redirect('users');
        }
        if ($user['role'] === 'admin' && User::countAdmins() <= 1) {
            flash('Impossible de supprimer le dernier administrateur.', 'danger');
            redirect('users');
        }

        User::delete($id);
        flash('Utilisateur supprimé.');
        redirect('users');
    }

    /** Validation des champs utilisateur. */
    private function validate(array $in, ?int $id): array
    {
        $errors = [];

        if ($in['username'] === '' || !preg_match('/^[A-Za-z0-9._-]{3,50}$/', $in['username'])) {
            $errors['username'] = 'Identifiant invalide (3 à 50 caractères : lettres, chiffres, . _ -).';
        } elseif (User::usernameExists($in['username'], $id)) {
            $errors['username'] = 'Cet identifiant est déjà utilisé.';
        }
        if ($in['full_name'] === '') {
            $errors['full_name'] = 'Le nom complet est obligatoire.';
        }
        if (!in_array($in['role'], User::ROLES, true)) {
            $errors['role'] = 'Rôle invalide.';
        }
        // Mot de passe obligatoire a la creation ; optionnel en modification.
        if ($id === null && strlen($in['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        } elseif ($id !== null && $in['password'] !== '' && strlen($in['password']) < 8) {
            $errors['password'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        return $errors;
    }
}
