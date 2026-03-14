<?php
/**
 * Conexiune MySQL – panoul /db
 *
 * Setează aici datele de la baza de date (ex. din Plesk: Databases → utilizator + parolă).
 * Numele bazei este cel în care ai creat tabelul contact_requests (vezi db/contact_requests.sql).
 */
$DB_HOST = 'localhost';
$DB_NAME = 'smartdb';           // numele bazei tale MySQL
$DB_USER = 'SETEAZĂ_USER';      // utilizatorul MySQL (ex. din Plesk)
$DB_PASS = 'SETEAZĂ_PAROLA';    // parola utilizatorului MySQL

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
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html lang="ro"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Eroare conexiune</title></head><body style="font-family:system-ui;max-width:520px;margin:2rem auto;padding:1.5rem;background:#fef2f2;border:1px solid #fecaca;border-radius:12px;color:#991b1b;">';
    echo '<p style="font-weight:600;margin:0 0 0.5rem;">Eroare conectare bază de date.</p>';
    echo '<p style="margin:0;font-size:0.9rem;">Verifică în <strong>db/config.php</strong>: host, nume bază (<code>' . htmlspecialchars($DB_NAME) . '</code>), utilizator și parolă MySQL. Asigură-te că baza există și că utilizatorul are drepturi pe ea.</p>';
    echo '</body></html>';
    exit;
}
