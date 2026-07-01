-- =====================================================================
--  Migration : ajout du telephone de l'etudiant (stagiaire).
--
--  A executer UNE SEULE FOIS sur une base deja installee
--  (via phpMyAdmin sur alwaysdata). Inutile pour une nouvelle
--  installation : la colonne est deja presente dans schema.sql.
-- =====================================================================

ALTER TABLE etudiants
    ADD COLUMN telephone VARCHAR(20) DEFAULT NULL AFTER email;
