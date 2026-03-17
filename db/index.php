<?php
session_start();

if (!empty($_SESSION['db_admin_logged_in'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username === 'admin' && $password === 'ondsecure2026') {
        $_SESSION['db_admin_logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Utilizator sau parolă incorectă.';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Autentificare Admin | ondsolutions.md</title>
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
        .card { box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.15); }
    </style>
</head>
<body class="min-h-screen bg-slate-100 flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="card rounded-2xl border border-slate-200 bg-white p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-blue text-white font-bold text-lg">S</div>
                <div>
                    <h1 class="text-xl font-semibold text-slate-900">ondsolutions.md</h1>
                    <p class="text-xs text-slate-500">Interfață administrare – baza de date</p>
                </div>
            </div>

            <h2 class="text-base font-semibold text-slate-800 mb-1">Autentificare</h2>
            <p class="text-sm text-slate-500 mb-6">Introdu utilizatorul și parola pentru acces.</p>

            <?php if ($error): ?>
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </div>
            <?php endif; ?>

            <form method="post" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Utilizator</label>
                    <input type="text" name="username" required autocomplete="username"
                           placeholder="admin"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20 transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Parolă</label>
                    <input type="password" name="password" required autocomplete="current-password"
                           placeholder="••••••••"
                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 placeholder-slate-400 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20 transition">
                </div>
                <button type="submit"
                        class="w-full rounded-xl bg-primary-blue py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-blue/25 transition hover:opacity-95 hover:shadow-xl active:scale-[0.99]">
                    Autentificare
                </button>
            </form>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500">Acces restricționat. Doar personal autorizat.</p>
    </div>
</body>
</html>
