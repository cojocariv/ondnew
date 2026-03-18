<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/config.php';

$clientsTableExists = false;
try {
    $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'client_users'");
    $clientsTableExists = $check && $check->fetch() !== false;
} catch (Throwable $e) {
    $clientsTableExists = false;
}

$rows = [];
$search = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($clientsTableExists) {
    $params = [];
    $sql = "SELECT u.id, u.email, u.full_name, u.created_at, u.last_login_at,
            (SELECT COUNT(*) FROM client_services s WHERE s.user_id = u.id AND s.status = 'active') AS active_services,
            (SELECT COALESCE(SUM(i.total_amount - i.paid_amount), 0) FROM client_invoices i WHERE i.user_id = u.id AND i.status IN ('unpaid','partial')) AS total_debt
            FROM client_users u";
    if ($search !== '') {
        $sql .= " WHERE u.email ILIKE :q OR u.full_name ILIKE :q";
        $params[':q'] = '%' . $search . '%';
    }
    $sql .= " ORDER BY u.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Clienți cabinet | Admin DB | ondsolutions.md</title>
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
        .table-wrap { max-height: 60vh; overflow: auto; }
        .table-wrap table { min-width: 640px; }
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
                <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Cereri contact</a>
                <a href="clients.php" class="text-sm font-medium text-primary-blue">Clienți cabinet</a>
                <a href="cabinet/conversations.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Cabinet chat</a>
                <a href="install_client_portal.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Instalare cabinet</a>
                <a href="../index.php" target="_blank" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Site</a>
                <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Deconectare</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <?php if (!$clientsTableExists): ?>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900">
            <h2 class="text-lg font-semibold">Tabelele cabinetului clienți nu există</h2>
            <p class="mt-2 text-sm">Rulează instalarea cabinetului o singură dată.</p>
            <a href="install_client_portal.php" class="mt-4 inline-block rounded-xl bg-primary-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95">Instalare cabinet clienți →</a>
        </div>
        <?php else: ?>
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Clienți cabinet</h1>
                <p class="mt-1 text-sm text-slate-500"><?php echo count($rows); ?> clienți înregistrați (Google)</p>
            </div>
            <form method="get" class="flex flex-wrap items-center gap-2">
                <input type="text" name="q" value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>"
                       placeholder="Caută după email sau nume..."
                       class="w-full min-w-[200px] max-w-sm rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20">
                <button type="submit" class="rounded-xl bg-primary-blue px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition">Caută</button>
                <?php if ($search !== ''): ?>
                <a href="clients.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-medium text-slate-600 hover:bg-slate-50">Resetează</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="table-wrap">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 sticky top-0">
                        <tr class="border-b border-slate-200">
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Nume</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Servicii active</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Datorie</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Ultima autentificare</th>
                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-500">Niciun client în cabinet. Clienții apar după autentificarea cu Google în cabinet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rows as $r): ?>
                        <tr class="border-b border-slate-100 transition">
                            <td class="px-4 py-3 text-slate-500 font-mono text-xs"><?php echo (int)$r['id']; ?></td>
                            <td class="px-4 py-3 font-medium text-slate-900"><?php echo htmlspecialchars($r['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 py-3">
                                <a href="mailto:<?php echo htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
                                   class="text-primary-blue hover:underline font-medium"><?php echo htmlspecialchars($r['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
                            </td>
                            <td class="px-4 py-3 text-slate-600"><?php echo (int)($r['active_services'] ?? 0); ?></td>
                            <td class="px-4 py-3 font-medium <?php echo (float)($r['total_debt'] ?? 0) > 0 ? 'text-amber-600' : 'text-slate-500'; ?>">
                                <?php echo number_format((float)($r['total_debt'] ?? 0), 2, ',', ' '); ?> MDL
                            </td>
                            <td class="px-4 py-3 text-slate-500 text-xs"><?php echo $r['last_login_at'] ? htmlspecialchars($r['last_login_at'], ENT_QUOTES, 'UTF-8') : '—'; ?></td>
                            <td class="px-4 py-3">
                                <a href="client_manage.php?id=<?php echo (int)$r['id']; ?>"
                                   class="rounded-lg bg-primary-blue px-3 py-1.5 text-xs font-semibold text-white hover:opacity-90 transition">Gestionează</a>
                            </td>
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
