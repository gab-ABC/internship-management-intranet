<?php
namespace App\Models;

defined('APP_RUNNING') or exit('Acces direct interdit.');

use App\Core\Database;

/**
 * Modele Stage : acces a la table `stages` et requetes d'agregation.
 */
class Stage
{
    /** Partie SELECT/JOIN commune (etudiant + entreprise + tuteur). */
    private const SELECT = '
        SELECT s.*,
               e.nom   AS etudiant_nom, e.prenom AS etudiant_prenom,
               e.email AS etudiant_email, e.telephone AS etudiant_telephone,
               en.id   AS ent_id, en.nom AS entreprise_nom, en.adresse AS entreprise_adresse, en.ville AS entreprise_ville,
               t.nom   AS tuteur_nom, t.telephone AS tuteur_tel, t.email AS tuteur_email,
               p.nom   AS prof_nom
        FROM stages s
        JOIN etudiants e         ON e.id  = s.etudiant_id
        LEFT JOIN entreprises en ON en.id = s.entreprise_id
        LEFT JOIN tuteurs t      ON t.id  = s.tuteur_id
        LEFT JOIN professeurs p  ON p.id  = s.prof_referent_id';

    /**
     * Recherche / listing des stages avec filtres optionnels.
     *
     * @param array $filters q, annee, formation, ville, telephone, email, prof, convention
     */
    public static function search(array $filters = []): array
    {
        $sql = self::SELECT . ' WHERE 1 = 1';
        $params = [];

        if (!empty($filters['q'])) {
            $sql .= ' AND (en.nom LIKE ? OR e.nom LIKE ? OR e.prenom LIKE ?)';
            $like = '%' . $filters['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($filters['annee'])) {
            $annees = array_map('intval', explode(',', $filters['annee']));
            $placeholders = implode(',', array_fill(0, count($annees), '?'));
            $sql .= " AND s.annee IN ($placeholders)";
            foreach ($annees as $a) {
                $params[] = $a;
            }
        }
        if (!empty($filters['formation'])) {
            $sql .= ' AND s.formation = ?';
            $params[] = $filters['formation'];
        }
        if (!empty($filters['ville'])) {
            $sql .= ' AND en.ville LIKE ?';
            $params[] = '%' . $filters['ville'] . '%';
        }
        if (!empty($filters['telephone'])) {
            $sql .= ' AND (e.telephone LIKE ? OR t.telephone LIKE ?)';
            array_push($params, '%' . $filters['telephone'] . '%', '%' . $filters['telephone'] . '%');
        }
        if (!empty($filters['email'])) {
            $sql .= ' AND (e.email LIKE ? OR t.email LIKE ? OR s.responsable_email LIKE ?)';
            array_push($params, '%' . $filters['email'] . '%', '%' . $filters['email'] . '%', '%' . $filters['email'] . '%');
        }
        if (!empty($filters['prof'])) {
            $sql .= ' AND p.nom LIKE ?';
            $params[] = '%' . $filters['prof'] . '%';
        }

        $sql .= ' ORDER BY s.annee DESC, e.nom ASC';

        return Database::run($sql, $params)->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $row = Database::run(self::SELECT . ' WHERE s.id = ?', [$id])->fetch();
        return $row ?: null;
    }

    public static function create(array $d): int
    {
        Database::run(
            'INSERT INTO stages
                (
                    etudiant_id, entreprise_id, 
                    tuteur_id, 
                    annee, 
                    formation, 
                    date_debut, date_fin,
                    prof_referent_id, 
                    responsable_nom, responsable_email
                )
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $d['etudiant_id'], $d['entreprise_id'] ?: null, $d['tuteur_id'] ?: null, $d['annee'] ?: null,
                $d['formation'] ?: null, $d['date_debut'] ?: null, $d['date_fin'] ?: null, $d['prof_referent_id'] ?: null,
                $d['responsable_nom'] ?: null, $d['responsable_email'] ?: null,
            ]
        );
        return (int) Database::pdo()->lastInsertId();
    }

    public static function update(int $id, array $d): void
    {
        Database::run(
            'UPDATE stages SET
                etudiant_id = ?, entreprise_id = ?, tuteur_id = ?, annee = ?, formation = ?,
                date_debut = ?, date_fin = ?, prof_referent_id = ?, responsable_nom = ?, responsable_email = ?
             WHERE id = ?',
            [
                $d['etudiant_id'], $d['entreprise_id'] ?: null, $d['tuteur_id'] ?: null, $d['annee'] ?: null,
                $d['formation'] ?: null, $d['date_debut'] ?: null, $d['date_fin'] ?: null, $d['prof_referent_id'] ?: null,
                $d['responsable_nom'] ?: null, $d['responsable_email'] ?: null, $id,
            ]
        );
    }

    public static function delete(int $id): void
    {
        Database::run('DELETE FROM stages WHERE id = ?', [$id]);
    }

    /** Indique si un stage identique existe deja (meme etudiant/annee/entreprise). */
    public static function exists(int $etudiantId, ?int $annee, ?int $entrepriseId): bool
    {
        return (bool) Database::run(
            'SELECT 1 FROM stages WHERE etudiant_id = ?
                AND annee <=> ? AND entreprise_id <=> ? LIMIT 1',
            [$etudiantId, $annee, $entrepriseId]
        )->fetchColumn();
    }

    public static function distinctYears(): array
    {
        return Database::run(
            'SELECT DISTINCT annee FROM stages WHERE annee IS NOT NULL ORDER BY annee DESC'
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    public static function distinctFormations(): array
    {
        return Database::run(
            'SELECT DISTINCT formation FROM stages WHERE formation IS NOT NULL ORDER BY formation'
        )->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Pour chaque entreprise : nombre d'etudiants distincts y ayant fait un
     * stage, et la liste des annees concernees.
     */
    public static function companyAggregates(): array
    {
        $rows = Database::run(
            'SELECT entreprise_id,
                    COUNT(DISTINCT etudiant_id) AS nb_etudiants,
                    GROUP_CONCAT(DISTINCT annee ORDER BY annee SEPARATOR ", ") AS annees
             FROM stages WHERE entreprise_id IS NOT NULL GROUP BY entreprise_id'
        )->fetchAll();

        $map = [];
        foreach ($rows as $r) {
            $map[(int) $r['entreprise_id']] = [
                'nb_etudiants' => (int) $r['nb_etudiants'],
                'annees'       => $r['annees'] ?? '',
            ];
        }
        return $map;
    }

    /** Statistiques globales pour la page d'accueil. */
    public static function stats(): array
    {
        $pdo = Database::pdo();
        return [
            'total_stages'      => (int) $pdo->query('SELECT COUNT(*) FROM stages')->fetchColumn(),
            'total_entreprises' => (int) $pdo->query('SELECT COUNT(*) FROM entreprises')->fetchColumn(),
            'total_etudiants'   => (int) $pdo->query('SELECT COUNT(*) FROM etudiants')->fetchColumn(),
            'par_annee'         => $pdo->query(
                'SELECT annee, COUNT(*) AS nb FROM stages
                 WHERE annee IS NOT NULL GROUP BY annee ORDER BY annee'
            )->fetchAll(),
            'top_entreprises'   => $pdo->query(
                'SELECT en.nom, COUNT(*) AS nb
                 FROM stages s JOIN entreprises en ON en.id = s.entreprise_id
                 GROUP BY en.id ORDER BY nb DESC, en.nom LIMIT 8'
            )->fetchAll(),
        ];
    }
}
