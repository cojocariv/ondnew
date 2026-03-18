<?php
require __DIR__ . '/lib.php';
if (!client_is_logged_in()) client_redirect('index.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

$pdo = client_require_db();
$uid = (int)$_SESSION['client_user_id'];

$error = '';
$success = '';
$adminEmail = '';

// Client info
$stmt = $pdo->prepare('SELECT id, email, full_name FROM client_users WHERE id = ?');
$stmt->execute([$uid]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$client) {
    session_destroy();
    client_redirect('index.php');
}

// Catalog services
$services = [];
try {
    $st = $pdo->query('SELECT id, code, name, description FROM service_catalog ORDER BY name');
    $services = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $error = 'Catalogul de servicii nu este disponibil.';
}

// Admin email (from site_data)
try {
    require __DIR__ . '/../includes/site_data.php';
    $adminEmail = $site_data['contact']['email_sales'] ?? 'sales@ondsolutions.md';
} catch (Throwable $e) {
    $adminEmail = 'sales@ondsolutions.md';
}

// Prețuri (din site_data.json) - mapate pe service code
$priceByServiceId = [];
$priceByServiceCode = [
    'hosting' => 0,
    'vps' => 0,
    '1c' => 0,
    'm365' => 0,
];

$getMinPrice = function ($arr, $key = 'price'): float {
    if (!is_array($arr) || empty($arr)) return 0.0;
    $prices = [];
    foreach ($arr as $p) {
        $val = 0.0;
        if (is_array($p) && array_key_exists($key, $p)) {
            $val = (float)($p[$key] ?? 0);
        }
        if ($val > 0) $prices[] = $val;
    }
    if (empty($prices)) return 0.0;
    return (float)min($prices);
};

try {
    $priceByServiceCode['hosting'] = $getMinPrice($site_data['hosting'] ?? [], 'price');
    $priceByServiceCode['vps'] = $getMinPrice($site_data['vps'] ?? [], 'price');
    $priceByServiceCode['1c'] = (float)(($site_data['1c']['min_price'] ?? null) ?: ($site_data['1c']['base_price'] ?? 0));
} catch (Throwable $e) {
    // dacă site_data nu conține chei, lăsăm prețurile la 0
}

foreach ($services as $s) {
    $id = (int)($s['id'] ?? 0);
    $code = (string)($s['code'] ?? '');
    if ($id > 0) $priceByServiceId[$id] = (float)($priceByServiceCode[$code] ?? 0);
}

// Table existence (so we can show a friendly message)
$requestsTableExists = false;
try {
    $check = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_name = 'client_service_requests'");
    $requestsTableExists = $check && $check->fetch() !== false;
} catch (Throwable $e) {
    $requestsTableExists = false;
}

function fnum($v): float {
    $s = is_string($v) ? trim($v) : '';
    if ($s === '') return 0.0;
    $s = str_replace(',', '.', $s);
    return (float)$s;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!client_check_csrf($_POST['csrf'] ?? '')) {
        $error = 'Token CSRF invalid. Reîncarcă pagina și încearcă din nou.';
    } else if (!$requestsTableExists) {
        $error = 'Tipul de cereri nu este instalat în baza de date. Verifică instalarea Cabinet Client (admin).';
    } else {
        $selected = $_POST['selected_services'] ?? [];
        if (!is_array($selected)) $selected = [];
        $selected = array_values(array_unique(array_filter(array_map('intval', $selected), fn($x) => $x > 0)));

        if (count($selected) === 0) {
            $error = 'Te rugăm să bifezi cel puțin un serviciu.';
        } else {
            $rows = [];
            foreach ($selected as $sid) {
                $periodMonths = (int)($_POST['period_months'][$sid] ?? 0);
                $pricePerUnit = (float)($priceByServiceId[$sid] ?? 0);

                if ($periodMonths < 1 || $periodMonths > 10) {
                    $error = 'Selectează o „Perioadă” între 1 și 10 luni pentru fiecare serviciu.';
                    break;
                }

                if ($pricePerUnit <= 0) {
                    $error = 'Prețul nu este disponibil pentru unul dintre servicii (contactează administratorul).';
                    break;
                }

                $periodText = $periodMonths . ' luni';
                $amount = (float)$periodMonths; // păstrăm câmpul „amount” ca număr de luni
                $totalAmount = $pricePerUnit * $periodMonths;

                $rows[] = [
                    'service_id' => $sid,
                    'period_text' => $periodText,
                    'price_per_unit' => $pricePerUnit,
                    'amount' => $amount,
                    'total_amount' => $totalAmount,
                    'currency' => 'MDL',
                ];
            }

            if ($error === '' && count($rows) > 0) {
                try {
                    $ins = $pdo->prepare('
                        INSERT INTO client_service_requests
                            (user_id, service_id, period_text, price_per_unit, amount, total_amount, currency, status)
                        VALUES
                            (:user_id, :service_id, :period_text, :price_per_unit, :amount, :total_amount, :currency, :status)
                    ');

                    foreach ($rows as $r) {
                        $ins->execute([
                            ':user_id' => $uid,
                            ':service_id' => (int)$r['service_id'],
                            ':period_text' => $r['period_text'],
                            ':price_per_unit' => $r['price_per_unit'],
                            ':amount' => $r['amount'],
                            ':total_amount' => $r['total_amount'],
                            ':currency' => $r['currency'],
                            ':status' => 'pending',
                        ]);
                    }

                    // Compose email
                    $serviceMap = [];
                    foreach ($services as $s) {
                        $serviceMap[(int)$s['id']] = $s;
                    }

                    $lines = [];
                    foreach ($rows as $r) {
                        $sid = (int)$r['service_id'];
                        $s = $serviceMap[$sid] ?? ['name' => 'Serviciu necunoscut', 'code' => ''];
                        $lines[] =
                            "• {$s['name']}" . (!empty($s['code']) ? " ({$s['code']})" : '') .
                            "\n  Perioada: {$r['period_text']}" .
                            "\n  Preț: " . number_format((float)$r['price_per_unit'], 2, '.', ' ') . " {$r['currency']}" .
                            "\n  Suma totală: " . number_format((float)$r['total_amount'], 2, '.', ' ') . " {$r['currency']}";
                    }

                    $body = "Cerere servicii noi primită de pe site (Cabinet client)\r\n\r\n";
                    $body .= "Client:\r\n";
                    $body .= "- ID: {$uid}\r\n";
                    $body .= "- Nume: " . ($client['full_name'] ?? '-') . "\r\n";
                    $body .= "- Email: {$client['email']}\r\n\r\n";
                    $body .= "Detalii cerere:\r\n";
                    $body .= implode("\r\n", $lines);
                    $body .= "\r\n\r\n";
                    $body .= "Status: pending";

                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = 'mail.ondsolutions.md';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'contact@ondsolutions.md';
                    $mail->Password = 'AAD1sup@$$';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->CharSet = 'UTF-8';

                    $mail->setFrom('contact@ondsolutions.md', 'Smart Solutions');
                    $mail->addAddress($adminEmail);
                    $mail->addReplyTo($client['email'], $client['full_name'] ?? '');
                    $mail->Subject = 'Cerere serviciu nou – ' . ($client['full_name'] ?? 'client');
                    $mail->Body = $body;
                    $mail->send();

                    $success = 'Cererea a fost trimisă. Administratorul a primit detaliile pe email.';
                } catch (Throwable $e) {
                    $error = 'Am salvat cererea, dar nu am reușit să trimit emailul: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
                }
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Cabinet Client | Cere serviciu nou</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } };</script>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary-blue text-white font-bold">S</div>
                <div>
                    <div class="font-semibold text-slate-900">Cabinet Client</div>
                    <div class="text-xs text-slate-500"><?php echo client_h($client['email']); ?></div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Dashboard</a>
                <a href="settings.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">Setări</a>
                <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Logout</a>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-6">
        <div class="flex items-start justify-between gap-4 flex-wrap mb-5">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Cere serviciu nou</h1>
                <p class="mt-1 text-sm text-slate-600">Bifează serviciile dorite și alege perioada (1-10 luni). Suma totală se calculează automat.</p>
            </div>
            <div class="text-sm text-slate-500">
                Client ID: <span class="font-mono"><?php echo (int)$uid; ?></span>
            </div>
        </div>

        <?php if ($error): ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <h2 class="font-semibold text-slate-900">Selectează servicii</h2>
                <span class="text-xs text-slate-500">Preț (lei / lună)</span>
            </div>

            <form method="post" class="px-5 py-4">
                <input type="hidden" name="csrf" value="<?php echo client_h(client_csrf_token()); ?>">

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-sm text-left">
                        <thead class="bg-slate-50 border-b border-slate-200">
                            <tr>
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Alege</th>
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Serviciu</th>
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Perioada</th>
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Preț</th>
                                <th class="px-3 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500">Suma totală</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        <?php if (empty($services)): ?>
                            <tr>
                                <td colspan="5" class="px-3 py-10 text-center text-slate-500">
                                    Nu există servicii în catalog.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($services as $s): ?>
                                <tr class="hover:bg-slate-50/80">
                                    <td class="px-3 py-3 align-top">
                                        <input
                                            type="checkbox"
                                            class="service-select"
                                            name="selected_services[]"
                                            value="<?php echo (int)$s['id']; ?>"
                                        >
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <div class="font-medium text-slate-900"><?php echo client_h($s['name']); ?></div>
                                        <?php if (!empty($s['code'])): ?>
                                            <div class="text-xs text-slate-500 mt-1"><?php echo client_h($s['code']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <?php $price = (float)($priceByServiceId[(int)$s['id']] ?? 0.0); ?>
                                        <select
                                            name="period_months[<?php echo (int)$s['id']; ?>]"
                                            class="period-select w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                            disabled
                                        >
                                            <?php for ($m = 1; $m <= 10; $m++): ?>
                                                <option value="<?php echo (int)$m; ?>" <?php echo $m === 3 ? 'selected' : ''; ?>><?php echo (int)$m; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <span class="inline-flex items-center gap-1 text-sm font-medium text-slate-900">
                                            <?php if ($price > 0): ?>
                                                <?php echo number_format($price, 2, ',', ' '); ?> <span class="text-xs text-slate-500">lei / lună</span>
                                            <?php else: ?>
                                                <span class="text-slate-400">n/a</span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="px-3 py-3 align-top">
                                        <input
                                            type="text"
                                            class="total-amount w-full rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm"
                                            value="0,00"
                                            data-price="<?php echo (float)$price; ?>"
                                            disabled
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-5 flex items-center justify-end gap-3 flex-wrap">
                    <a href="dashboard.php" class="rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Anulează
                    </a>
                    <button type="submit" class="rounded-xl bg-primary-blue px-5 py-2.5 text-sm font-semibold text-white hover:opacity-95 transition">
                        OK – Trimite cererea
                    </button>
                </div>
            </form>
        </section>
    </main>

    <script>
        (function () {
            function updateRow(tr) {
                var cb = tr.querySelector('input.service-select[type="checkbox"]');
                var periodSel = tr.querySelector('select.period-select');
                var totalInput = tr.querySelector('input.total-amount');
                if (!periodSel || !totalInput) return;

                var checked = cb ? cb.checked : false;
                periodSel.disabled = !checked;

                var price = parseFloat(totalInput.dataset.price || '0');
                if (!checked) {
                    totalInput.value = '0,00';
                    return;
                }

                var months = parseInt(periodSel.value, 10) || 1;
                if (!isFinite(price) || price <= 0) {
                    totalInput.value = '0,00';
                    return;
                }

                var total = price * months;
                totalInput.value = total.toFixed(2).replace('.', ',');
            }

            document.querySelectorAll('tr').forEach(function (tr) {
                var cb = tr.querySelector('input.service-select[type="checkbox"]');
                if (!cb) return;
                updateRow(tr);
                cb.addEventListener('change', function () { updateRow(tr); });
                var periodSel = tr.querySelector('select.period-select');
                if (periodSel) periodSel.addEventListener('change', function () { updateRow(tr); });
            });
        })();
    </script>
</body>
</html>

