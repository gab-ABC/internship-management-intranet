<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

/**
 * Modele Entreprise : acces a la table `entreprises`.
 *
 * La colonne `nom_normalise` (unique) garantit qu'une meme entreprise n'est
 * jamais enregistree deux fois, quelles que soient les variations de casse,
 * d'accents ou d'espaces.
 */
class Entreprise
{
    /**
     * Noms d'entreprises fusionnes : nom normalise -> nom canonique.
     * (WhiteDev a ete renomme WeSyn, sous plusieurs orthographes.)
     */
    private const ALIASES = [
        'whitedev'      => 'WeSyn',
        'wesyn'         => 'WeSyn',
        'whitedevwesyn' => 'WeSyn',
    ];

    /** Normalise un nom d'entreprise pour la deduplication. */
    public static function normalize(string $nom): string
    {
        $s = mb_strtolower(trim($nom), 'UTF-8');
        // Supprime les accents (é -> e, ç -> c, ...).
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        // Ne garde que les lettres et chiffres.
        return preg_replace('/[^a-z0-9]/', '', $s) ?? '';
    }

    /** Applique l'eventuelle fusion d'alias (WhiteDev/WeSyn -> WeSyn). */
    public static function canonicalName(string $nom): string
    {
        $key = self::normalize($nom);
        return self::ALIASES[$key] ?? trim($nom);
    }

    public static function all(): array
    {
        return Database::run('SELECT * FROM entreprises ORDER BY nom')->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::run('SELECT * FROM entreprises WHERE id = ?', [$id])->fetch();
        return $row ?: null;
    }

    /**
     * Retourne l'id d'une entreprise existante (par nom normalise) ou la cree.
     * C'est cette methode qui evite les doublons de societe.
     */
    public static function findOrCreate(string $nom, ?string $adresse, ?string $cp, ?string $ville): int
    {
        $nom = self::canonicalName($nom);   // fusionne les noms equivalents
        $normalise = self::normalize($nom);

        $existing = Database::run(
            'SELECT id FROM entreprises WHERE nom_normalise = ?',
            [$normalise]
        )->fetchColumn();

        if ($existing) {
            return (int) $existing;
        }

        Database::run(
            'INSERT INTO entreprises (nom, nom_normalise, adresse, code_postal, ville) VALUES (?, ?, ?, ?, ?)',
            [trim($nom), $normalise, $adresse ?: null, $cp ?: null, $ville ?: null]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    /** Indique si un autre enregistrement utilise deja ce nom normalise. */
    public static function nameExists(string $nom, int $exceptId): bool
    {
        return (bool) Database::run(
            'SELECT 1 FROM entreprises WHERE nom_normalise = ? AND id <> ?',
            [self::normalize($nom), $exceptId]
        )->fetchColumn();
    }

    public static function update(int $id, string $nom, ?string $adresse, ?string $cp, ?string $ville): void
    {
        Database::run(
            'UPDATE entreprises SET nom = ?, nom_normalise = ?, adresse = ?, code_postal = ?, ville = ? WHERE id = ?',
            [trim($nom), self::normalize($nom), $adresse ?: null, $cp ?: null, $ville ?: null, $id]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM entreprises WHERE id = ?', [$id]);
    }

    /** Nombre de stages rattaches a une entreprise. */
    public static function stageCount(int $id): int
    {
        return (int) Database::run(
            'SELECT COUNT(*) FROM stages WHERE entreprise_id = ?',
            [$id]
        )->fetchColumn();
    }

    /**
     * Liste des entreprises avec le nombre de stages associes.
     *
     * @param string $sort 'stages_asc' ou 'stages_desc' pour trier par nombre
     *                      de stages ; sinon tri alphabetique par nom.
     */
    public static function allWithStageCount(string $q = '', string $sort = ''): array
    {
        $sql = 'SELECT en.*, COUNT(s.id) AS nb_stages
                FROM entreprises en
                LEFT JOIN stages s ON s.entreprise_id = en.id';
        $params = [];
        if ($q !== '') {
            $sql .= ' WHERE en.nom LIKE ? OR en.ville LIKE ?';
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $order = match ($sort) {
            'stages_asc'  => 'nb_stages ASC, en.nom ASC',
            'stages_desc' => 'nb_stages DESC, en.nom ASC',
            default       => 'en.nom ASC',
        };
        $sql .= ' GROUP BY en.id ORDER BY ' . $order;
        return Database::run($sql, $params)->fetchAll();
    }
}
