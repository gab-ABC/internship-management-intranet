-- =====================================================================
--  Migration : table `tuteurs` liee aux entreprises + `convention_signee`.
--
--  Transforme une base a l'ANCIEN schema (tuteur stocke directement dans
--  la table stages) vers le NOUVEAU schema (table tuteurs separee).
--
--  >>> RECOMMANDATION : si la base ne contient pas encore de saisies
--  manuelles importantes, il est PLUS SIMPLE et plus fiable de re-importer
--  schema.sql puis seed.sql (qui contient deja toutes les donnees). <<<
--
--  A executer une seule fois, via phpMyAdmin. Faites une sauvegarde avant.
-- =====================================================================

-- 1) Nouvel attribut convention_signee sur les stages (defaut : non signee).
ALTER TABLE stages
    ADD COLUMN convention_signee TINYINT(1) NOT NULL DEFAULT 0;

-- 2) Table des tuteurs (rattaches a une entreprise).
CREATE TABLE IF NOT EXISTS tuteurs (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    entreprise_id INT UNSIGNED NOT NULL,
    nom           VARCHAR(150) NOT NULL,
    nom_normalise VARCHAR(150) NOT NULL,
    telephone     VARCHAR(20)  DEFAULT NULL,
    email         VARCHAR(180) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_tuteur (entreprise_id, nom_normalise),
    KEY idx_entreprise (entreprise_id),
    CONSTRAINT fk_tuteur_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3) Remplit la table tuteurs a partir des tuteurs deja saisis dans stages.
--    Normalisation simple (minuscule, sans espaces ni ponctuation courante).
INSERT IGNORE INTO tuteurs (entreprise_id, nom, nom_normalise, telephone, email)
SELECT s.entreprise_id,
       MIN(s.tuteur_nom),
       LOWER(REPLACE(REPLACE(REPLACE(REPLACE(s.tuteur_nom,' ',''),'.',''),'-',''),'''','')),
       MIN(NULLIF(s.tuteur_tel, '')),
       MIN(NULLIF(s.tuteur_email, ''))
FROM stages s
WHERE s.entreprise_id IS NOT NULL AND s.tuteur_nom IS NOT NULL AND s.tuteur_nom <> ''
GROUP BY s.entreprise_id,
         LOWER(REPLACE(REPLACE(REPLACE(REPLACE(s.tuteur_nom,' ',''),'.',''),'-',''),'''',''));

-- 4) Colonne de liaison stages -> tuteurs.
ALTER TABLE stages
    ADD COLUMN tuteur_id INT UNSIGNED DEFAULT NULL AFTER entreprise_id,
    ADD KEY idx_tuteur (tuteur_id),
    ADD CONSTRAINT fk_stage_tuteur FOREIGN KEY (tuteur_id) REFERENCES tuteurs(id) ON DELETE SET NULL;

-- 5) Rattache chaque stage a son tuteur.
UPDATE stages s
JOIN tuteurs t
  ON t.entreprise_id = s.entreprise_id
 AND t.nom_normalise = LOWER(REPLACE(REPLACE(REPLACE(REPLACE(s.tuteur_nom,' ',''),'.',''),'-',''),'''',''))
SET s.tuteur_id = t.id;

-- 6) Suppression des anciennes colonnes tuteur de la table stages.
ALTER TABLE stages
    DROP COLUMN tuteur_nom,
    DROP COLUMN tuteur_tel,
    DROP COLUMN tuteur_email;
