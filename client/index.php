<?php
require __DIR__ . '/lib.php';
require __DIR__ . '/config.php';

if (client_is_logged_in()) {
    client_redirect('dashboard.php');
}

$auth_url = 'https://accounts.google.com/o/oauth2/v2/auth';
$state = bin2hex(random_bytes(16));
$_SESSION['oauth_state'] = $state;

$params = [
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'include_granted_scopes' => 'true',
    'state' => $state,
    'prompt' => 'select_account',
];
$loginUrl = $auth_url . '?' . http_build_query($params);
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="../favicon.svg" type="image/svg+xml">
    <title>Cabinet Client | ondsolutions.md</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { 'primary-blue': '#2563eb' }, fontFamily: { sans: ['Inter','system-ui','sans-serif'] } } } };</script>
</head>
<body class="min-h-screen bg-slate-100 font-sans antialiased p-6 flex items-center justify-center">
    <div class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-lg">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-blue text-white font-bold text-lg">S</div>
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Cabinet Client</h1>
                <p class="text-xs text-slate-500">Autentificare cu Google</p>
            </div>
        </div>
        <p class="text-sm text-slate-600 mb-6">Intră în cabinet ca să vezi serviciile active, facturarea și notificările.</p>
        <a href="<?php echo client_h($loginUrl); ?>" class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-primary-blue px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-primary-blue/25 hover:opacity-95">
            <span>Continuă cu Google</span>
        </a>
        <p class="mt-4 text-xs text-slate-500">Dacă primești eroare, verifică în `client/config.php` Client ID/Secret și Redirect URI.</p>
    </div>
</body>
</html>

