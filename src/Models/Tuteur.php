<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

/**
 * Modele Tuteur : acces a la table `tuteurs`.
 *
 * Un tuteur est rattache a une entreprise. Une meme entreprise peut donc
 * avoir plusieurs tuteurs, sans dupliquer la fiche entreprise.
 */
class Tuteur
{
    /** Normalise un nom de tuteur (pour la deduplication). */
    public static function normalize(string $nom): string
    {
        $s = mb_strtolower(trim($nom), 'UTF-8');
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        return preg_replace('/[^a-z0-9]/', '', $s) ?? '';
    }

    /**
     * Retourne l'id d'un tuteur existant pour cette entreprise (meme nom),
     * ou le cree. Renvoie null si le nom est vide.
     */
    public static function findOrCreate(int $entrepriseId, string $nom, ?string $tel, ?string $email): ?int
    {
        $nom = trim($nom);
        if ($nom === '') {
            return null;
        }
        $normalise = self::normalize($nom);

        $existing = Database::run(
            'SELECT id FROM tuteurs WHERE entreprise_id = ? AND nom_normalise = ?',
            [$entrepriseId, $normalise]
        )->fetchColumn();

        if ($existing) {
            // Complete les coordonnees si elles manquaient.
            Database::run(
                'UPDATE tuteurs SET telephone = COALESCE(NULLIF(telephone, ""), ?),
                                    email = COALESCE(NULLIF(email, ""), ?)
                 WHERE id = ?',
                [$tel ?: null, $email ?: null, (int) $existing]
            );
            return (int) $existing;
        }

        Database::run(
            'INSERT INTO tuteurs (entreprise_id, nom, nom_normalise, telephone, email) VALUES (?, ?, ?, ?, ?)',
            [$entrepriseId, $nom, $normalise, $tel ?: null, $email ?: null]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    /** Tuteurs d'une entreprise (pour proposer un choix dans le formulaire). */
    public static function forEntreprise(int $entrepriseId): array
    {
        return Database::run(
            'SELECT * FROM tuteurs WHERE entreprise_id = ? ORDER BY nom',
            [$entrepriseId]
        )->fetchAll();
    }
}
