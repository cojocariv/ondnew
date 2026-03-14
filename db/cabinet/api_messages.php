<?php
/**
 * Returnează mesajele unei conversații ca JSON (doar pentru admin autentificat).
 * Folosit pentru actualizare în timp real în conversation.php.
 */
session_start();
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

if (empty($_SESSION['db_admin_logged_in'])) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Neautorizat'], JSON_UNESCAPED_UNICODE);
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    echo json_encode(['ok' => false, 'error' => 'ID invalid'], JSON_UNESCAPED_UNICODE);
    exit;
}

define('DB_CONFIG_SILENT', true);
require __DIR__ . '/../config.php';
if (isset($db_error) && $db_error !== null) {
    http_response_code(503);
    echo json_encode(['ok' => false, 'error' => 'Baza de date indisponibilă'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, sender_type, body, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC');
    $stmt->execute([$id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($messages as &$m) {
        $m['created_at_formatted'] = date('d.m.Y H:i', strtotime($m['created_at']));
    }
    echo json_encode(['ok' => true, 'messages' => $messages], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Eroare'], JSON_UNESCAPED_UNICODE);
}
