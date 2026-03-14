<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/config.php';

// Verifică dacă tabelul există; dacă nu, afișăm mesaj și link la instalare
$tableExists = false;
try {
    $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'contact_requests'");
    $tableExists = $check && $check->fetch() !== false;
} catch (Throwable $e) {
    $tableExists = false;
}

$rows = [];
$total = 0;
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($tableExists) {
    $params = [];
    $sql = 'SELECT id, first_name, last_name, email, message, created_at FROM contact_requests ';
    if ($search !== '') {
        $sql .= 'WHERE first_name LIKE :q OR last_name LIKE :q OR email LIKE :q ';
        $params[':q'] = '%' . $search . '%';
    }
    $sql .= 'ORDER BY created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
    $total = count($rows);
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Baza de date – Cereri contact | ondsolutions.md</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { 'primary-blue': '#2563eb' },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        };
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .table-wrap { max-height: 70vh; overflow: auto; }
        .table-wrap table { min-width: 700px; }
        .table-wrap tbody tr:nth-child(even) { background-color: rgba(248, 250, 252, 0.8); }
        .table-wrap tbody tr:hover { background-color: rgba(241, 245, 249, 1); }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <header class="sticky top-0 z-10 border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-blue text-white font-bold">S</div>
                    <span class="font-semibold text-slate-900">Admin – Baza de date</span>
                </a>
            </div>
            <div class="flex items-center gap-3">
                <a href="cabinet/conversations.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Cabinet chat</a>
                <a href="../index.php" target="_blank" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Site</a>
                <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                    Deconectare
                </a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <?php if (!$tableExists): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            <h2 class="text-lg font-semibold">Tabelul „contact_requests” nu există</h2>
            <p class="mt-2 text-sm">Creează tabelul în baza de date o singură dată, apoi reîncarcă această pagină.</p>
            <a href="install_table.php" class="mt-4 inline-block rounded-xl bg-primary-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95">Creează tabelul acum →</a>
        </div>
        <?php else: ?>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Cereri de contact</h1>
                <p class="mt-1 text-sm text-slate-500">
                    <?php echo $total === 0 ? 'Nicio cerere încă.' : $total . ' cereri în total.'; ?>
                </p>
            </div>
            <form method="get" class="flex flex-wrap items-center gap-2">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Caută după nume sau email..."
                       class="w-full min-w-[200px] max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20">
                <button type="submit" class="rounded-xl bg-primary-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition">
                    Caută
                </button>
                <?php if ($search !== ''): ?>
                <a href="dashboard.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Resetează</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Prenume</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Nume</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Mesaj</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                                Nu există cereri de contact. Formularul de pe site salvează aici datele.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $row): ?>
                        <tr class="border-b border-slate-100 transition">
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs"><?php echo (int)$row['id']; ?></td>
                            <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($row['first_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($row['last_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 py-3">
                                <a href="mailto:<?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   class="text-primary-blue hover:underline font-medium">
                                    <?php echo htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                            </td>
                            <td class="max-w-xs px-4 py-3 text-slate-600 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($row['message'] ?? '', ENT_QUOTES, 'UTF-8')); ?></td>
                            <td class="px-4 py-3 text-slate-500 text-xs"><?php echo htmlspecialchars($row['created_at'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
