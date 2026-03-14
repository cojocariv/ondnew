<?php
/**
 * Creează tabelele pentru chat (conversații + mesaje). Rulează o singură dată.
 * După succes, șterge acest fișier sau păstrează-l – verifică IF NOT EXISTS.
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
        $sql = file_get_contents(__DIR__ . '/chat_schema.sql');
        $sql = preg_replace('/--.*$/m', '', $sql);
        $parts = array_filter(array_map('trim', explode(';', $sql)));
        foreach ($parts as $stmt) {
            if ($stmt !== '' && stripos($stmt, 'CREATE') !== false) {
                $pdo->exec($stmt . ';');
            }
        }
        $message = 'Tabelele pentru chat au fost create. Poți accesa Cabinet → Conversații.';
    } catch (Throwable $e) {
        $error = 'Eroare: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
    }
}
$tableExists = false;
try {
    $c = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'chat_conversations'");
    $tableExists = $c && $c->fetch();
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Instalare Chat | ondsolutions.md</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 p-6 font-sans">
    <div class="max-w-xl mx-auto">
        <a href="dashboard.php" class="text-primary-blue hover:underline text-sm mb-4 inline-block">← Înapoi</a>
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900 mb-2">Instalare tabele chat</h1>
            <p class="text-slate-600 text-sm mb-4">Creează tabelele chat_conversations și chat_messages în baza de date.</p>
            <?php if ($message): ?>
                <div class="rounded-xl bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm mb-4"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm mb-4"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($tableExists): ?>
                <p class="text-slate-600 text-sm">Tabelele chat există deja. <a href="cabinet/conversations.php" class="text-primary-blue hover:underline">Mergi la Cabinet → Conversații</a>.</p>
            <?php else: ?>
                <form method="post">
                    <button type="submit" name="confirm" value="1" class="rounded-xl bg-primary-blue text-white px-4 py-2.5 text-sm font-semibold hover:opacity-95">Creează tabelele</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
