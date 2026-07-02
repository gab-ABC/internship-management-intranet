<?php
namespace App\Controllers;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Controller;
use App\Core\Auth;
use App\Models\User;
use App\Models\Etablissement;

/**
 * Connexion, deconnexion et changement de mot de passe personnel.
 */
class AuthController extends Controller
{
    /**
     * Page de connexion (page de demarrage).
     * Affiche le formulaire (GET) ou traite la soumission (POST).
     */
    public function login(): void
    {
        if (Auth::check()) {
            redirect('home');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            $this->renderLogin();
            return;
        }

        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $this->renderLogin('Veuillez renseigner vos identifiants.');
            return;
        }

        if (Auth::attempt($username, $password)) {
            redirect('home');
        }

        // Message volontairement generique (ne revele pas si le compte existe).
        $this->renderLogin('Identifiant ou mot de passe incorrect.');
    }

    public function logout(): void
    {
        Auth::logout();
        redirect('login');
    }

    /** Page permettant a l'utilisateur connecte de changer son mot de passe. */
    public function account(): void
    {
        $this->render('auth/account', [], 'Mon compte');
    }

    public function updatePassword(): void
    {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $user = User::find(Auth::id());

        if (!$user || !password_verify($current, $user['password_hash'])) {
            flash('Mot de passe actuel incorrect.', 'danger');
        } else if ($current === $new) {
            flash('Le nouveau mot de passe ne peut pas être votre ancien mot de passe.', 'danger');
        } elseif (strlen($new) < 8) {
            flash('Le nouveau mot de passe doit contenir au moins 8 caractères.', 'danger');
        } elseif ($new !== $confirm) {
            flash('La confirmation ne correspond pas.', 'danger');
        } else {
            User::updatePassword($user['id'], $new);
            flash('Mot de passe mis à jour.');
        }
        redirect('account');
    }

    /** Rend la page de connexion (sans le gabarit principal). */
    private function renderLogin(string $error = ''): void
    {
        $viewFile = SRC_PATH . '/Views/auth/login.php';
        $nom_etablissement = Etablissement::getEtablissementName();
        require $viewFile;
    }
}
