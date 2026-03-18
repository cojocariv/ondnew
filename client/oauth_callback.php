<?php
require __DIR__ . '/lib.php';
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Method not allowed');
}

if (empty($_GET['code']) || empty($_GET['state'])) {
    http_response_code(400);
    exit('Missing parameters');
}

if (empty($_SESSION['oauth_state']) || !hash_equals($_SESSION['oauth_state'], (string)$_GET['state'])) {
    http_response_code(400);
    exit('Invalid state');
}
unset($_SESSION['oauth_state']);

$code = (string)$_GET['code'];

// Exchange code for token
$tokenEndpoint = 'https://oauth2.googleapis.com/token';
$post = http_build_query([
    'code' => $code,
    'client_id' => GOOGLE_CLIENT_ID,
    'client_secret' => GOOGLE_CLIENT_SECRET,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'grant_type' => 'authorization_code',
]);

$ch = curl_init($tokenEndpoint);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $post,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_TIMEOUT => 15,
]);
$tokenResp = curl_exec($ch);
$httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($tokenResp === false || $httpCode < 200 || $httpCode >= 300) {
    http_response_code(500);
    exit('OAuth token exchange failed.');
}
$tokenData = json_decode($tokenResp, true);
$accessToken = $tokenData['access_token'] ?? null;
if (!$accessToken) {
    http_response_code(500);
    exit('Missing access token.');
}

// Fetch user info
$userinfoEndpoint = 'https://openidconnect.googleapis.com/v1/userinfo';
$ch = curl_init($userinfoEndpoint);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $accessToken],
    CURLOPT_TIMEOUT => 15,
]);
$userResp = curl_exec($ch);
$uCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
if ($userResp === false || $uCode < 200 || $uCode >= 300) {
    http_response_code(500);
    exit('Failed to fetch user info.');
}
$u = json_decode($userResp, true);

$sub = (string)($u['sub'] ?? '');
$email = (string)($u['email'] ?? '');
$name = (string)($u['name'] ?? '');
$picture = isset($u['picture']) ? (string)$u['picture'] : null;

if ($sub === '' || $email === '') {
    http_response_code(500);
    exit('Invalid user info.');
}

if (!empty(CLIENT_ALLOWED_EMAIL_DOMAINS)) {
    $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
    $allowed = array_map('strtolower', CLIENT_ALLOWED_EMAIL_DOMAINS);
    if ($domain === '' || !in_array($domain, $allowed, true)) {
        http_response_code(403);
        exit('Email domain not allowed.');
    }
}

// Upsert user
$pdo = client_require_db();
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare('SELECT id FROM client_users WHERE google_sub = ?');
    $stmt->execute([$sub]);
    $id = (int)($stmt->fetchColumn() ?: 0);
    if ($id > 0) {
        $stmt = $pdo->prepare('UPDATE client_users SET email = ?, full_name = ?, picture_url = ?, last_login_at = CURRENT_TIMESTAMP WHERE id = ?');
        $stmt->execute([$email, $name !== '' ? $name : $email, $picture, $id]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO client_users (google_sub, email, full_name, picture_url, last_login_at) VALUES (?,?,?,?, CURRENT_TIMESTAMP) RETURNING id');
        $stmt->execute([$sub, $email, $name !== '' ? $name : $email, $picture]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $id = (int)($row['id'] ?? 0);
        // default prefs
        if ($id > 0) {
            $pdo->prepare('INSERT INTO client_notification_prefs (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING')->execute([$id]);
        }
    }
    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    exit('Database error.');
}

$_SESSION['client_user_id'] = $id;
$_SESSION['client_email'] = $email;
$_SESSION['client_name'] = $name !== '' ? $name : $email;
$_SESSION['client_picture'] = $picture;

client_redirect('dashboard.php');

