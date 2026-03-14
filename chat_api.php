<?php
/**
 * API pentru mini-chat (vizitatori): start conversație, listare mesaje, trimitere mesaj.
 * Răspunsuri JSON. Cabinet-ul admin folosește db/cabinet/.
 */
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function json_response($data) {
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function json_error($message) {
    json_response(['ok' => false, 'error' => $message]);
}

$action = isset($_GET['action']) ? trim($_GET['action']) : (isset($_POST['action']) ? trim($_POST['action']) : '');

if (!in_array($action, ['start', 'messages', 'send'], true)) {
    json_error('Acțiune invalidă.');
}

$config_path = __DIR__ . '/db/config.php';
if (!is_file($config_path)) {
    json_error('Chat indisponibil.');
}
ob_start();
try {
    require $config_path;
} catch (Throwable $e) {
    ob_end_clean();
    json_error('Serviciu temporar indisponibil.');
}
if (isset($db_error) && $db_error !== null) {
    ob_end_clean();
    json_error('Chat indisponibil.');
}
ob_end_clean();

// Verifică dacă tabelele chat există
try {
    $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'chat_conversations'");
    if (!$check || !$check->fetch()) {
        json_error('Chat nu este configurat. Creează tabelele din db/chat_schema.sql.');
    }
} catch (Throwable $e) {
    json_error('Chat indisponibil.');
}

if ($action === 'start') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Metodă invalidă.');
    }
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    if ($name === '' || $email === '') {
        json_error('Introdu numele și emailul.');
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_error('Email invalid.');
    }
    $name = mb_substr($name, 0, 150);
    $email = mb_substr($email, 0, 150);
    try {
        $stmt = $pdo->prepare('INSERT INTO chat_conversations (visitor_name, visitor_email) VALUES (:n, :e) RETURNING id');
        $stmt->execute([':n' => $name, ':e' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = (int) ($row['id'] ?? 0);
        if ($id < 1) json_error('Eroare la creare conversație.');
        json_response(['ok' => true, 'conversation_id' => $id]);
    } catch (Throwable $e) {
        json_error('Nu s-a putut crea conversația.');
    }
}

if ($action === 'messages') {
    $cid = isset($_GET['conversation_id']) ? (int) $_GET['conversation_id'] : 0;
    if ($cid < 1) {
        json_error('Conversație invalidă.');
    }
    try {
        $stmt = $pdo->prepare('SELECT id, sender_type, body, created_at FROM chat_messages WHERE conversation_id = :cid ORDER BY created_at ASC');
        $stmt->execute([':cid' => $cid]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$r) {
            $r['created_at'] = date('Y-m-d H:i', strtotime($r['created_at']));
        }
        json_response(['ok' => true, 'messages' => $rows]);
    } catch (Throwable $e) {
        json_error('Nu s-au putut încărca mesajele.');
    }
}

if ($action === 'send') {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_error('Metodă invalidă.');
    }
    $cid = isset($_POST['conversation_id']) ? (int) $_POST['conversation_id'] : 0;
    $body = isset($_POST['body']) ? trim($_POST['body']) : '';
    if ($cid < 1) {
        json_error('Conversație invalidă.');
    }
    if ($body === '') {
        json_error('Mesajul nu poate fi gol.');
    }
    $body = mb_substr($body, 0, 4000);
    try {
        $stmt = $pdo->prepare('INSERT INTO chat_messages (conversation_id, sender_type, body) VALUES (:cid, \'visitor\', :body)');
        $stmt->execute([':cid' => $cid, ':body' => $body]);
        $pdo->prepare('UPDATE chat_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$cid]);
        json_response(['ok' => true]);
    } catch (Throwable $e) {
        json_error('Nu s-a putut trimite mesajul.');
    }
}
