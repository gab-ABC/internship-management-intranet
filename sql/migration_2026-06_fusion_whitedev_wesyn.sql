-- =====================================================================
--  Migration : fusion des entreprises WhiteDev et WeSyn.
--
--  WhiteDev a ete renomme WeSyn : cette migration regroupe les deux
--  fiches en une seule (WeSyn) et reaffecte les stages concernes.
--
--  A executer UNE SEULE FOIS sur une base deja installee
--  (via phpMyAdmin sur alwaysdata). Inutile pour une nouvelle
--  installation : le seed.sql contient deja une fiche unique WeSyn.
--
--  Le script gere tous les cas (les deux fiches, ou une seule).
-- =====================================================================

SET @whitedev := (SELECT id FROM entreprises WHERE nom_normalise = 'whitedev' LIMIT 1);
SET @wesyn    := (SELECT id FROM entreprises WHERE nom_normalise = 'wesyn'    LIMIT 1);

-- Cas 1 : les deux fiches existent -> on bascule les stages de WhiteDev vers WeSyn.
UPDATE stages
   SET entreprise_id = @wesyn
 WHERE @whitedev IS NOT NULL AND @wesyn IS NOT NULL
   AND entreprise_id = @whitedev;

-- ... puis on supprime la fiche WhiteDev devenue inutile.
DELETE FROM entreprises
 WHERE @whitedev IS NOT NULL AND @wesyn IS NOT NULL
   AND id = @whitedev;

-- Cas 2 : seule WhiteDev existe (base sans l'historique 2024) -> on la renomme.
UPDATE entreprises
   SET nom = 'WeSyn', nom_normalise = 'wesyn'
 WHERE @whitedev IS NOT NULL AND @wesyn IS NULL
   AND id = @whitedev;

-- Dans tous les cas, on fixe le nom canonique.
UPDATE entreprises SET nom = 'WeSyn' WHERE nom_normalise = 'wesyn';
