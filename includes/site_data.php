<?php
// Încarcă datele dinamice ale site-ului (prețuri, contact, etc.)
$base = dirname(__DIR__);
$data_file = $base . '/data/site_data.json';
if (!is_file($data_file)) {
    $data_file = $base . '/data/site_data.json.example';
}
$site_data = [];
if (is_file($data_file)) {
    $decoded = json_decode(file_get_contents($data_file), true);
    if (is_array($decoded)) {
        $site_data = $decoded;
    }
}
// Valori implicite dacă lipsește ceva
$defaults = [
    '1c' => [
        'base_price' => 330, 'per_user' => 30, 'per_gb_factor' => 0.08,
        'per_instance' => 30, 'min_price' => 150, 'trial_badge' => 'Test 7 zile',
        'migrare_note' => 'Migrare, VPN/RDP și consultanță incluse.'
    ],
    'hosting' => [],
    'vps' => [],
    'domains' => [],
    'contact' => [
        'email_sales' => '', 'email_support' => '', 'phone' => '', 'schedule' => ''
    ],
    'hero' => [ 'badge_1' => '', 'badge_2' => '', 'badge_3' => '' ],
    'company' => [ 'name' => 'Smart Solutions', 'tagline' => 'Hosting 1C • VPS • Domenii', 'footer_text' => '', 'copyright' => 'OND SOLUTIONS SRL' ]
];
foreach ($defaults as $key => $def) {
    if (!isset($site_data[$key])) {
        $site_data[$key] = $def;
    } elseif (is_array($def) && is_array($site_data[$key]) && !empty($def)) {
        $site_data[$key] = array_replace_recursive($def, $site_data[$key]);
    }
}
