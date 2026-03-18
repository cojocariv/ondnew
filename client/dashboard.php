<?php
require __DIR__ . '/lib.php';
if (!client_is_logged_in()) client_redirect('index.php');

$pdo = client_require_db();
$uid = (int)$_SESSION['client_user_id'];

// user
$stmt = $pdo->prepare('SELECT id, email, full_name, picture_url FROM client_users WHERE id = ?');
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    session_destroy();
    client_redirect('index.php');
}

// services
$stmt = $pdo->prepare("
    SELECT cs.id, sc.name AS service_name, cs.package_name, cs.status, cs.started_at, cs.next_billing_date,
           cs.monthly_amount, cs.currency
    FROM client_services cs
    JOIN service_catalog sc ON sc.id = cs.service_id
    WHERE cs.user_id = ?
    ORDER BY cs.status = 'active' DESC, cs.next_billing_date NULLS LAST, cs.created_at DESC
");
$stmt->execute([$uid]);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

// invoices and balance
$stmt = $pdo->prepare("
    SELECT id, invoice_no, due_date, total_amount, paid_amount, currency, status, created_at
    FROM client_invoices
    WHERE user_id = ?
    ORDER BY COALESCE(due_date, created_at) DESC
    LIMIT 10
");
$stmt->execute([$uid]);
$invoices = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount - paid_amount), 0) AS debt FROM client_invoices WHERE user_id = ? AND status IN ('unpaid','partial')");
$stmt->execute([$uid]);
$debt = (float)($stmt->fetchColumn() ?: 0);

// prefs
$stmt = $pdo->prepare("SELECT email_billing, email_service, email_marketing FROM client_notification_prefs WHERE user_id = ?");
$stmt->execute([$uid]);
$prefs = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['email_billing'=>true,'email_service'=>true,'email_marketing'=>false];

function money($amount, $currency) {
    return number_format((float)$amount, 2, '.', ' ') . ' ' . $currency;
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Cabinet Client | Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-blue text-white font-bold">S</div>
                <div>
                    <div class="font-semibold text-slate-900">Cabinet Client</div>
                    <div class="text-xs text-slate-500"><?php echo client_h($user['email']); ?></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="settings.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Setări</a>
                <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Logout</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6 space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Datorii curente</p>
                <p class="mt-2 text-2xl font-bold text-slate-900"><?php echo money($debt, 'MDL'); ?></p>
                <p class="mt-1 text-xs text-slate-500">Calculat din facturile neplătite/parțial.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Notificări email</p>
                <p class="mt-2 text-sm text-slate-700">
                    <?php echo !empty($prefs['email_billing']) ? 'Facturare: ON' : 'Facturare: OFF'; ?> ·
                    <?php echo !empty($prefs['email_service']) ? 'Servicii: ON' : 'Servicii: OFF'; ?>
                </p>
                <a href="settings.php" class="mt-3 inline-block text-sm font-semibold text-primary-blue hover:underline">Modifică setările</a>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-semibold text-slate-500">Servicii active</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">
                    <?php echo (int)count(array_filter($services, fn($s) => ($s['status'] ?? '') === 'active')); ?>
                </p>
                <p class="mt-1 text-xs text-slate-500">Suspendate/anulate apar separat.</p>
            </div>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="font-semibold text-slate-900">Serviciile mele</h2>
                <a href="../#contact" class="text-sm font-semibold text-primary-blue hover:underline">Cere serviciu nou</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3">Serviciu</th>
                            <th class="px-5 py-3">Pachet</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3">Următoarea facturare</th>
                            <th class="px-5 py-3">Preț</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($services)): ?>
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Nu ai servicii atașate încă. După ce activăm un serviciu, îl vei vedea aici.</td></tr>
                        <?php else: ?>
                            <?php foreach ($services as $s): ?>
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-5 py-3 font-medium text-slate-900"><?php echo client_h($s['service_name']); ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo client_h($s['package_name']); ?></td>
                                    <td class="px-5 py-3">
                                        <?php
                                            $st = $s['status'] ?? 'active';
                                            $badge = $st === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($st === 'suspended' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-slate-50 text-slate-600 border-slate-200');
                                        ?>
                                        <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo $badge; ?>"><?php echo client_h($st); ?></span>
                                    </td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo $s['next_billing_date'] ? client_h($s['next_billing_date']) : '-'; ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo money($s['monthly_amount'], $s['currency'] ?: 'MDL'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-semibold text-slate-900">Ultimele facturi</h2>
                <p class="text-xs text-slate-500 mt-1">Afișează ultimele 10 intrări.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm text-left">
                    <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                        <tr class="border-b border-slate-100">
                            <th class="px-5 py-3">Factura</th>
                            <th class="px-5 py-3">Scadență</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Plătit</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($invoices)): ?>
                            <tr><td colspan="5" class="px-5 py-10 text-center text-slate-500">Nu există facturi încă.</td></tr>
                        <?php else: ?>
                            <?php foreach ($invoices as $inv): ?>
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-5 py-3 text-slate-900 font-medium"><?php echo client_h($inv['invoice_no'] ?: ('#' . $inv['id'])); ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo $inv['due_date'] ? client_h($inv['due_date']) : '-'; ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo money($inv['total_amount'], $inv['currency'] ?: 'MDL'); ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo money($inv['paid_amount'], $inv['currency'] ?: 'MDL'); ?></td>
                                    <td class="px-5 py-3 text-slate-700"><?php echo client_h($inv['status']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>

