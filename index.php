<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/includes/site_data.php';
require __DIR__ . '/includes/i18n.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$mail_config_file = __DIR__ . '/includes/mail_config.php';
if (!is_file($mail_config_file)) {
    $mail_config_file = __DIR__ . '/includes/mail_config.example.php';
}
$mail_config = require $mail_config_file;
if (!is_array($mail_config)) {
    $mail_config = [];
}

$contact_success = '';
$contact_error   = '';
$nume = '';
$email = '';
$mesaj = '';

if (!empty($_SESSION['contact_flash'])) {
    $contact_flash = $_SESSION['contact_flash'];
    unset($_SESSION['contact_flash']);
    if (($contact_flash['type'] ?? '') === 'success') {
        $contact_success = $contact_flash['message'] ?? '';
    } elseif (($contact_flash['type'] ?? '') === 'error') {
        $contact_error = $contact_flash['message'] ?? '';
        $nume = $contact_flash['nume'] ?? '';
        $email = $contact_flash['email'] ?? '';
        $mesaj = $contact_flash['mesaj'] ?? '';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nume'], $_POST['email'], $_POST['mesaj'])) {
    $nume   = trim($_POST['nume']);
    $email  = trim($_POST['email']);
    $mesaj  = trim($_POST['mesaj']);
    $flash  = ['type' => 'error', 'message' => '', 'nume' => $nume, 'email' => $email, 'mesaj' => $mesaj];

    if ($nume === '' || $email === '' || $mesaj === '') {
        $flash['message'] = t('contact_error_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $flash['message'] = t('contact_error_invalid_email');
    } elseif (trim($mail_config['password'] ?? '') === '') {
        $flash['message'] = t('contact_error_send_prefix') . t('contact_error_smtp_not_configured');
    } else {
        $mail = new PHPMailer(true);
        try {
            $smtp_host = trim($mail_config['host'] ?? 'mail.smartsolutions.md');
            $smtp_port = (int) ($mail_config['port'] ?? 465);
            $smtp_user = trim($mail_config['username'] ?? 'contact@smartsolutions.md');
            $smtp_from = trim($mail_config['from_email'] ?? $smtp_user);
            $smtp_name = trim($mail_config['from_name'] ?? 'Smart Solutions');

            $mail->isSMTP();
            $mail->Host       = $smtp_host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $smtp_user;
            $mail->Password   = $mail_config['password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $smtp_port > 0 ? $smtp_port : 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom($smtp_from, $smtp_name);
            $contact_recipient = trim($site_data['contact']['email_support'] ?? '');
            if ($contact_recipient === '' || !filter_var($contact_recipient, FILTER_VALIDATE_EMAIL)) {
                $contact_recipient = 'contact@smartsolutions.md';
            }
            $mail->addAddress($contact_recipient);
            $mail->addReplyTo($email, $nume);
            $mail->Subject = 'Mesaj nou de pe formularul Smart Solutions';
            $body  = "Ai primit un mesaj nou de pe site.\r\n\r\n";
            $body .= "Nume: {$nume}\r\n";
            $body .= "Email: {$email}\r\n\r\n";
            $body .= "Mesaj:\r\n{$mesaj}\r\n";
            $mail->Body = $body;
            $mail->send();
            $flash = ['type' => 'success', 'message' => t('contact_success')];
        } catch (Exception $e) {
            $flash = [
                'type' => 'error',
                'message' => t('contact_error_send_prefix') . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'),
                'nume' => $nume,
                'email' => $email,
                'mesaj' => $mesaj,
            ];
        }
    }

    $_SESSION['contact_flash'] = $flash;
    $redirect_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $redirect_query = $lang !== 'ro' ? '?lang=' . urlencode($lang) : '';
    header('Location: ' . $redirect_path . $redirect_query . '#contact', true, 303);
    exit;
}

$company_seo = $site_data['company'] ?? [];
$contact_seo = $site_data['contact'] ?? [];
$chat_whatsapp_raw = trim($contact_seo['whatsapp'] ?? '');
if ($chat_whatsapp_raw === '') {
    $chat_whatsapp_raw = trim($contact_seo['phone'] ?? '');
}
$chat_viber_raw = trim($contact_seo['viber'] ?? '');
if ($chat_viber_raw === '') {
    $chat_viber_raw = trim($contact_seo['phone'] ?? '');
}
$chat_phone_digits = preg_replace('/\D+/', '', $chat_whatsapp_raw);
$chat_viber_digits = preg_replace('/\D+/', '', $chat_viber_raw);
$chat_whatsapp_url = $chat_phone_digits
    ? 'https://wa.me/' . $chat_phone_digits . '?text=' . rawurlencode(t('chat_whatsapp_message'))
    : '';
$chat_viber_url = $chat_viber_digits
    ? 'viber://chat?number=' . rawurlencode('+' . $chat_viber_digits)
    : '';
$chat_enabled = $chat_whatsapp_url !== '' || $chat_viber_url !== '';
$company_name = $company_seo['name'] ?? 'Smart Solutions';
$seo_title = t('seo_title', $company_name);
$seo_description = t('seo_description', $company_name);
$seo_keywords = t('seo_keywords');
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'smartsolutions.md';
$site_base = $scheme . '://' . preg_replace('/[^a-zA-Z0-9.\-:]/', '', $host);
$canonical_url = rtrim($site_base, '/') . '/' . ($lang !== 'ro' ? '?lang=' . urlencode($lang) : '');
$hreflang_urls = [
    'ro' => rtrim($site_base, '/') . '/',
    'en' => rtrim($site_base, '/') . '/?lang=en',
    'ru' => rtrim($site_base, '/') . '/?lang=ru',
];
$og_image = rtrim($site_base, '/') . '/assets/LOGO/transparent%20logo.png';
$hero_video_url = 'https://cojocaristorage.blob.core.windows.net/smartsolutions/business-web.mp4';
$json_ld = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type' => 'Organization',
            '@id' => $canonical_url . '#organization',
            'name' => $company_name,
            'url' => $canonical_url,
            'logo' => rtrim($site_base, '/') . '/assets/LOGO/transparent%20logo.png',
            'description' => $company_seo['footer_text'] ?? $seo_description,
            'email' => $contact_seo['email_sales'] ?? 'sales@smartsolutions.md',
            'telephone' => $contact_seo['phone'] ?? '',
        ],
        [
            '@type' => 'WebSite',
            '@id' => $canonical_url . '#website',
            'url' => $canonical_url,
            'name' => $company_name,
            'inLanguage' => lang_schema(),
            'publisher' => ['@id' => $canonical_url . '#organization'],
        ],
        [
            '@type' => 'ProfessionalService',
            '@id' => $canonical_url . '#service',
            'name' => $company_name,
            'url' => $canonical_url,
            'description' => $seo_description,
            'areaServed' => ['MD', 'RO'],
            'serviceType' => $L['json_ld_service_types'] ?? [],
        ],
    ],
];

