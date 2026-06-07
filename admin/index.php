<?php
session_start();
require __DIR__ . '/config.php';

// Logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $pass = $_POST['password'] ?? '';
    if (password_verify($pass, $admin_password_hash)) {
        $_SESSION['admin_logged'] = true;
        header('Location: index.php');
        exit;
    }
    $login_error = 'Parola este incorectă.';
}

// Schimbare parolă (doar dacă ești logat)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password']) && !empty($_SESSION['admin_logged'])) {
    $current = $_POST['current_password'] ?? '';
    $new1 = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password_confirm'] ?? '';
    $pw_error = '';
    if (!password_verify($current, $admin_password_hash)) {
        $pw_error = 'Parola actuală este incorectă.';
    } elseif (strlen($new1) < 8) {
        $pw_error = 'Parola nouă trebuie să aibă minim 8 caractere.';
    } elseif ($new1 !== $new2) {
        $pw_error = 'Parola nouă și confirmarea nu coincid.';
    } else {
        $config_path = __DIR__ . '/config.php';
        $new_hash = password_hash($new1, PASSWORD_DEFAULT);
        $content = file_get_contents($config_path);
        $content = preg_replace(
            '/\$admin_password_hash\s*=\s*[\'"][^\'"]*[\'"]\s*;/',
            '$admin_password_hash = ' . var_export($new_hash, true) . ';',
            $content,
            1
        );
        if ($content && is_writable($config_path) && file_put_contents($config_path, $content) !== false) {
            $_SESSION['admin_password_changed'] = true;
            header('Location: index.php');
            exit;
        }
        $pw_error = 'Nu s-a putut scrie în config.php. Verifică permisiunile sau schimbă parola manual (vezi SCHIMBARE_PAROLA.md).';
    }
}

// Save data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && !empty($_SESSION['admin_logged'])) {
    $d = $_POST['data'] ?? [];
    $out = [
        '1c' => [
            'base_price' => (int)($d['1c']['base_price'] ?? 330),
            'per_user' => (int)($d['1c']['per_user'] ?? 30),
            'per_gb_factor' => (float)str_replace(',', '.', $d['1c']['per_gb_factor'] ?? '0.08'),
            'per_instance' => (int)($d['1c']['per_instance'] ?? 30),
            'min_price' => (int)($d['1c']['min_price'] ?? 150),
            'trial_badge' => trim($d['1c']['trial_badge'] ?? 'Test 7 zile'),
            'migrare_note' => trim($d['1c']['migrare_note'] ?? ''),
        ],
        'hosting' => [],
        'vps' => [],
        'domains' => [],
        'contact' => [
            'email_sales' => trim($d['contact']['email_sales'] ?? ''),
            'email_support' => trim($d['contact']['email_support'] ?? ''),
            'phone' => trim($d['contact']['phone'] ?? ''),
            'schedule' => trim($d['contact']['schedule'] ?? ''),
        ],
        'hero' => [
            'badge_1' => trim($d['hero']['badge_1'] ?? ''),
            'badge_2' => trim($d['hero']['badge_2'] ?? ''),
            'badge_3' => trim($d['hero']['badge_3'] ?? ''),
        ],
        'company' => [
            'name' => trim($d['company']['name'] ?? ''),
            'tagline' => trim($d['company']['tagline'] ?? ''),
            'footer_text' => trim($d['company']['footer_text'] ?? ''),
            'copyright' => trim($d['company']['copyright'] ?? ''),
        ],
    ];
    for ($i = 0; $i < 3; $i++) {
        $h = $d['hosting'][$i] ?? [];
        $features = isset($h['features']) ? (is_array($h['features']) ? $h['features'] : array_filter(array_map('trim', explode("\n", $h['features'])))) : [];
        $out['hosting'][] = [
            'name' => trim($h['name'] ?? ''),
            'subtitle' => trim($h['subtitle'] ?? ''),
            'icon' => trim($h['icon'] ?? 'H'),
            'description' => trim($h['description'] ?? ''),
            'price' => (int)($h['price'] ?? 0),
            'price_alt' => (int)($h['price_alt'] ?? 0),
            'price_note' => trim($h['price_note'] ?? ''),
            'price_note_alt' => trim($h['price_note_alt'] ?? ''),
            'features' => $features,
        ];
    }
    for ($i = 0; $i < 3; $i++) {
        $v = $d['vps'][$i] ?? [];
        $out['vps'][] = [
            'name' => trim($v['name'] ?? ''),
            'specs' => trim($v['specs'] ?? ''),
            'price' => (int)($v['price'] ?? 0),
        ];
    }
    for ($i = 0; $i < 4; $i++) {
        $dom = $d['domains'][$i] ?? [];
        $out['domains'][] = [
            'tld' => trim($dom['tld'] ?? ''),
            'price' => (int)($dom['price'] ?? 0),
        ];
    }
    $json = json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    $can_save = $json !== false && (is_writable($data_file) || (!is_file($data_file) && is_dir($data_dir) && is_writable($data_dir)));
    if (!$can_save && !is_dir($data_dir)) {
        @mkdir($data_dir, 0755, true);
        $can_save = $json !== false && is_dir($data_dir) && is_writable($data_dir);
    }
    if ($can_save && file_put_contents($data_file, $json) !== false) {
        $_SESSION['admin_saved'] = true;
    } else {
        $_SESSION['admin_save_error'] = true;
    }
    header('Location: index.php');
    exit;
}

