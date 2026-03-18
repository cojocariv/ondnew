<?php
/**
 * Creează tabelele pentru Cabinet Client (Google login + servicii + facturi + notificări).
 * Rulează o singură dată după autentificare la /db/.
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
        $sql = file_get_contents(__DIR__ . '/client_portal_schema.sql');
        $sql = preg_replace('/--.*$/m', '', $sql);
        $parts = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($parts as $stmt) {
            if ($stmt !== '' && (stripos($stmt, 'CREATE') !== false || stripos($stmt, 'INSERT') !== false)) {
                $pdo->exec($stmt . ';');
            }
        }
        $message = 'Cabinet Client a fost instalat. Acces: /client/';
    } catch (Throwable $e) {
        $error = 'Eroare: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}

$tablesOk = false;
try {
    $c = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'client_users'");
    $tablesOk = $c && $c->fetch();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Instalare Cabinet Client | ondsolutions.md</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 p-6 font-sans">
    <div class="max-w-xl mx-auto">
        <a href="dashboard.php" class="text-primary-blue hover:underline text-sm mb-4 inline-block">← Înapoi</a>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900 mb-2">Instalare tabele Cabinet Client</h1>
            <p class="text-slate-600 text-sm mb-4">Creează tabelele pentru utilizatori, servicii, facturi și preferințe notificări.</p>

            <?php if ($message): ?>
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-4"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if ($tablesOk): ?>
                <p class="text-slate-600 text-sm">Tabelele există deja. Mergi la <a class="text-primary-blue hover:underline" href="../client/">/client/</a>.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="confirm" value="1" class="rounded-xl bg-primary-blue text-white px-4 py-2.5 text-sm font-semibold hover:opacity-95">Instalează tabelele</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