$services = [
    ['icon' => 'code', 'title' => 'service_web_title', 'desc' => 'service_web_desc'],
    ['icon' => 'cloud', 'title' => 'service_azure_title', 'desc' => 'service_azure_desc'],
    ['icon' => 'shield', 'title' => 'service_backup_title', 'desc' => 'service_backup_desc'],
    ['icon' => 'mail', 'title' => 'service_m365_title', 'desc' => 'service_m365_desc'],
    ['icon' => 'database', 'title' => 'service_1c_title', 'desc' => 'service_1c_desc'],
    ['icon' => 'zap', 'title' => 'service_automation_title', 'desc' => 'service_automation_desc'],
    ['icon' => 'server', 'title' => 'service_managed_title', 'desc' => 'service_managed_desc'],
    ['icon' => 'gauge', 'title' => 'service_perf_title', 'desc' => 'service_perf_desc'],
];

$hosting_1c_features = [
    ['title' => 'hosting_1c_card_migrare_title', 'desc' => 'hosting_1c_card_migrare_desc'],
    ['title' => 'hosting_1c_card_acces_title', 'desc' => 'hosting_1c_card_acces_desc'],
    ['title' => 'hosting_1c_card_backup_title', 'desc' => 'hosting_1c_card_backup_desc'],
    ['title' => 'hosting_1c_card_scalare_title', 'desc' => 'hosting_1c_card_scalare_desc'],
];
$hosting_1c_scenarios = [
    ['id' => 'firma-mica', 'dot' => 'primary', 'title' => 'hosting_1c_scenario_firma_title', 'desc' => 'hosting_1c_scenario_firma_desc'],
    ['id' => 'grup', 'dot' => 'accent', 'title' => 'hosting_1c_scenario_grup_title', 'desc' => 'hosting_1c_scenario_grup_desc'],
    ['id' => 'crm', 'dot' => 'green', 'title' => 'hosting_1c_scenario_crm_title', 'desc' => 'hosting_1c_scenario_crm_desc'],
];
$scenario_details = [
    'firma-mica' => [
        'title' => t('scenario_firma_mica_title'),
        'dotClass' => 'hosting-1c-scenario-dot--primary',
        'lead' => t('scenario_firma_mica_lead'),
        'bullets' => [t('scenario_firma_mica_b1'), t('scenario_firma_mica_b2'), t('scenario_firma_mica_b3'), t('scenario_firma_mica_b4'), t('scenario_firma_mica_b5')],
    ],
    'grup' => [
        'title' => t('scenario_grup_title'),
        'dotClass' => 'hosting-1c-scenario-dot--accent',
        'lead' => t('scenario_grup_lead'),
        'bullets' => [t('scenario_grup_b1'), t('scenario_grup_b2'), t('scenario_grup_b3'), t('scenario_grup_b4'), t('scenario_grup_b5')],
    ],
    'crm' => [
        'title' => t('scenario_crm_title'),
        'dotClass' => 'hosting-1c-scenario-dot--green',
        'lead' => t('scenario_crm_lead'),
        'bullets' => [t('scenario_crm_b1'), t('scenario_crm_b2'), t('scenario_crm_b3'), t('scenario_crm_b4'), t('scenario_crm_b5')],
    ],
];

$dev_cards = [
    ['title' => 'section_dev_card_landing_title', 'desc' => 'section_dev_card_landing_desc'],
    ['title' => 'section_dev_card_apps_title', 'desc' => 'section_dev_card_apps_desc'],
    ['title' => 'section_dev_card_shop_title', 'desc' => 'section_dev_card_shop_desc'],
    ['title' => 'section_dev_card_visit_title', 'desc' => 'section_dev_card_visit_desc'],
];

$dev_portfolio = [
    [
        'url' => 'https://alinabradu.com',
        'logo' => 'assets/portfolio/alinabradu.png',
        'alt' => 'Alina Bradu',
    ],
    [
        'url' => 'https://ecoschimb.md',
        'logo' => 'assets/portfolio/ecoschimb.png',
        'alt' => 'EcoSchimb',
        'label' => 'ecoschimb.md',
        'desc' => 'section_dev_portfolio_ecoschimb_desc',
    ],
];

$process_steps = [
    ['title' => 'process_1_title', 'desc' => 'process_1_desc'],
    ['title' => 'process_2_title', 'desc' => 'process_2_desc'],
    ['title' => 'process_3_title', 'desc' => 'process_3_desc'],
    ['title' => 'process_4_title', 'desc' => 'process_4_desc'],
    ['title' => 'process_5_title', 'desc' => 'process_5_desc'],
    ['title' => 'process_6_title', 'desc' => 'process_6_desc'],
];