// Not logged in — show login form
if (empty($_SESSION['admin_logged'])) {
    ?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Autentificare Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 p-8 w-full max-w-sm">
        <h1 class="text-xl font-bold text-slate-800 mb-2">Panel Administrator</h1>
        <p class="text-sm text-slate-500 mb-6">Introdu parola pentru a edita prețurile și datele site-ului.</p>
        <?php if (!empty($login_error)): ?>
        <p class="text-sm text-red-600 mb-4"><?php echo htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="login" value="1">
            <label class="block text-xs font-semibold text-slate-700 mb-1">Parolă</label>
            <input type="password" name="password" required autofocus
                   class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 mb-4">
            <button type="submit" class="w-full rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 text-sm">
                Autentificare
            </button>
        </form>
    </div>
</body>
</html>
    <?php
    exit;
}

// Load current data
$site_data = [];
if (is_file($data_file)) {
    $decoded = json_decode(file_get_contents($data_file), true);
    if (is_array($decoded)) {
        $site_data = $decoded;
    }
}
$c1 = $site_data['1c'] ?? [];
$contact = $site_data['contact'] ?? [];
$hero = $site_data['hero'] ?? [];
$company = $site_data['company'] ?? [];
$hosting = $site_data['hosting'] ?? array_fill(0, 3, []);
$vps = $site_data['vps'] ?? array_fill(0, 3, []);
$domains = $site_data['domains'] ?? array_fill(0, 4, []);
$saved = isset($_SESSION['admin_saved']);
$save_error = isset($_SESSION['admin_save_error']);
$password_changed = isset($_SESSION['admin_password_changed']);
unset($_SESSION['admin_saved'], $_SESSION['admin_save_error'], $_SESSION['admin_password_changed']);
$pw_error = $pw_error ?? '';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Panel Admin — Date dinamice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 text-slate-800 min-h-screen">
    <header class="bg-white border-b border-slate-200 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <div>
                <h1 class="text-lg font-bold text-slate-800">Panel Administrator</h1>
                <p class="text-xs text-slate-500">Modifică prețurile și textele afișate pe site</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="#schimbare-parola" class="text-sm text-slate-600 hover:underline">Schimbă parola</a>
                <a href="../index.php" target="_blank" class="text-sm text-blue-600 hover:underline">Vezi site-ul</a>
                <a href="?logout=1" class="text-sm text-slate-500 hover:text-slate-700">Deconectare</a>
            </div>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <?php if ($password_changed): ?>
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
            Parola a fost schimbată. La următoarea autentificare folosește parola nouă.
        </div>
        <?php endif; ?>
        <?php if ($saved): ?>
        <div class="mb-6 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm">
            Datele au fost salvate cu succes.
        </div>
        <?php endif; ?>
        <?php if ($save_error): ?>
        <div class="mb-6 rounded-xl bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
            Eroare la salvare. Verifică că directorul <code>data/</code> este scriibil.
        </div>
        <?php endif; ?>

        <section id="schimbare-parola" class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm mb-10">
            <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Schimbă parola de administrator</h2>
            <?php if ($pw_error): ?>
            <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-700 px-3 py-2 text-sm"><?php echo htmlspecialchars($pw_error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form method="post" action="" class="space-y-4 max-w-md">
                <input type="hidden" name="change_password" value="1">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Parola actuală</label>
                    <input type="password" name="current_password" required autocomplete="current-password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Parola nouă (min. 8 caractere)</label>
                    <input type="password" name="new_password" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Confirmă parola nouă</label>
                    <input type="password" name="new_password_confirm" required minlength="8" autocomplete="new-password" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <button type="submit" class="rounded-lg bg-slate-700 hover:bg-slate-800 text-white font-semibold px-4 py-2 text-sm">Salvează parola nouă</button>
            </form>
        </section>

        <form method="post" action="" class="space-y-10">
            <input type="hidden" name="save" value="1">

            <!-- 1C -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Calculator 1C (preț estimat)</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Preț bază (lei)</label>
                        <input type="number" name="data[1c][base_price]" value="<?php echo (int)($c1['base_price'] ?? 330); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Per utilizator (lei)</label>
                        <input type="number" name="data[1c][per_user]" value="<?php echo (int)($c1['per_user'] ?? 30); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Factor per GB (ex: 0.08)</label>
                        <input type="text" name="data[1c][per_gb_factor]" value="<?php echo htmlspecialchars($c1['per_gb_factor'] ?? '0.08', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Per instanță/bază (lei)</label>
                        <input type="number" name="data[1c][per_instance]" value="<?php echo (int)($c1['per_instance'] ?? 30); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Preț minim (lei)</label>
                        <input type="number" name="data[1c][min_price]" value="<?php echo (int)($c1['min_price'] ?? 150); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Badge trial (ex: Test 7 zile)</label>
                        <input type="text" name="data[1c][trial_badge]" value="<?php echo htmlspecialchars($c1['trial_badge'] ?? 'Test 7 zile', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Notă migrare (sub calculator)</label>
                        <input type="text" name="data[1c][migrare_note]" value="<?php echo htmlspecialchars($c1['migrare_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                </div>
            </section>

            <!-- Hosting plans -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Pachete găzduire web (3 planuri)</h2>
                <?php for ($i = 0; $i < 3; $i++): $h = $hosting[$i] ?? []; $feat = $h['features'] ?? []; $featStr = is_array($feat) ? implode("\n", $feat) : (string)$feat; ?>
                <div class="mb-8 last:mb-0 pb-8 last:pb-0 border-b border-slate-100 last:border-0">
                    <h3 class="text-sm font-semibold text-slate-700 mb-3">Plan <?php echo $i + 1; ?></h3>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Nume</label><input type="text" name="data[hosting][<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($h['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Subtitle</label><input type="text" name="data[hosting][<?php echo $i; ?>][subtitle]" value="<?php echo htmlspecialchars($h['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Icon (o literă)</label><input type="text" name="data[hosting][<?php echo $i; ?>][icon]" value="<?php echo htmlspecialchars($h['icon'] ?? 'H', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" maxlength="1"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Preț (lei/lună)</label><input type="number" name="data[hosting][<?php echo $i; ?>][price]" value="<?php echo (int)($h['price'] ?? 0); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Preț alternativ (lei, opțional)</label><input type="number" name="data[hosting][<?php echo $i; ?>][price_alt]" value="<?php echo (int)($h['price_alt'] ?? 0); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div><label class="block text-xs font-semibold text-slate-600 mb-1">Notă preț</label><input type="text" name="data[hosting][<?php echo $i; ?>][price_note]" value="<?php echo htmlspecialchars($h['price_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div class="sm:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1">Notă preț alternativă</label><input type="text" name="data[hosting][<?php echo $i; ?>][price_note_alt]" value="<?php echo htmlspecialchars($h['price_note_alt'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div class="sm:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1">Descriere</label><input type="text" name="data[hosting][<?php echo $i; ?>][description]" value="<?php echo htmlspecialchars($h['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                        <div class="sm:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1">Caracteristici (câte una per linie)</label><textarea name="data[hosting][<?php echo $i; ?>][features]" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><?php echo htmlspecialchars($featStr, ENT_QUOTES, 'UTF-8'); ?></textarea></div>
                    </div>
                </div>
                <?php endfor; ?>
            </section>

            <!-- VPS -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Configurații VPS (3 oferte)</h2>
                <?php for ($i = 0; $i < 3; $i++): $v = $vps[$i] ?? []; ?>
                <div class="mb-6 last:mb-0 flex flex-wrap gap-4 items-end">
                    <div class="flex-1 min-w-[120px]"><label class="block text-xs font-semibold text-slate-600 mb-1">Nume</label><input type="text" name="data[vps][<?php echo $i; ?>][name]" value="<?php echo htmlspecialchars($v['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div class="flex-1 min-w-[180px]"><label class="block text-xs font-semibold text-slate-600 mb-1">Specificații</label><input type="text" name="data[vps][<?php echo $i; ?>][specs]" value="<?php echo htmlspecialchars($v['specs'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="ex: 2 vCPU, 4 GB RAM"></div>
                    <div class="w-24"><label class="block text-xs font-semibold text-slate-600 mb-1">Preț (lei)</label><input type="number" name="data[vps][<?php echo $i; ?>][price]" value="<?php echo (int)($v['price'] ?? 0); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
                <?php endfor; ?>
            </section>

            <!-- Domains -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Domenii (preț/an)</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <?php for ($i = 0; $i < 4; $i++): $d = $domains[$i] ?? []; ?>
                    <div class="flex gap-2">
                        <input type="text" name="data[domains][<?php echo $i; ?>][tld]" value="<?php echo htmlspecialchars($d['tld'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder=".ro" class="flex-1 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <input type="number" name="data[domains][<?php echo $i; ?>][price]" value="<?php echo (int)($d['price'] ?? 0); ?>" placeholder="45" class="w-20 rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                    <?php endfor; ?>
                </div>
            </section>

            <!-- Contact -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Contact</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Email vânzări</label><input type="text" name="data[contact][email_sales]" value="<?php echo htmlspecialchars($contact['email_sales'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Email suport</label><input type="text" name="data[contact][email_support]" value="<?php echo htmlspecialchars($contact['email_support'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Telefon</label><input type="text" name="data[contact][phone]" value="<?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Program</label><input type="text" name="data[contact][schedule]" value="<?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
            </section>

            <!-- Hero badges -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Badge-uri hero (3)</h2>
                <div class="grid gap-4 sm:grid-cols-3">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Badge 1</label><input type="text" name="data[hero][badge_1]" value="<?php echo htmlspecialchars($hero['badge_1'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Badge 2</label><input type="text" name="data[hero][badge_2]" value="<?php echo htmlspecialchars($hero['badge_2'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Badge 3</label><input type="text" name="data[hero][badge_3]" value="<?php echo htmlspecialchars($hero['badge_3'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
            </section>

            <!-- Company -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
                <h2 class="text-base font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Companie (header & footer)</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Nume companie</label><input type="text" name="data[company][name]" value="<?php echo htmlspecialchars($company['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Tagline (sub logo)</label><input type="text" name="data[company][tagline]" value="<?php echo htmlspecialchars($company['tagline'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                    <div class="sm:col-span-2"><label class="block text-xs font-semibold text-slate-600 mb-1">Text footer</label><textarea name="data[company][footer_text]" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><?php echo htmlspecialchars($company['footer_text'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea></div>
                    <div><label class="block text-xs font-semibold text-slate-600 mb-1">Copyright (ex: OND SOLUTIONS SRL)</label><input type="text" name="data[company][copyright]" value="<?php echo htmlspecialchars($company['copyright'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></div>
                </div>
            </section>

            <div class="flex justify-end">
                <button type="submit" class="rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 text-sm shadow-lg">
                    Salvează modificările
                </button>
            </div>
        </form>
    </main>
</body>
</html>
