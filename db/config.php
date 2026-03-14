<?php
/**
 * Conexiune MySQL – baza smartdb
 * Completează mai jos cu user-ul și parola tale.
 */
$DB_HOST = 'localhost';
$DB_NAME = 'smartdb';
$DB_USER = 'SETEAZĂ_USER';   // ex: root sau user-ul tău MySQL
$DB_PASS = 'SETEAZĂ_PAROLA'; // parola pentru $DB_USER

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Eroare conectare bază de date.');
}
