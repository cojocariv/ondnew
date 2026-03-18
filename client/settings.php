<?php
require __DIR__ . '/lib.php';
if (!client_is_logged_in()) client_redirect('index.php');
$pdo = client_require_db();
$uid = (int)$_SESSION['client_user_id'];

$saved = false;
$error = '';

// load current
$stmt = $pdo->prepare("SELECT email_billing, email_service, email_marketing FROM client_notification_prefs WHERE user_id = ?");
$stmt->execute([$uid]);
$prefs = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$prefs) {
    $pdo->prepare("INSERT INTO client_notification_prefs (user_id) VALUES (?) ON CONFLICT (user_id) DO NOTHING")->execute([$uid]);
    $prefs = ['email_billing'=>true,'email_service'=>true,'email_marketing'=>false];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf'] ?? '';
    if (!client_check_csrf($token)) {
        $error = 'Sesiune expirată. Reîncearcă.';
    } else {
        $email_billing = !empty($_POST['email_billing']);
        $email_service = !empty($_POST['email_service']);
        $email_marketing = !empty($_POST['email_marketing']);
        try {
            $stmt = $pdo->prepare("UPDATE client_notification_prefs SET email_billing=?, email_service=?, email_marketing=?, updated_at=CURRENT_TIMESTAMP WHERE user_id=?");
            $stmt->execute([$email_billing, $email_service, $email_marketing, $uid]);
            $prefs = ['email_billing'=>$email_billing,'email_service'=>$email_service,'email_marketing'=>$email_marketing];
            $saved = true;
        } catch (Throwable $e) {
            $error = 'Nu s-au putut salva setările.';
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
    <title>Cabinet Client | Setări</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased text-slate-800">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-3xl px-4 py-4 flex items-center justify-between">
            <a href="dashboard.php" class="text-sm font-medium text-slate-600 hover:text-primary-blue">← Înapoi</a>
            <a href="logout.php" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Logout</a>
        </div>
    </header>

    <main class="mx-auto max-w-3xl px-4 py-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="text-xl font-semibold text-slate-900">Setări notificări</h1>
            <p class="mt-1 text-sm text-slate-600">Alege ce emailuri vrei să primești.</p>

            <?php if ($saved): ?>
                <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">Setările au fost salvate.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><?php echo client_h($error); ?></div>
            <?php endif; ?>

            <form method="post" class="mt-6 space-y-4">
                <input type="hidden" name="csrf" value="<?php echo client_h(client_csrf_token()); ?>">
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                    <input type="checkbox" name="email_billing" class="mt-1" <?php echo !empty($prefs['email_billing']) ? 'checked' : ''; ?>>
                    <span>
                        <span class="font-semibold text-slate-900">Facturare</span>
                        <span class="block text-sm text-slate-600">Notificări despre facturi, scadențe și datorii.</span>
                    </span>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                    <input type="checkbox" name="email_service" class="mt-1" <?php echo !empty($prefs['email_service']) ? 'checked' : ''; ?>>
                    <span>
                        <span class="font-semibold text-slate-900">Servicii</span>
                        <span class="block text-sm text-slate-600">Notificări despre activare, suspendare, mentenanță.</span>
                    </span>
                </label>
                <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 hover:bg-slate-50">
                    <input type="checkbox" name="email_marketing" class="mt-1" <?php echo !empty($prefs['email_marketing']) ? 'checked' : ''; ?>>
                    <span>
                        <span class="font-semibold text-slate-900">Noutăți</span>
                        <span class="block text-sm text-slate-600">Oferte și update-uri (opțional).</span>
                    </span>
                </label>
                <button type="submit" class="w-full rounded-xl bg-primary-blue py-3 text-sm font-semibold text-white shadow-lg shadow-primary-blue/25 hover:opacity-95">Salvează</button>
            </form>
        </div>
    </main>
</body>
</html>