$cases = [
    ['tag' => 'case_1_tag', 'title' => 'case_1_title', 'desc' => 'case_1_desc', 'link' => 'case_1_link', 'url' => 'https://alinabradu.com', 'image' => 'assets/code-testing-.jpg', 'external' => true],
    ['tag' => 'case_2_tag', 'title' => 'case_2_title', 'desc' => 'case_2_desc', 'link' => 'case_2_link', 'url' => '#contact', 'image' => 'assets/business-man.jpg', 'external' => false],
    ['tag' => 'case_3_tag', 'title' => 'case_3_title', 'desc' => 'case_3_desc', 'link' => 'case_3_link', 'url' => '#contact', 'image' => 'assets/ai-nuclear-energy.jpg', 'external' => false],
];

$stats = [
    ['value' => 'trust_stat_1_value', 'suffix' => 'trust_stat_1_suffix', 'label' => 'trust_stat_1_label', 'decimals' => 0],
    ['value' => 'trust_stat_2_value', 'suffix' => 'trust_stat_2_suffix', 'label' => 'trust_stat_2_label', 'decimals' => 0],
    ['value' => 'trust_stat_3_value', 'suffix' => 'trust_stat_3_suffix', 'label' => 'trust_stat_3_label', 'decimals' => 1],
    ['value' => 'trust_stat_4_value', 'suffix' => 'trust_stat_4_suffix', 'label' => 'trust_stat_4_label', 'decimals' => 0],
];

