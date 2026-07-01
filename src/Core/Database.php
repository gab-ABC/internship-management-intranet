<?php
namespace App\Core;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use PDO;
use PDOException;

/**
 * Connexion a la base de donnees MySQL via PDO.
 *
 * On utilise un point d'acces unique (methode statique pdo()) afin de
 * n'ouvrir qu'une seule connexion par requete HTTP.
 *
 * Toutes les requetes passent par des requetes preparees (parametres lies),
 * ce qui protege contre les injections SQL.
 */
class Database
{
    private static array $config = [];
    private static ?PDO $pdo = null;

    /** Memorise les parametres de connexion (appele depuis bootstrap.php). */
    public static function setConfig(array $config): void
    {
        self::$config = $config;
    }

    /** Retourne l'objet PDO, en l'ouvrant a la premiere utilisation. */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            $c = self::$config;
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=%s',
                $c['host'], $c['name'], $c['charset'] ?? 'utf8mb4'
            );
            try {
                self::$pdo = new PDO($dsn, $c['user'], $c['pass'], [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } catch (PDOException $e) {
                http_response_code(500);
                exit('Connexion a la base de donnees impossible.');
            }
        }
        return self::$pdo;
    }

    /** Prepare et execute une requete, puis retourne le statement. */
    public static function run(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
