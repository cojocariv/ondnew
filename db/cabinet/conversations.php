<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}
require __DIR__ . '/../config.php';

$chatTablesExist = false;
try {
    $c = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'chat_conversations'");
    $chatTablesExist = $c && $c->fetch();
} catch (Throwable $e) {}

$conversations = [];
if ($chatTablesExist) {
    try {
        $stmt = $pdo->query("
            SELECT c.id, c.visitor_name, c.visitor_email, c.created_at, c.updated_at,
                   (SELECT body FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
                   (SELECT sender_type FROM chat_messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_sender_type
            FROM chat_conversations c
            ORDER BY c.updated_at DESC
        ");
        $conversations = $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
    } catch (Throwable $e) {}
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../../favicon.svg" type="image/svg+xml">
    <title>Cabinet – Conversații chat | ondsolutions.md</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto max-w-4xl flex items-center justify-between px-4 py-4">
            <div class="flex items-center gap-3">
                <a href="conversations.php" class="font-semibold text-slate-900">Cabinet – Chat</a>
                <a href="../dashboard.php" class="text-sm text-slate-500 hover:text-primary-blue">Cereri contact</a>
            </div>
            <a href="../logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Deconectare</a>
        </div>
    </header>
    <main class="mx-auto max-w-4xl px-4 py-6">
        <h1 class="text-2xl font-bold text-slate-900 mb-4">Conversații cu clienții</h1>
        <?php if (!$chatTablesExist): ?>
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
                <p class="font-medium">Tabelele pentru chat nu există.</p>
                <p class="mt-2 text-sm">Rulează <a href="../install_chat.php" class="text-primary-blue underline">Instalare chat</a> o singură dată, apoi reîncarcă.</p>
            </div>
        <?php elseif (empty($conversations)): ?>
            <div class="rounded-2xl border border-slate-200 bg-white p-8 text-center text-slate-500">
                <p>Nicio conversație încă. Clienții pot deschide chat-ul pe site și să înceapă o conversație.</p>
            </div>
        <?php else: ?>
            <ul class="space-y-2">
                <?php foreach ($conversations as $conv):
                    $isUnread = isset($conv['last_sender_type']) && $conv['last_sender_type'] === 'visitor';
                ?>
                <li>
                    <a href="conversation.php?id=<?php echo (int)$conv['id']; ?>" class="block rounded-xl border p-4 hover:border-primary-blue/40 hover:shadow-md transition <?php echo $isUnread ? 'bg-primary-blue/10 border-primary-blue/30' : 'bg-white border-slate-200'; ?>">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($conv['visitor_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <span class="text-slate-500 text-sm ml-2"><?php echo htmlspecialchars($conv['visitor_email'], ENT_QUOTES, 'UTF-8'); ?></span>
                            </div>
                            <span class="text-xs text-slate-400"><?php echo date('d.m.Y H:i', strtotime($conv['updated_at'])); ?></span>
                        </div>
                        <?php if (!empty($conv['last_message'])): ?>
                        <p class="mt-2 text-sm text-slate-600 truncate"><?php echo htmlspecialchars(mb_substr($conv['last_message'], 0, 120), ENT_QUOTES, 'UTF-8'); ?><?php echo mb_strlen($conv['last_message']) > 120 ? '…' : ''; ?></p>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>