function service_icon(string $name): string {
    $icons = [
        'code' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 18l6-6-6-6M8 6l-6 6 6 6"/></svg>',
        'cloud' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 10h-1.26A8 8 0 109 20h9a5 5 0 000-10z"/></svg>',
        'shield' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'mail' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 7l-10 7L2 7"/></svg>',
        'database' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>',
        'zap' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>',
        'server' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="8" rx="2"/><rect x="2" y="14" width="20" height="8" rx="2"/><path d="M6 6h.01M6 18h.01"/></svg>',
        'gauge' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/><circle cx="12" cy="12" r="4"/></svg>',
    ];
    return $icons[$name] ?? $icons['code'];
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars(lang_html(), ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($seo_keywords, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="author" content="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="ro" href="<?php echo htmlspecialchars($hreflang_urls['ro'], ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="en" href="<?php echo htmlspecialchars($hreflang_urls['en'], ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="ru" href="<?php echo htmlspecialchars($hreflang_urls['ru'], ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo htmlspecialchars($hreflang_urls['ro'], ENT_QUOTES, 'UTF-8'); ?>">
    <link rel="icon" href="assets/LOGO/favicon.png" type="image/png">
    <link rel="apple-touch-icon" href="assets/LOGO/favicon.png">
    <meta name="theme-color" content="#185649">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="<?php echo htmlspecialchars(lang_og_locale(), ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:site_name" content="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($seo_title, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($seo_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($og_image, ENT_QUOTES, 'UTF-8'); ?>">
    <script type="application/ld+json"><?php echo json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/site.css">
</head>
<body>

<header id="site-header" class="site-header is-dark">
    <div class="container header-inner">
        <a href="#top" aria-label="<?php echo htmlspecialchars(t('nav_aria_home', $company_name), ENT_QUOTES, 'UTF-8'); ?>">
            <img src="assets/LOGO/transparent%20logo.png" alt="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>" class="site-logo" width="216" height="48">
        </a>
        <nav class="nav-desktop" aria-label="<?php echo htmlspecialchars(t('nav_aria_main'), ENT_QUOTES, 'UTF-8'); ?>">
            <a href="#services"><?php echo htmlspecialchars(t('nav_services'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#hosting-1c"><?php echo htmlspecialchars(t('nav_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#cloud"><?php echo htmlspecialchars(t('nav_cloud'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#dezvoltare"><?php echo htmlspecialchars(t('nav_dezvoltare'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#process"><?php echo htmlspecialchars(t('nav_process'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#cases"><?php echo htmlspecialchars(t('nav_cases'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#contact"><?php echo htmlspecialchars(t('nav_contact'), ENT_QUOTES, 'UTF-8'); ?></a>
        </nav>
        <div class="header-actions">
            <div class="lang-switcher" role="navigation" aria-label="Language">
                <?php foreach (['ro' => 'RO', 'en' => 'EN', 'ru' => 'RU'] as $code => $label) : ?>
                <a href="<?php echo htmlspecialchars(lang_url($code), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $lang === $code ? 'is-active' : ''; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <a href="#contact" class="btn btn-primary header-cta"><?php echo htmlspecialchars(t('nav_cta_primary'), ENT_QUOTES, 'UTF-8'); ?></a>
            <button id="mobile-menu-btn" type="button" class="menu-toggle" aria-expanded="false" aria-controls="mobile-nav"
                data-open-label="<?php echo htmlspecialchars(t('nav_menu_open'), ENT_QUOTES, 'UTF-8'); ?>"
                data-close-label="<?php echo htmlspecialchars(t('nav_menu_close'), ENT_QUOTES, 'UTF-8'); ?>"
                aria-label="<?php echo htmlspecialchars(t('nav_menu_open'), ENT_QUOTES, 'UTF-8'); ?>">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
            </button>
        </div>
    </div>
    <nav id="mobile-nav" class="mobile-nav" aria-label="<?php echo htmlspecialchars(t('nav_aria_main'), ENT_QUOTES, 'UTF-8'); ?>" hidden>
        <a href="#services" data-mobile-nav><?php echo htmlspecialchars(t('nav_services'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#hosting-1c" data-mobile-nav><?php echo htmlspecialchars(t('nav_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#cloud" data-mobile-nav><?php echo htmlspecialchars(t('nav_cloud'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#dezvoltare" data-mobile-nav><?php echo htmlspecialchars(t('nav_dezvoltare'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#process" data-mobile-nav><?php echo htmlspecialchars(t('nav_process'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#cases" data-mobile-nav><?php echo htmlspecialchars(t('nav_cases'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#contact" data-mobile-nav><?php echo htmlspecialchars(t('nav_contact'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#contact" class="btn btn-primary" style="margin-top:1rem;text-align:center;" data-mobile-nav><?php echo htmlspecialchars(t('nav_cta_primary'), ENT_QUOTES, 'UTF-8'); ?></a>
    </nav>
</header>

<main id="top">

    <!-- HERO -->
    <section class="hero" aria-labelledby="hero-title">
        <div class="hero-bg" aria-hidden="true">
            <video autoplay muted loop playsinline preload="metadata" poster="assets/woman-typing-laptop.png">
                <source src="<?php echo htmlspecialchars($hero_video_url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
            </video>
        </div>
        <div class="hero-orb hero-orb--1" aria-hidden="true"></div>
        <div class="hero-orb hero-orb--2" aria-hidden="true"></div>
        <div class="hero-grid" aria-hidden="true"></div>
        <div class="container" style="position:relative;z-index:2;display:flex;align-items:center;min-height:calc(100vh - var(--header-h));min-height:calc(100dvh - var(--header-h));">
            <div class="hero-content reveal is-visible">
                <span class="kicker kicker--light"><?php echo htmlspecialchars(t('hero_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h1 id="hero-title" class="display-xl hero-title"><?php echo t('hero_title'); ?></h1>
                <p class="hero-subtitle"><?php echo htmlspecialchars(t('hero_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="hero-actions">
                    <a href="#contact" class="btn btn-primary"><?php echo htmlspecialchars(t('hero_cta_primary'), ENT_QUOTES, 'UTF-8'); ?></a>
                    <a href="#services" class="btn btn-outline-light"><?php echo htmlspecialchars(t('hero_cta_secondary'), ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
                <button type="button" class="hero-scroll" data-scroll-hint style="background:none;border:none;cursor:pointer;">
                    <?php echo htmlspecialchars(t('hero_scroll'), ENT_QUOTES, 'UTF-8'); ?>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
                </button>
            </div>
            <div class="hero-visual" aria-hidden="true">
                <svg class="hero-network" viewBox="0 0 400 400" fill="none">
                    <circle cx="200" cy="200" r="120" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                    <circle cx="200" cy="200" r="80" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                    <circle cx="200" cy="200" r="40" stroke="rgba(255,89,56,0.4)" stroke-width="2"/>
                    <circle cx="200" cy="80" r="12" fill="#FF5938"/><line x1="200" y1="92" x2="200" y2="160" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                    <circle cx="320" cy="200" r="10" fill="rgba(255,255,255,0.6)"/><line x1="308" y1="200" x2="240" y2="200" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                    <circle cx="200" cy="320" r="10" fill="rgba(255,255,255,0.6)"/><line x1="200" y1="308" x2="200" y2="240" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                    <circle cx="80" cy="200" r="10" fill="rgba(255,255,255,0.6)"/><line x1="92" y1="200" x2="160" y2="200" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                    <circle cx="285" cy="115" r="8" fill="rgba(255,255,255,0.4)"/><line x1="275" y1="125" x2="220" y2="175" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
                    <circle cx="115" cy="285" r="8" fill="rgba(255,255,255,0.4)"/><line x1="125" y1="275" x2="180" y2="225" stroke="rgba(255,255,255,0.2)" stroke-width="1"/>
                    <circle cx="285" cy="285" r="8" fill="rgba(255,255,255,0.4)"/>
                    <circle cx="115" cy="115" r="8" fill="rgba(255,255,255,0.4)"/>
                </svg>
            </div>
        </div>
    </section>

    <!-- TRUST -->
    <section id="trust" class="section section--muted">
        <div class="container">
            <div class="section-header section-header--center reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('trust_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('trust_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="stats-grid">
                <?php foreach ($stats as $i => $stat) : ?>
                <div class="stat-card reveal reveal-delay-<?php echo min($i + 1, 4); ?>">
                    <div class="stat-value">
                        <span data-counter="<?php echo htmlspecialchars(t($stat['value']), ENT_QUOTES, 'UTF-8'); ?>" data-suffix="<?php echo htmlspecialchars(t($stat['suffix']), ENT_QUOTES, 'UTF-8'); ?>" data-decimals="<?php echo $stat['decimals']; ?>">0</span>
                    </div>
                    <div class="stat-label"><?php echo htmlspecialchars(t($stat['label']), ENT_QUOTES, 'UTF-8'); ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="trust-logos reveal">
                <span class="trust-logo">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    Microsoft Azure
                </span>
                <span class="trust-logo">
                    <svg viewBox="0 0 24 24" fill="currentColor"><rect x="3" y="3" width="8" height="8"/><rect x="13" y="3" width="8" height="8"/><rect x="3" y="13" width="8" height="8"/><rect x="13" y="13" width="8" height="8"/></svg>
                    Microsoft 365
                </span>
                <span class="trust-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    Zapier
                </span>
                <span class="trust-logo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"/></svg>
                    1C Platform
                </span>
            </div>
        </div>
    </section>

    <!-- SERVICES -->
    <section id="services" class="section">
        <div class="container">
            <div class="section-header reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('services_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('services_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="section-intro"><?php echo htmlspecialchars(t('services_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="services-grid">
                <?php foreach ($services as $i => $svc) : ?>
                <article class="service-card reveal reveal-delay-<?php echo ($i % 4) + 1; ?>">
                    <div class="service-icon"><?php echo service_icon($svc['icon']); ?></div>
                    <h3><?php echo htmlspecialchars(t($svc['title']), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p><?php echo htmlspecialchars(t($svc['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- HOSTING 1C -->
    <section id="hosting-1c" class="section section--muted hosting-1c" aria-labelledby="hosting-1c-title">
        <div class="hosting-1c-bg" aria-hidden="true">
            <img src="assets/woman-typing-laptop.png" alt="" loading="lazy" width="1920" height="1080">
        </div>
        <div class="container">
            <div class="hosting-1c-hero-row">
            <div class="reveal">
                <?php $h1c = $site_data['hero'] ?? []; ?>
                <span class="kicker"><?php echo htmlspecialchars(t('hosting_1c_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 id="hosting-1c-title" class="display-lg" style="margin-bottom:1rem;"><?php echo t('hosting_1c_title'); ?></h2>
                <p class="section-intro" style="margin-top:0;"><?php echo htmlspecialchars(t('hosting_1c_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="hosting-1c-badges">
                    <span class="hosting-1c-badge hosting-1c-badge--uptime">
                        <span class="hosting-1c-badge-dot hosting-1c-badge-dot--green"></span>
                        <?php echo htmlspecialchars($h1c['badge_1'] ?? t('hosting_1c_badge_uptime'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="hosting-1c-badge hosting-1c-badge--migrare">
                        <span class="hosting-1c-badge-dot hosting-1c-badge-dot--primary"></span>
                        <?php echo htmlspecialchars($h1c['badge_2'] ?? t('hosting_1c_badge_migrare'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="hosting-1c-badge hosting-1c-badge--rdp">
                        <span class="hosting-1c-badge-dot hosting-1c-badge-dot--accent"></span>
                        <?php echo htmlspecialchars($h1c['badge_3'] ?? t('hosting_1c_badge_rdp'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>
                <div class="hosting-1c-actions">
                    <a href="#hosting-1c-details" class="btn btn-primary"><?php echo htmlspecialchars(t('hosting_1c_btn_details'), ENT_QUOTES, 'UTF-8'); ?></a>
                    <a href="#contact" class="btn btn-outline"><?php echo htmlspecialchars(t('hosting_1c_btn_quote'), ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
                <dl class="hosting-1c-stats">
                    <div class="hosting-1c-stat">
                        <dt><?php echo htmlspecialchars(t('hosting_1c_stat_experienta_label'), ENT_QUOTES, 'UTF-8'); ?></dt>
                        <dd><?php echo htmlspecialchars(t('hosting_1c_stat_experienta_value'), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="hosting-1c-stat">
                        <dt><?php echo htmlspecialchars(t('hosting_1c_stat_suport_label'), ENT_QUOTES, 'UTF-8'); ?></dt>
                        <dd><?php echo htmlspecialchars(t('hosting_1c_stat_suport_value'), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                    <div class="hosting-1c-stat">
                        <dt><?php echo htmlspecialchars(t('hosting_1c_stat_securitate_label'), ENT_QUOTES, 'UTF-8'); ?></dt>
                        <dd><?php echo htmlspecialchars(t('hosting_1c_stat_securitate_value'), ENT_QUOTES, 'UTF-8'); ?></dd>
                    </div>
                </dl>
            </div>
            <aside class="hosting-1c-config reveal reveal-delay-2">
                <div class="hosting-1c-config-head">
                    <div>
                        <h3><?php echo htmlspecialchars(t('hosting_1c_config_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('hosting_1c_config_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <span class="hosting-1c-trial"><?php echo htmlspecialchars($site_data['1c']['trial_badge'] ?? t('hosting_1c_config_trial'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="hosting-1c-slider">
                    <div class="hosting-1c-slider-label">
                        <span><?php echo htmlspecialchars(t('hosting_1c_config_users'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <strong id="fg-users-val">1</strong>
                    </div>
                    <input id="fg-users" type="range" min="1" max="30" value="1" aria-label="<?php echo htmlspecialchars(t('hosting_1c_config_users'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="hosting-1c-slider">
                    <div class="hosting-1c-slider-label">
                        <span><?php echo htmlspecialchars(t('hosting_1c_config_space'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <strong id="fg-space-val">10 GB</strong>
                    </div>
                    <input id="fg-space" type="range" min="10" max="1000" step="10" value="10" aria-label="<?php echo htmlspecialchars(t('hosting_1c_config_space'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="hosting-1c-slider">
                    <div class="hosting-1c-slider-label">
                        <span><?php echo htmlspecialchars(t('hosting_1c_config_baze'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <strong id="fg-inst-val">1</strong>
                    </div>
                    <input id="fg-inst" type="range" min="1" max="20" step="1" value="1" aria-label="<?php echo htmlspecialchars(t('hosting_1c_config_baze'), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
                <div class="hosting-1c-price">
                    <p class="hosting-1c-price-label"><?php echo htmlspecialchars(t('hosting_1c_config_estimate'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p id="fg-price" class="hosting-1c-price-value">330 <?php echo htmlspecialchars(t('js_price_currency'), ENT_QUOTES, 'UTF-8'); ?> <span><?php echo htmlspecialchars(t('js_price_per_month'), ENT_QUOTES, 'UTF-8'); ?></span></p>
                </div>
                <a href="#contact" class="hosting-1c-config-btn" style="display:block;text-align:center;text-decoration:none;"><?php echo htmlspecialchars(t('hosting_1c_config_btn'), ENT_QUOTES, 'UTF-8'); ?></a>
                <p class="hosting-1c-config-note"><?php echo htmlspecialchars($site_data['1c']['migrare_note'] ?? t('hosting_1c_config_note'), ENT_QUOTES, 'UTF-8'); ?></p>
            </aside>
            </div>

            <div id="hosting-1c-details" class="hosting-1c-details reveal">
                <div class="hosting-1c-banner">
                    <img src="assets/business-man.jpg" alt="<?php echo htmlspecialchars(t('hosting_1c_detail_image_alt'), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" width="1200" height="400">
                </div>
                <div class="hosting-1c-details-grid">
                    <div class="hosting-1c-details-main">
                        <h3 class="heading-md"><?php echo htmlspecialchars(t('hosting_1c_detail_heading'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-muted" style="margin:1rem 0 1.5rem;font-size:1rem;line-height:1.7;"><?php echo htmlspecialchars(t('hosting_1c_detail_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <div class="hosting-1c-features-grid">
                            <?php foreach ($hosting_1c_features as $i => $feat) : ?>
                            <article class="hosting-1c-feature-card reveal reveal-delay-<?php echo ($i % 4) + 1; ?>">
                                <h4><?php echo htmlspecialchars(t($feat['title']), ENT_QUOTES, 'UTF-8'); ?></h4>
                                <p><?php echo htmlspecialchars(t($feat['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                            </article>
                            <?php endforeach; ?>
                        </div>
                        <div class="hosting-1c-included">
                            <strong><?php echo htmlspecialchars(t('hosting_1c_included_label'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <ul>
                                <?php for ($ii = 1; $ii <= 7; $ii++) : ?>
                                <li><?php echo htmlspecialchars(t('hosting_1c_included_' . $ii), ENT_QUOTES, 'UTF-8'); ?></li>
                                <?php endfor; ?>
                            </ul>
                        </div>
                    </div>
                    <aside class="hosting-1c-scenarios-card reveal reveal-delay-2">
                        <h3><?php echo htmlspecialchars(t('hosting_1c_scenarios_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="hosting-1c-scenarios-hint"><?php echo htmlspecialchars(t('hosting_1c_scenarios_hint'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <ul class="hosting-1c-scenario-list">
                            <?php foreach ($hosting_1c_scenarios as $si => $sc) : ?>
                            <li>
                                <button type="button" class="hosting-1c-scenario-item" data-scenario="<?php echo htmlspecialchars($sc['id'], ENT_QUOTES, 'UTF-8'); ?>" aria-haspopup="dialog">
                                    <span class="hosting-1c-scenario-dot hosting-1c-scenario-dot--<?php echo htmlspecialchars($sc['dot'], ENT_QUOTES, 'UTF-8'); ?>"></span>
                                    <span class="hosting-1c-scenario-text">
                                        <strong><?php echo htmlspecialchars(t($sc['title']), ENT_QUOTES, 'UTF-8'); ?></strong>
                                        <span><?php echo htmlspecialchars(t($sc['desc']), ENT_QUOTES, 'UTF-8'); ?></span>
                                    </span>
                                </button>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="#contact" class="hosting-1c-consultant-btn"><?php echo htmlspecialchars(t('hosting_1c_btn_consultant'), ENT_QUOTES, 'UTF-8'); ?></a>
                    </aside>
                </div>
            </div>
        </div>
    </section>

    <!-- CLOUD -->
    <section id="cloud" class="cloud-section section--dark">
        <div class="cloud-parallax" aria-hidden="true">
            <div class="cloud-layer">
                <img src="assets/coding-man.jpg" alt="" loading="lazy" width="1920" height="1080">
            </div>
            <div class="cloud-layer cloud-layer--overlay"></div>
        </div>
        <div class="container cloud-content">
            <div class="cloud-intro-col reveal">
                <span class="kicker kicker--accent"><?php echo htmlspecialchars(t('cloud_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg cloud-heading"><?php echo htmlspecialchars(t('cloud_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="cloud-intro"><?php echo htmlspecialchars(t('cloud_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="cloud-features">
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                <article class="cloud-feature reveal reveal-delay-<?php echo $i; ?>">
                    <span class="cloud-feature-num" aria-hidden="true">0<?php echo $i; ?></span>
                    <div class="cloud-feature-body">
                        <h3><?php echo htmlspecialchars(t('cloud_f' . $i . '_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t('cloud_f' . $i . '_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </article>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- DEZVOLTARE WEB -->
    <section id="dezvoltare" class="section dev-section" aria-labelledby="dev-title">
        <div class="dev-section-bg" aria-hidden="true">
            <img src="assets/code-testing-.jpg" alt="" loading="lazy" width="1920" height="1080">
        </div>
        <div class="container dev-section-inner">
            <div class="dev-grid">
                <div class="reveal">
                    <span class="kicker"><?php echo htmlspecialchars(t('section_dev_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <h2 id="dev-title" class="display-lg" style="margin-bottom:1rem;"><?php echo htmlspecialchars(t('section_dev_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="section-intro" style="margin-top:0;"><?php echo htmlspecialchars(t('section_dev_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <div class="dev-cards-grid">
                        <?php foreach ($dev_cards as $i => $card) : ?>
                        <article class="dev-type-card reveal reveal-delay-<?php echo ($i % 4) + 1; ?>">
                            <h3><?php echo htmlspecialchars(t($card['title']), ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo htmlspecialchars(t($card['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                        </article>
                        <?php endforeach; ?>
                    </div>
                    <ul class="dev-bullets">
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_1'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_2'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_3'), ENT_QUOTES, 'UTF-8'); ?></li>
                    </ul>
                    <a href="#contact" class="btn btn-primary" style="margin-top:1.5rem;"><?php echo htmlspecialchars(t('section_dev_btn'), ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
                <aside class="dev-portfolio reveal reveal-delay-2">
                    <div class="dev-portfolio-card">
                        <span class="kicker"><?php echo htmlspecialchars(t('section_dev_portfolio_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3 class="heading-md" style="margin:0.5rem 0 0.75rem;"><?php echo htmlspecialchars(t('section_dev_portfolio_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-muted" style="font-size:0.9375rem;margin:0;"><?php echo htmlspecialchars(t('section_dev_portfolio_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <?php foreach ($dev_portfolio as $project) : ?>
                        <a href="<?php echo htmlspecialchars($project['url'], ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" class="dev-portfolio-item">
                            <div class="dev-portfolio-brand">
                                <?php if (!empty($project['logo']) && is_file(__DIR__ . '/' . $project['logo'])) : ?>
                                <img src="<?php echo htmlspecialchars($project['logo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($project['alt'], ENT_QUOTES, 'UTF-8'); ?>" class="dev-portfolio-wordmark" width="160" height="72" loading="lazy">
                                <?php else : ?>
                                <span class="dev-portfolio-label"><?php echo htmlspecialchars($project['label'] ?? $project['alt'], ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($project['desc'])) : ?>
                                <span class="dev-portfolio-desc"><?php echo htmlspecialchars(t($project['desc']), ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="dev-portfolio-btn"><?php echo htmlspecialchars(t('section_dev_portfolio_btn'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </aside>
            </div>
        </div>
    </section>

    <!-- PROCESS -->
    <section id="process" class="section">
        <div class="container">
            <div class="section-header section-header--center reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('process_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('process_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="process-timeline" aria-label="<?php echo htmlspecialchars(t('process_heading'), ENT_QUOTES, 'UTF-8'); ?>">
                <?php foreach ($process_steps as $i => $step) : ?>
                <div class="process-step">
                    <span class="process-num"><?php echo $i + 1; ?></span>
                    <h4><?php echo htmlspecialchars(t($step['title']), ENT_QUOTES, 'UTF-8'); ?></h4>
                    <p><?php echo htmlspecialchars(t($step['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CASE STUDIES -->
    <section id="cases" class="section section--muted">
        <div class="container">
            <div class="section-header reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('cases_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('cases_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="section-intro"><?php echo htmlspecialchars(t('cases_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="cases-grid">
                <?php foreach ($cases as $i => $case) : ?>
                <article class="case-card reveal reveal-delay-<?php echo $i + 1; ?>">
                    <div class="case-card-image">
                        <img src="<?php echo htmlspecialchars($case['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars(t($case['title']), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" width="640" height="400">
                    </div>
                    <div class="case-card-body">
                        <span class="case-tag"><?php echo htmlspecialchars(t($case['tag']), ENT_QUOTES, 'UTF-8'); ?></span>
                        <h3><?php echo htmlspecialchars(t($case['title']), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p><?php echo htmlspecialchars(t($case['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="<?php echo htmlspecialchars($case['url'], ENT_QUOTES, 'UTF-8'); ?>" class="case-link"<?php echo $case['external'] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
                            <?php echo htmlspecialchars(t($case['link']), ENT_QUOTES, 'UTF-8'); ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="section--dark cta-band">
        <div class="container reveal">
            <h2 class="display-lg"><?php echo htmlspecialchars(t('cta_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <p><?php echo htmlspecialchars(t('cta_subheading'), ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="#contact" class="btn btn-primary"><?php echo htmlspecialchars(t('cta_button'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="section">
        <div class="container">
            <div class="section-header reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('section_contact_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('section_contact_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="contact-grid">
                <?php $contact = $site_data['contact'] ?? []; ?>
                <div class="reveal">
                    <div class="contact-info">
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars(t('section_contact_email_sales'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php echo htmlspecialchars($contact['email_sales'] ?? 'sales@smartsolutions.md', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars(t('section_contact_email_support'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php echo htmlspecialchars($contact['email_support'] ?? 'contact@smartsolutions.md', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars(t('section_contact_phone'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php if (!empty($contact['phone'])) : ?>
                                <a href="tel:<?php echo htmlspecialchars(preg_replace('/[^\d+]/', '', $contact['phone']), ENT_QUOTES, 'UTF-8'); ?>" class="contact-item-link"><?php echo htmlspecialchars($contact['phone'], ENT_QUOTES, 'UTF-8'); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars(t('section_contact_schedule'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <?php if ($chat_enabled) : ?>
                        <div class="contact-messengers">
                            <strong class="contact-messengers__label"><?php echo htmlspecialchars(t('section_contact_messengers'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <div class="contact-messengers__actions">
                                <?php if ($chat_whatsapp_url) : ?>
                                <a href="<?php echo htmlspecialchars($chat_whatsapp_url, ENT_QUOTES, 'UTF-8'); ?>" class="contact-messenger contact-messenger--whatsapp" target="_blank" rel="noopener noreferrer">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                    <?php echo htmlspecialchars(t('chat_whatsapp'), ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php endif; ?>
                                <?php if ($chat_viber_url) : ?>
                                <a href="<?php echo htmlspecialchars($chat_viber_url, ENT_QUOTES, 'UTF-8'); ?>" class="contact-messenger contact-messenger--viber">
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 8.82c-.06 1.88-.13 5.448 3.398 6.447l-.002 2.032s-.022.56.345.673c.398.123.63-.257 1.013-.667.21-.227.502-.555.722-.806 1.99 1.032 4.283 1.606 6.59 1.638.003 0 .01 0 .013 0 2.447 0 4.886-.643 7.01-1.86 3.56-1.944 3.585-5.228 3.57-6.312-.018-1.172-.04-4.92-3.28-7.158-2.29-1.57-5.033-1.912-6.68-1.926z"/></svg>
                                    <?php echo htmlspecialchars(t('chat_viber'), ENT_QUOTES, 'UTF-8'); ?>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="contact-map" role="img" aria-label="<?php echo htmlspecialchars(t('contact_map_placeholder'), ENT_QUOTES, 'UTF-8'); ?>">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4;margin-right:0.75rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?php echo htmlspecialchars(t('contact_map_placeholder'), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <form method="post" action="<?php echo htmlspecialchars(($lang !== 'ro' ? '?lang=' . urlencode($lang) : '') . '#contact', ENT_QUOTES, 'UTF-8'); ?>" class="contact-form reveal reveal-delay-2">
                    <?php if (!empty($contact_success)) : ?>
                        <div class="form-alert form-alert--success"><?php echo htmlspecialchars($contact_success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php elseif (!empty($contact_error)) : ?>
                        <div class="form-alert form-alert--error"><?php echo htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="nume"><?php echo htmlspecialchars(t('form_label_name'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="text" id="nume" name="nume" placeholder="<?php echo htmlspecialchars(t('form_placeholder_name'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo isset($nume) ? htmlspecialchars($nume, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email"><?php echo htmlspecialchars(t('form_label_email'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" id="email" name="email" placeholder="<?php echo htmlspecialchars(t('form_placeholder_email'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="mesaj"><?php echo htmlspecialchars(t('form_label_message'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <textarea id="mesaj" name="mesaj" rows="4" placeholder="<?php echo htmlspecialchars(t('form_placeholder_message'), ENT_QUOTES, 'UTF-8'); ?>" required><?php echo isset($mesaj) ? htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;"><?php echo htmlspecialchars(t('form_submit'), ENT_QUOTES, 'UTF-8'); ?></button>
                    <p style="margin:1rem 0 0;font-size:0.75rem;color:var(--text-muted);"><?php echo htmlspecialchars(t('form_privacy'), ENT_QUOTES, 'UTF-8'); ?></p>
                </form>
            </div>
        </div>
    </section>

</main>

<footer class="site-footer">
    <div class="container">
        <?php $company = $site_data['company'] ?? []; ?>
        <div class="footer-grid">
            <div>
                <img src="assets/LOGO/transparent%20logo.png" alt="<?php echo htmlspecialchars($company['name'] ?? $company_name, ENT_QUOTES, 'UTF-8'); ?>" class="footer-logo" width="160" height="32">
                <p style="font-size:0.875rem;max-width:20rem;opacity:0.75;"><?php echo htmlspecialchars($company['footer_text'] ?? t('footer_default_text'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="footer-links">
                <strong style="display:block;margin-bottom:0.75rem;font-size:0.8125rem;text-transform:uppercase;letter-spacing:0.08em;"><?php echo htmlspecialchars(t('footer_services'), ENT_QUOTES, 'UTF-8'); ?></strong>
                <a href="#cloud"><?php echo htmlspecialchars(t('footer_services_cloud'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#dezvoltare"><?php echo htmlspecialchars(t('footer_services_dev'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#hosting-1c"><?php echo htmlspecialchars(t('footer_services_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#services"><?php echo htmlspecialchars(t('footer_services_managed'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
            <div class="footer-links">
                <strong style="display:block;margin-bottom:0.75rem;font-size:0.8125rem;text-transform:uppercase;letter-spacing:0.08em;"><?php echo htmlspecialchars(t('footer_company'), ENT_QUOTES, 'UTF-8'); ?></strong>
                <a href="#"><?php echo htmlspecialchars(t('footer_about'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#"><?php echo htmlspecialchars(t('footer_privacy'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#"><?php echo htmlspecialchars(t('footer_terms'), ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['copyright'] ?? 'OND SOLUTIONS SRL', ENT_QUOTES, 'UTF-8'); ?></span>
            <span><?php echo htmlspecialchars(t('footer_payment'), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</footer>

<div id="scenario-modal" class="scenario-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="scenario-modal-title">
    <div class="scenario-modal-backdrop" data-scenario-close></div>
    <div class="scenario-modal-panel">
        <button type="button" class="scenario-modal-close" data-scenario-close aria-label="<?php echo htmlspecialchars(t('modal_close'), ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
        <span id="scenario-modal-dot" class="hosting-1c-scenario-dot hosting-1c-scenario-dot--primary scenario-modal-dot"></span>
        <h3 id="scenario-modal-title" class="heading-md" style="padding-right:2rem;"></h3>
        <p id="scenario-modal-lead" class="text-muted" style="margin-top:0.75rem;font-size:0.9375rem;line-height:1.65;"></p>
        <ul id="scenario-modal-list" class="scenario-modal-list"></ul>
        <a href="#contact" id="scenario-modal-cta" class="btn btn-primary" style="width:100%;margin-top:1.25rem;text-align:center;" data-scenario-close><?php echo htmlspecialchars(t('modal_scenario_cta'), ENT_QUOTES, 'UTF-8'); ?></a>
    </div>
</div>

<?php if ($chat_enabled) : ?>
<div class="chat-widget" id="chat-widget">
    <div class="chat-widget__panel" id="chat-widget-panel" hidden>
        <p class="chat-widget__title"><?php echo htmlspecialchars(t('chat_widget_title'), ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($chat_whatsapp_url) : ?>
        <a href="<?php echo htmlspecialchars($chat_whatsapp_url, ENT_QUOTES, 'UTF-8'); ?>" class="chat-widget__link chat-widget__link--whatsapp" target="_blank" rel="noopener noreferrer">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
            <span><?php echo htmlspecialchars(t('chat_whatsapp'), ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <?php endif; ?>
        <?php if ($chat_viber_url) : ?>
        <a href="<?php echo htmlspecialchars($chat_viber_url, ENT_QUOTES, 'UTF-8'); ?>" class="chat-widget__link chat-widget__link--viber">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M11.398.002C9.473.028 5.331.344 3.014 2.467 1.294 4.177.693 6.698.623 8.82c-.06 1.88-.13 5.448 3.398 6.447l-.002 2.032s-.022.56.345.673c.398.123.63-.257 1.013-.667.21-.227.502-.555.722-.806 1.99 1.032 4.283 1.606 6.59 1.638.003 0 .01 0 .013 0 2.447 0 4.886-.643 7.01-1.86 3.56-1.944 3.585-5.228 3.57-6.312-.018-1.172-.04-4.92-3.28-7.158-2.29-1.57-5.033-1.912-6.68-1.926z"/></svg>
            <span><?php echo htmlspecialchars(t('chat_viber'), ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <?php endif; ?>
    </div>
    <button type="button" class="chat-widget__toggle" id="chat-widget-toggle" aria-expanded="false" aria-controls="chat-widget-panel" aria-label="<?php echo htmlspecialchars(t('chat_widget_toggle'), ENT_QUOTES, 'UTF-8'); ?>">
        <svg class="chat-widget__icon chat-widget__icon--open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
        <svg class="chat-widget__icon chat-widget__icon--close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
    </button>
</div>
<?php endif; ?>

<button id="back-to-top" type="button" class="back-to-top" aria-label="<?php echo htmlspecialchars(t('back_to_top'), ENT_QUOTES, 'UTF-8'); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<script>
    window.siteData1C = <?php echo json_encode($site_data['1c'] ?? ['base_price'=>330,'per_user'=>30,'per_gb_factor'=>0.08,'per_instance'=>30,'min_price'=>150], JSON_UNESCAPED_UNICODE); ?>;
    window.i18nPrice = <?php echo json_encode(['currency' => t('js_price_currency'), 'perMonth' => t('js_price_per_month'), 'gb' => t('js_price_gb')], JSON_UNESCAPED_UNICODE); ?>;
    window.scenarioDetails = <?php echo json_encode($scenario_details, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="assets/js/site.js" defer></script>
</body>
</html>
