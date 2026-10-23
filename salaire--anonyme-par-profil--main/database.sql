CREATE DATABASE IF NOT EXISTS salaire_anonyme
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE salaire_anonyme;

-- Attention : cette ligne supprime l'ancienne table (et ses données) avant de la recréer.
DROP TABLE IF EXISTS salary_submissions;

CREATE TABLE salary_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    job_title VARCHAR(100) NOT NULL,
    experience_level ENUM('0-1 an', '2-4 ans', '5-9 ans', '10 ans ou plus') NOT NULL,
    sector ENUM('Public', 'Privé', 'Association/ONG', 'Indépendant', 'Autre') NOT NULL,
    -- Salaire mensuel NET en FCFA (XOF)
    salary_amount DECIMAL(12,0) NOT NULL,
    -- Date seule (sans l'heure) pour limiter les risques de recoupement
    created_at DATE NOT NULL,
    -- Empreinte SHA-256 du jeton secret (le jeton lui-même n'est jamais stocké)
    token_hash CHAR(64) NULL UNIQUE,
    INDEX idx_profile (job_title, experience_level, sector)
);
