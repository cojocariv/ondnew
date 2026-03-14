<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: ../index.php');
    exit;
}
require __DIR__ . '/../config.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id < 1) {
    header('Location: conversations.php');
    exit;
}

$conv = null;
$messages = [];
try {
    $stmt = $pdo->prepare('SELECT id, visitor_name, visitor_email, created_at FROM chat_conversations WHERE id = ?');
    $stmt->execute([$id]);
    $conv = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($conv) {
        $stmt = $pdo->prepare('SELECT id, sender_type, body, created_at FROM chat_messages WHERE conversation_id = ? ORDER BY created_at ASC');
        $stmt->execute([$id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (Throwable $e) {}
if (!$conv) {
    header('Location: conversations.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['body'])) {
    $body = trim((string) $_POST['body']);
    if ($body !== '') {
        $body = mb_substr($body, 0, 4000);
        try {
            $pdo->prepare('INSERT INTO chat_messages (conversation_id, sender_type, body) VALUES (?, \'admin\', ?)')->execute([$id, $body]);
            $pdo->prepare('UPDATE chat_conversations SET updated_at = CURRENT_TIMESTAMP WHERE id = ?')->execute([$id]);
            header('Location: conversation.php?id=' . $id);
            exit;
        } catch (Throwable $e) {}
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Conversație cu <?php echo htmlspecialchars($conv['visitor_name'], ENT_QUOTES, 'UTF-8'); ?> | Cabinet</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto max-w-3xl flex items-center justify-between px-4 py-4">
            <div class="flex items-center gap-3">
                <a href="conversations.php" class="text-slate-600 hover:text-primary-blue text-sm">← Conversații</a>
                <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($conv['visitor_name'], ENT_QUOTES, 'UTF-8'); ?></span>
                <a href="mailto:<?php echo htmlspecialchars($conv['visitor_email'], ENT_QUOTES, 'UTF-8'); ?>" class="text-sm text-primary-blue hover:underline"><?php echo htmlspecialchars($conv['visitor_email'], ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <a href="../logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Deconectare</a>
        </div>
    </header>
    <main class="mx-auto max-w-3xl px-4 py-6">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden flex flex-col" style="min-height: 400px;">
            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-3" style="max-height: 50vh;">
                <?php foreach ($messages as $m): ?>
                <div class="flex <?php echo $m['sender_type'] === 'admin' ? 'justify-end' : 'justify-start'; ?>">
                    <div class="max-w-[85%] rounded-2xl px-4 py-2 text-sm <?php echo $m['sender_type'] === 'admin' ? 'bg-primary-blue text-white' : 'bg-slate-100 text-slate-800'; ?>">
                        <?php echo nl2br(htmlspecialchars($m['body'], ENT_QUOTES, 'UTF-8')); ?>
                        <span class="block text-xs mt-1 opacity-80"><?php echo date('d.m.Y H:i', strtotime($m['created_at'])); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <form method="post" class="p-4 border-t border-slate-100">
                <div class="flex gap-2">
                    <textarea name="body" placeholder="Răspunde clientului..." rows="2" required class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm resize-none focus:border-primary-blue focus:ring-1 focus:ring-primary-blue" maxlength="4000"></textarea>
                    <button type="submit" class="self-end rounded-xl bg-primary-blue text-white px-4 py-2 text-sm font-semibold hover:opacity-95 shrink-0">Trimite</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
