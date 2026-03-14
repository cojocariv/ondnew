<?php
/**
 * Rulează o singură dată pentru a crea tabelul contact_requests.
 * După ce vezi "Tabel creat cu succes", șterge acest fișier (install_table.php).
 */
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/config.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    try {
        $sql = file_get_contents(__DIR__ . '/contact_requests.sql');
        // Elimină comentariile și liniile goale, păstrează doar CREATE TABLE
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = trim($sql);
        if (strpos($sql, 'CREATE TABLE') === false) {
            throw new Exception('Fișierul SQL nu conține CREATE TABLE.');
        }
        $pdo->exec($sql);
        $message = 'Tabelul contact_requests a fost creat (sau exista deja). Poți șterge acest fișier (install_table.php) din server.';
    } catch (Exception $e) {
        $error = 'Eroare: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

$tableExists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'contact_requests'");
    $tableExists = $stmt->fetch() !== false;
} catch (Exception $e) {
    $tableExists = false;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalare tabel | ondsolutions.md</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased p-6">
    <div class="max-w-xl mx-auto">
        <a href="dashboard.php" class="text-sm text-primary-blue hover:underline mb-4 inline-block">← Înapoi la panou</a>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900 mb-2">Instalare tabel contact_requests</h1>
            <p class="text-slate-600 text-sm mb-4">Acest script creează tabelul în baza de date (dacă nu există). Rulează o singură dată.</p>

            <?php if ($message): ?>
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-4"><?php echo $message; ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($tableExists && !$error): ?>
                <p class="text-slate-600 text-sm">Tabelul <strong>contact_requests</strong> există deja. Nu e nevoie să rulezi din nou.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="confirm" value="1" class="rounded-xl bg-primary-blue text-white px-4 py-2.5 text-sm font-semibold hover:opacity-95">Creează tabelul</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
