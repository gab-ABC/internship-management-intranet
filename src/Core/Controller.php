<?php
namespace App\Core;

defined('APP_RUNNING') or exit('Acces direct interdit.');

/**
 * Controleur de base : fournit le rendu des vues avec le gabarit commun.
 */
abstract class Controller
{
    /**
     * Affiche une vue dans le gabarit (layout) principal.
     *
     * @param string $view  Chemin de la vue, ex: 'stages/index'
     * @param array  $data  Variables mises a disposition de la vue
     */
    protected function render(string $view, array $data = [], string $title = 'Stages'): void
    {
        extract($data, EXTR_SKIP);
        $viewFile = SRC_PATH . '/Views/' . $view . '.php';

        if (!is_file($viewFile)) {
            http_response_code(500);
            exit('Vue introuvable : ' . e($view));
        }

        // Le contenu de la vue est capture puis injecte dans le gabarit.
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        require SRC_PATH . '/Views/layout/main.php';
    }

    /** Verifie qu'un role est autorise, sinon renvoie une erreur 403. */
    protected function authorize(string ...$roles): void
    {
        if (!Auth::hasRole(...$roles)) {
            http_response_code(403);
            exit('Acces refuse : vous n\'avez pas les droits necessaires.');
        }
    }
}
