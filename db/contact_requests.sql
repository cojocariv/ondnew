-- PostgreSQL: rulează în baza ta dacă tabelul contact_requests nu există.
-- Exemplu creare bază (în psql sau alt client): CREATE DATABASE smartdb ENCODING 'UTF8';

CREATE TABLE IF NOT EXISTS contact_requests (
    id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NOT NULL,
    email      VARCHAR(150) NOT NULL,
    message    TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
