-- Rulează în baza de date smartdb dacă tabelul contact_requests nu există.
-- CREATE DATABASE IF NOT EXISTS smartdb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE smartdb;

CREATE TABLE IF NOT EXISTS contact_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
