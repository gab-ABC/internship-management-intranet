-- =====================================================================
--  Gestion des stages - BTS CIEL/SNIR - Lycee Jean Rostand (Roubaix)
--  Schema de la base de donnees MySQL.
--
--  A importer une seule fois (par exemple via phpMyAdmin sur alwaysdata) :
--    1) schema.sql   (cree les tables et le compte administrateur)
--    2) seed.sql     (importe l'historique des stages)
-- =====================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS stages;
DROP TABLE IF EXISTS tuteurs;
DROP TABLE IF EXISTS etudiants;
DROP TABLE IF EXISTS entreprises;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Utilisateurs de l'application (3 roles : admin, prof, eleve)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username      VARCHAR(50)  NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name     VARCHAR(120) NOT NULL,
    role          ENUM('admin','prof','eleve') NOT NULL DEFAULT 'eleve',
    created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Entreprises (pas de doublon grace a la colonne normalisee unique)
-- ---------------------------------------------------------------------
CREATE TABLE entreprises (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom           VARCHAR(180) NOT NULL,
    nom_normalise VARCHAR(180) NOT NULL,   -- nom sans accents/espaces/ponctuation
    adresse       VARCHAR(255) DEFAULT NULL,
    code_postal   VARCHAR(20)  DEFAULT NULL,
    ville         VARCHAR(120) DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_nom_normalise (nom_normalise)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Tuteurs : rattaches a une entreprise (une entreprise peut avoir
-- plusieurs tuteurs). Evite les doublons d'entreprise.
-- ---------------------------------------------------------------------
CREATE TABLE tuteurs (
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

-- ---------------------------------------------------------------------
-- Etudiants
-- ---------------------------------------------------------------------
CREATE TABLE etudiants (
    id        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nom       VARCHAR(120) NOT NULL,
    prenom    VARCHAR(120) NOT NULL,
    email     VARCHAR(180) DEFAULT NULL,
    telephone VARCHAR(20)  DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_nom (nom, prenom)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Stages : un etudiant dans une entreprise, une annee donnee.
-- Le tuteur est reference via la table tuteurs.
-- ---------------------------------------------------------------------
CREATE TABLE stages (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    etudiant_id       INT UNSIGNED NOT NULL,
    entreprise_id     INT UNSIGNED DEFAULT NULL,
    tuteur_id         INT UNSIGNED DEFAULT NULL,
    annee             SMALLINT DEFAULT NULL,            -- annee de debut (ex: 2025 pour 2025-2026)
    formation         VARCHAR(40)  DEFAULT NULL,        -- BTS IRIS / SNIR / CIEL
    periode           VARCHAR(120) DEFAULT NULL,
    prof_referent     VARCHAR(120) DEFAULT NULL,
    responsable_nom   VARCHAR(150) DEFAULT NULL,
    responsable_email VARCHAR(180) DEFAULT NULL,
    convention_signee TINYINT(1) NOT NULL DEFAULT 0,    -- 0 = non, 1 = oui
    created_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_annee (annee),
    KEY idx_entreprise (entreprise_id),
    KEY idx_etudiant (etudiant_id),
    KEY idx_tuteur (tuteur_id),
    CONSTRAINT fk_stage_etudiant   FOREIGN KEY (etudiant_id)   REFERENCES etudiants(id)   ON DELETE CASCADE,
    CONSTRAINT fk_stage_entreprise FOREIGN KEY (entreprise_id) REFERENCES entreprises(id) ON DELETE SET NULL,
    CONSTRAINT fk_stage_tuteur     FOREIGN KEY (tuteur_id)     REFERENCES tuteurs(id)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Compte administrateur par defaut.
-- Identifiant : admin   /   Mot de passe : Admin123!
-- IMPORTANT : changez ce mot de passe des la premiere connexion.
-- ---------------------------------------------------------------------
INSERT INTO users (username, password_hash, full_name, role) VALUES
('admin', '$2y$12$HmOmecODrPmpxOZkI.CWfez4hX5b4n4Vdzmex2QqQRyURjBOE3Bnm', 'Administrateur', 'admin');
