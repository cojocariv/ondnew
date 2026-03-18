<?php
session_start();

function client_require_db() {
    define('DB_CONFIG_SILENT', true);
    require __DIR__ . '/../db/config.php';
    if (isset($db_error) && $db_error !== null) {
        http_response_code(503);
        exit('Baza de date indisponibilă.');
    }
    return $pdo;
}

function client_is_logged_in() {
    return !empty($_SESSION['client_user_id']);
}

function client_redirect($path) {
    header('Location: ' . $path);
    exit;
}

function client_h($s) {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function client_current_url_base() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return $scheme . '://' . $host;
}

function client_csrf_token() {
    if (empty($_SESSION['client_csrf'])) {
        $_SESSION['client_csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['client_csrf'];
}

function client_check_csrf($token) {
    return !empty($_SESSION['client_csrf']) && is_string($token) && hash_equals($_SESSION['client_csrf'], $token);
}

