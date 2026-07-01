<?php
namespace App\Core;

defined('APP_RUNNING') or exit('Acces direct interdit.');

/**
 * Routeur minimal.
 *
 * Les routes sont identifiees par le parametre d'URL "p" (ex: index.php?p=stages).
 * Chaque route precise le controleur, la methode et les roles autorises.
 */
class Router
{
    /** @var array<string, array{0:string,1:string,2:array<string>}> */
    private array $routes = [];

    /**
     * Enregistre une route.
     *
     * @param string        $key        Cle de route (valeur du parametre "p")
     * @param string        $controller Nom court du controleur (ex: 'StageController')
     * @param string        $method     Methode a appeler
     * @param array<string> $roles      Roles autorises ; [] = route publique
     */
    public function add(string $key, string $controller, string $method, array $roles = []): void
    {
        $this->routes[$key] = [$controller, $method, $roles];
    }

    public function dispatch(): void
    {
        $key = $_GET['p'] ?? 'home';

        if (!isset($this->routes[$key])) {
            http_response_code(404);
            exit('Page introuvable.');
        }

        [$controller, $method, $roles] = $this->routes[$key];
        $isPublic = ($roles === []);

        // Authentification requise pour toutes les routes non publiques.
        if (!$isPublic && !Auth::check()) {
            redirect('login');
        }

        // Verification du role.
        if (!$isPublic && !Auth::hasRole(...$roles)) {
            http_response_code(403);
            exit('Acces refuse.');
        }

        // Protection CSRF systematique sur les requetes POST.
        Csrf::verifyPost();

        $class = 'App\\Controllers\\' . $controller;
        $instance = new $class();
        $instance->$method();
    }
}
