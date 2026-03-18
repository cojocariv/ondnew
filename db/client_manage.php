<?php
session_start();
if (empty($_SESSION['db_admin_logged_in'])) {
    header('Location: index.php');
    exit;
}

require __DIR__ . '/config.php';

$userId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($userId <= 0) {
    header('Location: clients.php');
    exit;
}

// Încarcă clientul
$stmt = $pdo->prepare("SELECT id, email, full_name, created_at, last_login_at FROM client_users WHERE id = ?");
$stmt->execute([$userId]);
$client = $stmt->fetch();
if (!$client) {
    header('Location: clients.php');
    exit;
}

$message = '';
$error = '';

// —— POST: acțiuni CRUD ——
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? trim($_POST['action']) : '';

    if ($action === 'add_service') {
        $serviceId = (int) ($_POST['service_id'] ?? 0);
        $packageName = trim($_POST['package_name'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'suspended', 'cancelled']) ? $_POST['status'] : 'active';
        $nextBilling = !empty($_POST['next_billing_date']) ? $_POST['next_billing_date'] : null;
        $monthlyAmount = (float) str_replace(',', '.', $_POST['monthly_amount'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'MDL') ?: 'MDL';
        $notes = trim($_POST['notes'] ?? '');
        if ($serviceId > 0 && $packageName !== '') {
            try {
                $ins = $pdo->prepare("INSERT INTO client_services (user_id, service_id, package_name, status, next_billing_date, monthly_amount, currency, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $ins->execute([$userId, $serviceId, $packageName, $status, $nextBilling, $monthlyAmount, $currency, $notes ?: null]);
                $message = 'Serviciu adăugat.';
            } catch (Throwable $e) {
                $error = 'Eroare la adăugare serviciu: ' . $e->getMessage();
            }
        } else {
            $error = 'Selectează un serviciu din catalog și completează denumirea pachetului.';
        }
    } elseif ($action === 'edit_service') {
        $sid = (int) ($_POST['service_row_id'] ?? 0);
        $packageName = trim($_POST['package_name'] ?? '');
        $status = in_array($_POST['status'] ?? '', ['active', 'suspended', 'cancelled']) ? $_POST['status'] : 'active';
        $nextBilling = !empty($_POST['next_billing_date']) ? $_POST['next_billing_date'] : null;
        $monthlyAmount = (float) str_replace(',', '.', $_POST['monthly_amount'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'MDL') ?: 'MDL';
        $notes = trim($_POST['notes'] ?? '');
        if ($sid > 0 && $packageName !== '') {
            try {
                $up = $pdo->prepare("UPDATE client_services SET package_name = ?, status = ?, next_billing_date = ?, monthly_amount = ?, currency = ?, notes = ? WHERE id = ? AND user_id = ?");
                $up->execute([$packageName, $status, $nextBilling, $monthlyAmount, $currency, $notes ?: null, $sid, $userId]);
                $message = 'Serviciu actualizat.';
            } catch (Throwable $e) {
                $error = 'Eroare la actualizare: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_service') {
        $sid = (int) ($_POST['service_row_id'] ?? 0);
        if ($sid > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM client_services WHERE id = ? AND user_id = ?");
                $del->execute([$sid, $userId]);
                $message = 'Serviciu șters.';
            } catch (Throwable $e) {
                $error = 'Eroare la ștergere: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'add_invoice') {
        $invoiceNo = trim($_POST['invoice_no'] ?? '');
        $periodStart = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $periodEnd = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $totalAmount = (float) str_replace(',', '.', $_POST['total_amount'] ?? 0);
        $paidAmount = (float) str_replace(',', '.', $_POST['paid_amount'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'MDL') ?: 'MDL';
        $status = in_array($_POST['status'] ?? '', ['unpaid', 'partial', 'paid', 'void']) ? $_POST['status'] : 'unpaid';
        try {
            $ins = $pdo->prepare("INSERT INTO client_invoices (user_id, invoice_no, period_start, period_end, due_date, total_amount, paid_amount, currency, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $ins->execute([$userId, $invoiceNo ?: null, $periodStart, $periodEnd, $dueDate, $totalAmount, $paidAmount, $currency, $status]);
            $message = 'Factură adăugată.';
        } catch (Throwable $e) {
            $error = 'Eroare la adăugare factură: ' . $e->getMessage();
        }
    } elseif ($action === 'edit_invoice') {
        $iid = (int) ($_POST['invoice_row_id'] ?? 0);
        $invoiceNo = trim($_POST['invoice_no'] ?? '');
        $periodStart = !empty($_POST['period_start']) ? $_POST['period_start'] : null;
        $periodEnd = !empty($_POST['period_end']) ? $_POST['period_end'] : null;
        $dueDate = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        $totalAmount = (float) str_replace(',', '.', $_POST['total_amount'] ?? 0);
        $paidAmount = (float) str_replace(',', '.', $_POST['paid_amount'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'MDL') ?: 'MDL';
        $status = in_array($_POST['status'] ?? '', ['unpaid', 'partial', 'paid', 'void']) ? $_POST['status'] : 'unpaid';
        if ($iid > 0) {
            try {
                $up = $pdo->prepare("UPDATE client_invoices SET invoice_no = ?, period_start = ?, period_end = ?, due_date = ?, total_amount = ?, paid_amount = ?, currency = ?, status = ? WHERE id = ? AND user_id = ?");
                $up->execute([$invoiceNo ?: null, $periodStart, $periodEnd, $dueDate, $totalAmount, $paidAmount, $currency, $status, $iid, $userId]);
                $message = 'Factură actualizată.';
            } catch (Throwable $e) {
                $error = 'Eroare la actualizare factură: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'delete_invoice') {
        $iid = (int) ($_POST['invoice_row_id'] ?? 0);
        if ($iid > 0) {
            try {
                $del = $pdo->prepare("DELETE FROM client_invoices WHERE id = ? AND user_id = ?");
                $del->execute([$iid, $userId]);
                $message = 'Factură ștearsă.';
            } catch (Throwable $e) {
                $error = 'Eroare la ștergere factură: ' . $e->getMessage();
            }
        }
    }

    if ($message || $error) {
        header('Location: client_manage.php?id=' . $userId . ($message ? '&ok=1' : '') . ($error ? '&err=1' : ''));
        exit;
    }
}

if (isset($_GET['ok'])) $message = 'Operațiune reușită.';
if (isset($_GET['err'])) $error = 'A apărut o eroare.';

// Catalog servicii pentru dropdown
$catalog = [];
try {
    $catalog = $pdo->query("SELECT id, code, name FROM service_catalog ORDER BY name")->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Servicii client
$services = [];
try {
    $st = $pdo->prepare("SELECT s.id, s.package_name, s.status, s.next_billing_date, s.monthly_amount, s.currency, s.notes, c.code AS service_code, c.name AS service_name
                         FROM client_services s
                         JOIN service_catalog c ON c.id = s.service_id
                         WHERE s.user_id = ?
                         ORDER BY s.id DESC");
    $st->execute([$userId]);
    $services = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

// Facturi client
$invoices = [];
try {
    $st = $pdo->prepare("SELECT id, invoice_no, period_start, period_end, due_date, total_amount, paid_amount, currency, status, created_at FROM client_invoices WHERE user_id = ? ORDER BY id DESC");
    $st->execute([$userId]);
    $invoices = $st->fetchAll();
} catch (Throwable $e) { /* ignore */ }

function esc($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Gestionează client <?php echo esc($client['full_name']); ?> | Admin DB</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] } } } };
    </script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
        .table-sm { font-size: 0.875rem; }
        .table-sm th, .table-sm td { padding: 0.5rem 0.75rem; }
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
                <a href="clients.php" class="text-sm font-medium text-primary-blue">← Clienți cabinet</a>
                <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Cereri contact</a>
                <a href="cabinet/conversations.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Cabinet chat</a>
                <a href="../index.php" target="_blank" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Site</a>
                <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">Deconectare</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900"><?php echo esc($client['full_name']); ?></h1>
            <p class="mt-1 text-slate-600">
                <a href="mailto:<?php echo esc($client['email']); ?>" class="text-primary-blue hover:underline"><?php echo esc($client['email']); ?></a>
                · ID: <?php echo (int)$client['id']; ?>
                · Înregistrat: <?php echo esc($client['created_at']); ?>
                <?php if (!empty($client['last_login_at'])): ?> · Ultima autentificare: <?php echo esc($client['last_login_at']); ?><?php endif; ?>
            </p>
        </div>

        <?php if ($message): ?>
        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 text-sm"><?php echo esc($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 text-sm"><?php echo esc($error); ?></div>
        <?php endif; ?>

        <!-- Servicii -->
        <section class="mb-8 rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-lg font-semibold text-slate-900">Servicii</h2>
                <button type="button" onclick="document.getElementById('add-service-form').classList.toggle('hidden')"
                        class="rounded-lg bg-primary-blue px-3 py-1.5 text-sm font-semibold text-white hover:opacity-90 transition">+ Adaugă serviciu</button>
            </div>

            <div id="add-service-form" class="hidden border-b border-slate-100 bg-slate-50/50 p-4">
                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <input type="hidden" name="action" value="add_service">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Serviciu (catalog)</label>
                        <select name="service_id" required class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="">— Selectează —</option>
                            <?php foreach ($catalog as $cat): ?>
                            <option value="<?php echo (int)$cat['id']; ?>"><?php echo esc($cat['name']); ?> (<?php echo esc($cat['code']); ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Denumire pachet</label>
                        <input type="text" name="package_name" required placeholder="ex. Hosting Pro" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="active">Activ</option>
                            <option value="suspended">Suspendat</option>
                            <option value="cancelled">Anulat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Data facturare</label>
                        <input type="date" name="next_billing_date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumă lunară</label>
                        <input type="text" name="monthly_amount" value="0" placeholder="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Monedă</label>
                        <input type="text" name="currency" value="MDL" maxlength="10" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-slate-500 mb-1">Note</label>
                        <input type="text" name="notes" placeholder="Opțional" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <button type="submit" class="rounded-lg bg-primary-blue px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition">Salvează</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Serviciu</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Pachet</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Facturare</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase">Sumă lunară</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($services)): ?>
                        <tr><td colspan="6" class="py-6 text-center text-slate-500">Niciun serviciu. Adaugă unul cu butonul de mai sus.</td></tr>
                    <?php else: ?>
                        <?php foreach ($services as $sv): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                            <td><?php echo esc($sv['service_name']); ?> (<?php echo esc($sv['service_code']); ?>)</td>
                            <td><?php echo esc($sv['package_name']); ?></td>
                            <td>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                    <?php echo $sv['status'] === 'active' ? 'bg-emerald-100 text-emerald-800' : ($sv['status'] === 'suspended' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'); ?>">
                                    <?php echo esc($sv['status']); ?>
                                </span>
                            </td>
                            <td><?php echo $sv['next_billing_date'] ? esc($sv['next_billing_date']) : '—'; ?></td>
                            <td class="text-right font-medium"><?php echo number_format((float)$sv['monthly_amount'], 2, ',', ' '); ?> <?php echo esc($sv['currency']); ?></td>
                            <td>
                                <button type="button" onclick="toggleEditService(<?php echo (int)$sv['id']; ?>)" class="text-primary-blue text-xs font-medium hover:underline">Editează</button>
                                <form method="post" class="inline ml-1" onsubmit="return confirm('Ștergi acest serviciu?');">
                                    <input type="hidden" name="action" value="delete_service">
                                    <input type="hidden" name="service_row_id" value="<?php echo (int)$sv['id']; ?>">
                                    <button type="submit" class="text-red-600 text-xs font-medium hover:underline">Șterge</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-service-<?php echo (int)$sv['id']; ?>" class="hidden bg-slate-50/80 border-b border-slate-100">
                            <td colspan="6" class="p-3">
                                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 items-end">
                                    <input type="hidden" name="action" value="edit_service">
                                    <input type="hidden" name="service_row_id" value="<?php echo (int)$sv['id']; ?>">
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Pachet</label>
                                        <input type="text" name="package_name" value="<?php echo esc($sv['package_name']); ?>" required class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Status</label>
                                        <select name="status" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                            <option value="active" <?php echo $sv['status'] === 'active' ? 'selected' : ''; ?>>Activ</option>
                                            <option value="suspended" <?php echo $sv['status'] === 'suspended' ? 'selected' : ''; ?>>Suspendat</option>
                                            <option value="cancelled" <?php echo $sv['status'] === 'cancelled' ? 'selected' : ''; ?>>Anulat</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Data facturare</label>
                                        <input type="date" name="next_billing_date" value="<?php echo esc($sv['next_billing_date'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Sumă lunară</label>
                                        <input type="text" name="monthly_amount" value="<?php echo esc($sv['monthly_amount']); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Monedă</label>
                                        <input type="text" name="currency" value="<?php echo esc($sv['currency']); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="sm:col-span-2">
                                        <label class="block text-xs text-slate-500 mb-1">Note</label>
                                        <input type="text" name="notes" value="<?php echo esc($sv['notes'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="rounded bg-primary-blue px-3 py-1.5 text-sm font-medium text-white hover:opacity-90">Salvează</button>
                                        <button type="button" onclick="toggleEditService(<?php echo (int)$sv['id']; ?>)" class="rounded border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Anulare</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Facturi -->
        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-lg font-semibold text-slate-900">Facturi</h2>
                <button type="button" onclick="document.getElementById('add-invoice-form').classList.toggle('hidden')"
                        class="rounded-lg bg-primary-blue px-3 py-1.5 text-sm font-semibold text-white hover:opacity-90 transition">+ Adaugă factură</button>
            </div>

            <div id="add-invoice-form" class="hidden border-b border-slate-100 bg-slate-50/50 p-4">
                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                    <input type="hidden" name="action" value="add_invoice">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Nr. factură</label>
                        <input type="text" name="invoice_no" placeholder="ex. F-2026-001" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Perioadă de la</label>
                        <input type="date" name="period_start" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Perioadă până la</label>
                        <input type="date" name="period_end" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Scadentă</label>
                        <input type="date" name="due_date" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumă totală</label>
                        <input type="text" name="total_amount" value="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Sumă plătită</label>
                        <input type="text" name="paid_amount" value="0" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Monedă</label>
                        <input type="text" name="currency" value="MDL" maxlength="10" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Status</label>
                        <select name="status" class="w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                            <option value="unpaid">Neplătită</option>
                            <option value="partial">Plată parțială</option>
                            <option value="paid">Plătită</option>
                            <option value="void">Anulată</option>
                        </select>
                    </div>
                    <div>
                        <button type="submit" class="rounded-lg bg-primary-blue px-4 py-2 text-sm font-semibold text-white hover:opacity-90 transition">Salvează</button>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Nr.</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Perioadă</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Scadentă</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase">Total</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase">Plătit</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Status</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($invoices)): ?>
                        <tr><td colspan="7" class="py-6 text-center text-slate-500">Nicio factură. Adaugă una cu butonul de mai sus.</td></tr>
                    <?php else: ?>
                        <?php foreach ($invoices as $inv): ?>
                        <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                            <td><?php echo esc($inv['invoice_no'] ?: '—'); ?></td>
                            <td><?php echo $inv['period_start'] || $inv['period_end'] ? esc(($inv['period_start'] ?? '') . ' – ' . ($inv['period_end'] ?? '')) : '—'; ?></td>
                            <td><?php echo $inv['due_date'] ? esc($inv['due_date']) : '—'; ?></td>
                            <td class="text-right font-medium"><?php echo number_format((float)$inv['total_amount'], 2, ',', ' '); ?> <?php echo esc($inv['currency']); ?></td>
                            <td class="text-right"><?php echo number_format((float)$inv['paid_amount'], 2, ',', ' '); ?> <?php echo esc($inv['currency']); ?></td>
                            <td>
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium
                                    <?php echo $inv['status'] === 'paid' ? 'bg-emerald-100 text-emerald-800' : ($inv['status'] === 'partial' ? 'bg-amber-100 text-amber-800' : ($inv['status'] === 'void' ? 'bg-slate-100 text-slate-600' : 'bg-red-100 text-red-800')); ?>">
                                    <?php echo esc($inv['status']); ?>
                                </span>
                            </td>
                            <td>
                                <button type="button" onclick="toggleEditInvoice(<?php echo (int)$inv['id']; ?>)" class="text-primary-blue text-xs font-medium hover:underline">Editează</button>
                                <form method="post" class="inline ml-1" onsubmit="return confirm('Ștergi această factură?');">
                                    <input type="hidden" name="action" value="delete_invoice">
                                    <input type="hidden" name="invoice_row_id" value="<?php echo (int)$inv['id']; ?>">
                                    <button type="submit" class="text-red-600 text-xs font-medium hover:underline">Șterge</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="edit-invoice-<?php echo (int)$inv['id']; ?>" class="hidden bg-slate-50/80 border-b border-slate-100">
                            <td colspan="7" class="p-3">
                                <form method="post" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-2 items-end">
                                    <input type="hidden" name="action" value="edit_invoice">
                                    <input type="hidden" name="invoice_row_id" value="<?php echo (int)$inv['id']; ?>">
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Nr. factură</label>
                                        <input type="text" name="invoice_no" value="<?php echo esc($inv['invoice_no'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Perioadă de la</label>
                                        <input type="date" name="period_start" value="<?php echo esc($inv['period_start'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Perioadă până la</label>
                                        <input type="date" name="period_end" value="<?php echo esc($inv['period_end'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Scadentă</label>
                                        <input type="date" name="due_date" value="<?php echo esc($inv['due_date'] ?? ''); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Total</label>
                                        <input type="text" name="total_amount" value="<?php echo esc($inv['total_amount']); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Plătit</label>
                                        <input type="text" name="paid_amount" value="<?php echo esc($inv['paid_amount']); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Monedă</label>
                                        <input type="text" name="currency" value="<?php echo esc($inv['currency']); ?>" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-slate-500 mb-1">Status</label>
                                        <select name="status" class="w-full rounded border border-slate-200 px-2 py-1.5 text-sm">
                                            <option value="unpaid" <?php echo $inv['status'] === 'unpaid' ? 'selected' : ''; ?>>Neplătită</option>
                                            <option value="partial" <?php echo $inv['status'] === 'partial' ? 'selected' : ''; ?>>Plată parțială</option>
                                            <option value="paid" <?php echo $inv['status'] === 'paid' ? 'selected' : ''; ?>>Plătită</option>
                                            <option value="void" <?php echo $inv['status'] === 'void' ? 'selected' : ''; ?>>Anulată</option>
                                        </select>
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="submit" class="rounded bg-primary-blue px-3 py-1.5 text-sm font-medium text-white hover:opacity-90">Salvează</button>
                                        <button type="button" onclick="toggleEditInvoice(<?php echo (int)$inv['id']; ?>)" class="rounded border border-slate-300 px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-50">Anulare</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
    <script>
        function toggleEditService(id) {
            var row = document.getElementById('edit-service-' + id);
            if (row) row.classList.toggle('hidden');
        }
        function toggleEditInvoice(id) {
            var row = document.getElementById('edit-invoice-' + id);
            if (row) row.classList.toggle('hidden');
        }
    </script>
</body>
</html>
