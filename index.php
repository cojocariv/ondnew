<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/includes/site_data.php';
require __DIR__ . '/includes/i18n.php';

$contact_success = '';
$contact_error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume   = isset($_POST['nume'])   ? trim($_POST['nume'])   : '';
    $email  = isset($_POST['email'])  ? trim($_POST['email'])  : '';
    $mesaj  = isset($_POST['mesaj'])  ? trim($_POST['mesaj'])  : '';
    if ($nume === '' || $email === '' || $mesaj === '') {
        $contact_error = t('contact_error_required');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = t('contact_error_invalid_email');
    } else {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.smartsolutions.md';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact@smartsolutions.md';
            $mail->Password   = 'AAD1sup@$$';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('contact@smartsolutions.md', 'Smart Solutions');
            $mail->addAddress('sales@smartsolutions.md');
            $mail->addReplyTo($email, $nume);
            $mail->Subject = 'Mesaj nou de pe formularul Smart Solutions';
            $body  = "Ai primit un mesaj nou de pe site.\r\n\r\n";
            $body .= "Nume: {$nume}\r\n";
            $body .= "Email: {$email}\r\n\r\n";
            $body .= "Mesaj:\r\n{$mesaj}\r\n";
            $mail->Body = $body;
            $mail->send();
            $contact_success = t('contact_success');
            $nume = $email = $mesaj = '';
        } catch (Exception $e) {
            $contact_error = t('contact_error_send_prefix') .
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}

$company_seo = $site_data['company'] ?? [];
$contact_seo = $site_data['contact'] ?? [];
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

$why_blocks = [
    ['title' => 'why_security_title', 'desc' => 'why_security_desc', 'image' => 'assets/business-man.jpg'],
    ['title' => 'why_reliability_title', 'desc' => 'why_reliability_desc', 'image' => 'assets/coding-man.jpg'],
    ['title' => 'why_scalability_title', 'desc' => 'why_scalability_desc', 'image' => 'assets/ai-nuclear-energy.jpg'],
    ['title' => 'why_cost_title', 'desc' => 'why_cost_desc', 'image' => 'assets/pen-adult-.jpg'],
    ['title' => 'why_automation_title', 'desc' => 'why_automation_desc', 'image' => 'assets/code-testing-.jpg'],
    ['title' => 'why_support_title', 'desc' => 'why_support_desc', 'image' => 'assets/contact-us-word.jpg'],
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
    <link rel="icon" href="assets/LOGO/transparent%20logo.png" type="image/png">
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
            <img src="assets/LOGO/transparent%20logo.png" alt="<?php echo htmlspecialchars($company_name, ENT_QUOTES, 'UTF-8'); ?>" class="site-logo" width="180" height="40">
        </a>
        <nav class="nav-desktop" aria-label="<?php echo htmlspecialchars(t('nav_aria_main'), ENT_QUOTES, 'UTF-8'); ?>">
            <a href="#services"><?php echo htmlspecialchars(t('nav_services'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#cloud"><?php echo htmlspecialchars(t('nav_cloud'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#why"><?php echo htmlspecialchars(t('nav_why'), ENT_QUOTES, 'UTF-8'); ?></a>
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
        <a href="#cloud" data-mobile-nav><?php echo htmlspecialchars(t('nav_cloud'), ENT_QUOTES, 'UTF-8'); ?></a>
        <a href="#why" data-mobile-nav><?php echo htmlspecialchars(t('nav_why'), ENT_QUOTES, 'UTF-8'); ?></a>
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

    <!-- CLOUD -->
    <section id="cloud" class="cloud-section section--dark">
        <div class="cloud-parallax" aria-hidden="true">
            <div class="cloud-layer" data-parallax="0.2">
                <img src="assets/coding-man.jpg" alt="" loading="lazy" width="1920" height="1080">
            </div>
            <div class="cloud-layer cloud-layer--overlay"></div>
        </div>
        <div class="container cloud-content">
            <div class="reveal">
                <span class="kicker kicker--light"><?php echo htmlspecialchars(t('cloud_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('cloud_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="section-intro" style="margin-top:1.5rem;"><?php echo htmlspecialchars(t('cloud_intro'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="cloud-features">
                <?php for ($i = 1; $i <= 4; $i++) : ?>
                <div class="cloud-feature reveal reveal-delay-<?php echo $i; ?>">
                    <span class="cloud-feature-num">0<?php echo $i; ?></span>
                    <div>
                        <strong><?php echo htmlspecialchars(t('cloud_f' . $i . '_title'), ENT_QUOTES, 'UTF-8'); ?></strong>
                        <p style="margin:0.35rem 0 0;font-size:0.875rem;opacity:0.85;"><?php echo htmlspecialchars(t('cloud_f' . $i . '_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>

    <!-- WHY -->
    <section id="why" class="section section--muted">
        <div class="container">
            <div class="section-header section-header--center reveal">
                <span class="kicker"><?php echo htmlspecialchars(t('why_kicker'), ENT_QUOTES, 'UTF-8'); ?></span>
                <h2 class="display-lg"><?php echo htmlspecialchars(t('why_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            </div>
            <div class="why-blocks">
                <?php foreach ($why_blocks as $i => $block) : ?>
                <div class="why-block reveal">
                    <div class="why-block-image">
                        <img src="<?php echo htmlspecialchars($block['image'], ENT_QUOTES, 'UTF-8'); ?>" alt="" loading="lazy" width="800" height="600">
                    </div>
                    <div class="why-block-content">
                        <span class="why-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                            <?php echo htmlspecialchars(t($block['title']), ENT_QUOTES, 'UTF-8'); ?>
                        </span>
                        <h3 class="heading-md"><?php echo htmlspecialchars(t($block['title']), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-muted" style="margin-top:1rem;font-size:1.0625rem;"><?php echo htmlspecialchars(t($block['desc']), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
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
            <div class="process-timeline">
                <?php foreach ($process_steps as $i => $step) : ?>
                <div class="process-step reveal reveal-delay-<?php echo min($i + 1, 4); ?>">
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
                            <?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                        <div class="contact-item">
                            <strong><?php echo htmlspecialchars(t('section_contact_schedule'), ENT_QUOTES, 'UTF-8'); ?></strong>
                            <?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    </div>
                    <div class="contact-map" role="img" aria-label="<?php echo htmlspecialchars(t('contact_map_placeholder'), ENT_QUOTES, 'UTF-8'); ?>">
                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="opacity:0.4;margin-right:0.75rem;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <?php echo htmlspecialchars(t('contact_map_placeholder'), ENT_QUOTES, 'UTF-8'); ?>
                    </div>
                </div>
                <form method="post" action="#contact" class="contact-form reveal reveal-delay-2">
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
                <a href="#services"><?php echo htmlspecialchars(t('footer_services_dev'), ENT_QUOTES, 'UTF-8'); ?></a>
                <a href="#services"><?php echo htmlspecialchars(t('footer_services_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
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

<button id="back-to-top" type="button" class="back-to-top" aria-label="<?php echo htmlspecialchars(t('back_to_top'), ENT_QUOTES, 'UTF-8'); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
</button>

<script src="assets/js/site.js" defer></script>
</body>
</html>
