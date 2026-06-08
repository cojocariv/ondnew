<?php
// index.php – Landing hosting 1C + hosting/VPS/domenii, cu trimitere email prin SMTP
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
$scenario_details = [
    'firma-mica' => [
        'title' => t('scenario_firma_mica_title'),
        'dotClass' => 'bg-ihcBlue',
        'lead' => t('scenario_firma_mica_lead'),
        'bullets' => [t('scenario_firma_mica_b1'), t('scenario_firma_mica_b2'), t('scenario_firma_mica_b3'), t('scenario_firma_mica_b4'), t('scenario_firma_mica_b5')],
    ],
    'grup' => [
        'title' => t('scenario_grup_title'),
        'dotClass' => 'bg-ihcOrange',
        'lead' => t('scenario_grup_lead'),
        'bullets' => [t('scenario_grup_b1'), t('scenario_grup_b2'), t('scenario_grup_b3'), t('scenario_grup_b4'), t('scenario_grup_b5')],
    ],
    'crm' => [
        'title' => t('scenario_crm_title'),
        'dotClass' => 'bg-emerald-500',
        'lead' => t('scenario_crm_lead'),
        'bullets' => [t('scenario_crm_b1'), t('scenario_crm_b2'), t('scenario_crm_b3'), t('scenario_crm_b4'), t('scenario_crm_b5')],
    ],
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
    <meta name="theme-color" content="#2563EB">

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

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ihcBlue: '#2563EB',
                        ihcBlueDark: '#1D4ED8',
                        ihcOrange: '#F97316',
                        ihcGray: '#111827'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out forwards',
                        'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
                        'slide-in-right': 'slideInRight 0.5s ease-out forwards',
                    },
                    keyframes: {
                        fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
                        fadeInUp: { '0%': { opacity: '0', transform: 'translateY(20px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
                        slideInRight: { '0%': { opacity: '0', transform: 'translateX(16px)' }, '100%': { opacity: '1', transform: 'translateX(0)' } },
                    },
                }
            }
        }
    </script>

    <style>
        body {
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(59,130,246,0.15), transparent),
                        radial-gradient(ellipse 60% 40% at 100% 50%, rgba(249,115,22,0.08), transparent),
                        radial-gradient(ellipse 60% 40% at 0% 80%, rgba(59,130,246,0.06), transparent),
                        #f8fafc;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        input, textarea { -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; user-select: text; }
        section[id] { scroll-margin-top: 90px; }

        :root { --header-h: 4.25rem; }
        .site-header {
            transition: background-color 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease, backdrop-filter 0.3s ease;
            background-color: transparent;
            border-bottom: 1px solid transparent;
            box-shadow: none;
        }
        .site-header.is-scrolled {
            background-color: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom-color: rgba(226, 232, 240, 0.8);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
        }

        .site-logo {
            height: 2.75rem;
            width: auto;
            max-width: min(220px, 55vw);
            object-fit: contain;
        }
        .site-logo-footer {
            height: 2rem;
            width: auto;
            max-width: 180px;
            object-fit: contain;
        }
        .section-photo {
            overflow: hidden;
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 20px 40px -16px rgba(15, 23, 42, 0.12);
        }
        .section-photo img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .section-photo-banner { height: 14rem; }
        @media (min-width: 640px) {
            .section-photo-banner { height: 16rem; }
        }
        @media (min-width: 1024px) {
            .section-photo-side { min-height: 100%; height: auto; }
            .section-photo-side img { min-height: 320px; }
        }
        .hero-section {
            position: relative;
            min-height: 32rem;
            margin-top: calc(-1 * var(--header-h));
            padding-top: var(--header-h);
        }
        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .hero-bg video {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-bg video { display: none; }
            .hero-bg {
                background: url('assets/woman-typing-laptop.png') center / cover no-repeat;
            }
        }
        .hero-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                105deg,
                rgba(255, 255, 255, 0.96) 0%,
                rgba(255, 255, 255, 0.88) 32%,
                rgba(248, 250, 252, 0.72) 52%,
                rgba(248, 250, 252, 0.38) 72%,
                rgba(248, 250, 252, 0.22) 100%
            );
        }
        .hero-inner {
            position: relative;
            z-index: 1;
        }
        .hero-content-inner {
            padding: 0.25rem 0;
        }
        @media (min-width: 1024px) {
            .hero-section { min-height: 36rem; }
        }
        .dev-section {
            position: relative;
            overflow: hidden;
        }
        .dev-section-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .dev-section-bg img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .dev-section-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.96) 0%,
                rgba(248, 250, 252, 0.92) 45%,
                rgba(248, 250, 252, 0.88) 100%
            );
        }
        .dev-section-inner {
            position: relative;
            z-index: 1;
        }
        .portfolio-logo {
            height: 2.75rem;
            width: auto;
            max-width: 7rem;
            object-fit: contain;
        }
        .contact-section {
            position: relative;
            overflow: hidden;
        }
        .contact-section-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .contact-section-bg img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }
        .contact-section-bg::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(
                135deg,
                rgba(255, 255, 255, 0.94) 0%,
                rgba(248, 250, 252, 0.9) 50%,
                rgba(248, 250, 252, 0.86) 100%
            );
        }
        .contact-section-inner {
            position: relative;
            z-index: 1;
        }
        .glass { background: rgba(255,255,255,0.92); backdrop-filter: blur(20px); }
        .glass-soft { background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); }
        .shadow-strong { box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(0,0,0,0.03); }
        .pill-dot { box-shadow: 0 0 0 5px rgba(34,197,94,0.18); }
        .uptime-badge {
            animation: uptimeBadgeIn 0.65s ease-out forwards, uptimeGlow 2.8s ease-in-out infinite 0.65s;
        }
        .uptime-dot {
            position: relative;
            flex-shrink: 0;
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25);
            animation: uptimeDotPulse 2s ease-in-out infinite;
        }
        .uptime-dot::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: 9999px;
            border: 2px solid rgba(34, 197, 94, 0.55);
            animation: uptimeRing 2s ease-out infinite;
        }
        @keyframes uptimeBadgeIn {
            from { opacity: 0; transform: translateY(10px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes uptimeGlow {
            0%, 100% {
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
                border-color: rgba(167, 243, 208, 0.75);
            }
            50% {
                box-shadow: 0 0 14px rgba(34, 197, 94, 0.22);
                border-color: rgba(52, 211, 153, 0.95);
            }
        }
        @keyframes uptimeDotPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.85; }
        }
        @keyframes uptimeRing {
            0% { transform: scale(0.85); opacity: 0.75; }
            70%, 100% { transform: scale(2.4); opacity: 0; }
        }

        .no-select { -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; }

        .card-anim {
            transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.25s ease, border-color 0.2s ease, background-color 0.2s ease;
            transform: translateY(0) scale(1);
        }
        .card-anim:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 24px 48px -12px rgba(37, 99, 235, 0.18), 0 0 0 1px rgba(37, 99, 235, 0.08);
            border-color: rgba(37, 99, 235, 0.4);
            background-color: rgba(255, 255, 255, 0.98);
        }

        .btn-anim {
            transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease, background-color 0.2s ease, border-color 0.2s ease;
        }
        .btn-anim:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .btn-anim:active { transform: translateY(0) scale(0.98); }

        .reveal { opacity: 0; transform: translateY(24px); }
        .reveal.revealed { animation: fadeInUp 0.7s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .reveal-stagger > * { opacity: 0; transform: translateY(16px); }
        .reveal-stagger.revealed > *:nth-child(1) { animation: fadeInUp 0.5s ease-out 0.1s forwards; }
        .reveal-stagger.revealed > *:nth-child(2) { animation: fadeInUp 0.5s ease-out 0.2s forwards; }
        .reveal-stagger.revealed > *:nth-child(3) { animation: fadeInUp 0.5s ease-out 0.3s forwards; }

        .gradient-text { background: linear-gradient(135deg, #F97316, #2563EB); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .section-kicker { letter-spacing: 0.2em; }

        [data-reveal] { opacity: 0; transform: translateY(20px); transition: opacity 0.6s ease, transform 0.6s ease; }
        [data-reveal].visible { opacity: 1; transform: translateY(0); }

        /* Animații câmpuri VPS */
        .vps-config-item {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.25s ease, background-color 0.25s ease;
        }
        .vps-config-item:hover {
            transform: translateX(6px) scale(1.02);
            box-shadow: 0 8px 24px -6px rgba(37, 99, 235, 0.2);
            border-color: rgba(37, 99, 235, 0.4);
            background-color: rgba(248, 250, 252, 0.95);
        }
        .vps-config-item .vps-dot {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .vps-config-item:hover .vps-dot {
            transform: scale(1.35);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
        }
        .vps-config-list > li { opacity: 0; transform: translateX(-12px); }
        .vps-config-list > li.visible { animation: vpsItemIn 0.5s ease-out forwards; }
        .vps-config-list > li:nth-child(1) { animation-delay: 0.1s; }
        .vps-config-list > li:nth-child(2) { animation-delay: 0.2s; }
        .vps-config-list > li:nth-child(3) { animation-delay: 0.3s; }
        @keyframes vpsItemIn {
            to { opacity: 1; transform: translateX(0); }
        }

        /* Animații Scenarii hosting 1C */
        .scenario-1c-item {
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, border-color 0.25s ease, background-color 0.25s ease;
            cursor: pointer;
            text-align: left;
            width: 100%;
        }
        .scenario-1c-item:hover {
            transform: translateX(6px) scale(1.02);
            box-shadow: 0 8px 24px -6px rgba(37, 99, 235, 0.18);
            border-color: rgba(37, 99, 235, 0.35);
            background-color: rgba(248, 250, 252, 0.95);
        }
        .scenario-1c-item .scenario-dot {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .scenario-1c-item:hover .scenario-dot {
            transform: scale(1.35);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.18);
        }
        .scenario-1c-item:active {
            transform: translateX(4px) scale(0.99);
        }
        .scenario-1c-list > li { opacity: 0; transform: translateX(-12px); }
        .scenario-1c-list > li.visible { animation: scenario1cIn 0.5s ease-out forwards; }
        .scenario-1c-list > li:nth-child(1) { animation-delay: 0.1s; }
        .scenario-1c-list > li:nth-child(2) { animation-delay: 0.2s; }
        .scenario-1c-list > li:nth-child(3) { animation-delay: 0.3s; }
        @keyframes scenario1cIn {
            to { opacity: 1; transform: translateX(0); }
        }

        .scenario-modal {
            position: fixed;
            inset: 0;
            z-index: 60;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .scenario-modal.is-open {
            opacity: 1;
            visibility: visible;
        }
        .scenario-modal-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
        }
        .scenario-modal-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 28rem;
            border-radius: 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 28px 60px -16px rgba(15, 23, 42, 0.28);
            padding: 1.5rem;
            transform: translateY(24px) scale(0.94);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.35s ease;
        }
        .scenario-modal.is-open .scenario-modal-panel {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
        .scenario-modal-close {
            position: absolute;
            top: 0.85rem;
            right: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2rem;
            height: 2rem;
            border: none;
            border-radius: 9999px;
            background: rgba(241, 245, 249, 0.95);
            color: #64748b;
            font-size: 1.25rem;
            line-height: 1;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .scenario-modal-close:hover {
            background: #e2e8f0;
            color: #0f172a;
        }
        .scenario-modal-dot {
            display: inline-block;
            width: 0.625rem;
            height: 0.625rem;
            border-radius: 9999px;
            margin-bottom: 0.75rem;
        }

        .back-to-top {
            position: fixed;
            right: 1.25rem;
            bottom: 1.25rem;
            z-index: 40;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.75rem;
            height: 2.75rem;
            border: none;
            border-radius: 9999px;
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            color: #fff;
            box-shadow: 0 10px 28px rgba(37, 99, 235, 0.35);
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transform: translateY(14px);
            transition: opacity 0.3s ease, visibility 0.3s ease, transform 0.3s ease, box-shadow 0.2s ease;
        }
        .back-to-top.is-visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .back-to-top:hover {
            box-shadow: 0 14px 32px rgba(37, 99, 235, 0.45);
            transform: translateY(-2px);
        }
        .back-to-top.is-visible:hover {
            transform: translateY(-2px);
        }
        .back-to-top svg {
            width: 1.25rem;
            height: 1.25rem;
        }

        .lang-switcher {
            display: inline-flex;
            align-items: center;
            gap: 0.125rem;
            border-radius: 9999px;
            border: 1px solid rgba(226, 232, 240, 0.9);
            background: rgba(255, 255, 255, 0.85);
            padding: 0.125rem;
        }
        .lang-switcher a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 2rem;
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #64748b;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .lang-switcher a:hover { color: #2563EB; }
        .lang-switcher a.is-active {
            background: #2563EB;
            color: #fff;
        }

        .mobile-menu-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.5rem;
            height: 2.5rem;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.9);
            color: #334155;
            cursor: pointer;
            transition: border-color 0.2s ease, color 0.2s ease, background-color 0.2s ease;
        }
        .mobile-menu-btn:hover {
            border-color: rgba(37, 99, 235, 0.4);
            color: #2563EB;
        }
        .mobile-menu-btn svg {
            width: 1.25rem;
            height: 1.25rem;
        }
        .mobile-menu-btn .icon-close { display: none; }
        .mobile-menu-btn.is-open .icon-menu { display: none; }
        .mobile-menu-btn.is-open .icon-close { display: block; }

        .mobile-nav {
            display: none;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .mobile-nav.is-open { display: block; }
        .mobile-nav-inner {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
            padding: 0.75rem 1rem 1rem;
        }
        .mobile-nav a,
        .mobile-nav button {
            display: block;
            width: 100%;
            text-align: left;
            padding: 0.75rem 0.5rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: #475569;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .mobile-nav a:hover,
        .mobile-nav button:hover {
            background: rgba(241, 245, 249, 0.9);
            color: #2563EB;
        }
        .mobile-nav-cta {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            margin-top: 0.5rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
        }
        .mobile-nav-cta button,
        .mobile-nav-cta a {
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        @media (min-width: 768px) {
            .mobile-menu-btn,
            .mobile-nav { display: none !important; }
        }
    </style>
</head>
<body class="text-slate-900 antialiased">

<!-- HEADER -->
<header id="site-header" class="site-header sticky top-0 z-50">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 lg:px-8">
        <a href="#top" class="flex items-center group" aria-label="<?php echo htmlspecialchars(t('nav_aria_home', $company_name), ENT_QUOTES, 'UTF-8'); ?>">
            <img src="assets/LOGO/transparent%20logo.png" alt="<?php echo htmlspecialchars($site_data['company']['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?>" class="site-logo transition group-hover:scale-[1.02]">
        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex" aria-label="<?php echo htmlspecialchars(t('nav_aria_main'), ENT_QUOTES, 'UTF-8'); ?>">
            <a href="#1c" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full"><?php echo htmlspecialchars(t('nav_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#dezvoltare" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full"><?php echo htmlspecialchars(t('nav_dezvoltare'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#vps" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full"><?php echo htmlspecialchars(t('nav_vps_vds'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#zapier" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full"><?php echo htmlspecialchars(t('nav_automatizari'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#contact" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full"><?php echo htmlspecialchars(t('nav_contact'), ENT_QUOTES, 'UTF-8'); ?></a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            <div class="lang-switcher" role="navigation" aria-label="Language">
                <?php foreach (['ro' => 'RO', 'en' => 'EN', 'ru' => 'RU'] as $code => $label) : ?>
                <a href="<?php echo htmlspecialchars(lang_url($code), ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $lang === $code ? 'is-active' : ''; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <div class="hidden items-center gap-3 md:flex">
                <button onclick="scrollToSection('1c')" class="btn-anim rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 shadow-sm hover:border-ihcBlue hover:text-ihcBlue hover:shadow">
                    <?php echo htmlspecialchars(t('nav_cta_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <a href="#contact" class="btn-anim inline-flex items-center rounded-full bg-gradient-to-r from-ihcOrange to-ihcBlue px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl hover:shadow-ihcBlue/40">
                    <?php echo htmlspecialchars(t('nav_cta_cere_oferta'), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
            <button id="mobile-menu-btn" type="button" class="mobile-menu-btn md:hidden" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php echo htmlspecialchars(t('nav_menu_open'), ENT_QUOTES, 'UTF-8'); ?>">
                <svg class="icon-menu" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16"></path>
                </svg>
                <svg class="icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18"></path>
                </svg>
            </button>
        </div>
    </div>
    <nav id="mobile-nav" class="mobile-nav md:hidden" aria-label="<?php echo htmlspecialchars(t('nav_aria_main'), ENT_QUOTES, 'UTF-8'); ?>" hidden>
        <div class="mobile-nav-inner mx-auto max-w-6xl">
            <a href="#1c" data-mobile-nav-link><?php echo htmlspecialchars(t('nav_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#dezvoltare" data-mobile-nav-link><?php echo htmlspecialchars(t('nav_dezvoltare'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#vps" data-mobile-nav-link><?php echo htmlspecialchars(t('nav_vps_vds'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#zapier" data-mobile-nav-link><?php echo htmlspecialchars(t('nav_automatizari'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#contact" data-mobile-nav-link><?php echo htmlspecialchars(t('nav_contact'), ENT_QUOTES, 'UTF-8'); ?></a>
            <div class="mobile-nav-cta">
                <button type="button" data-mobile-nav-link data-scroll="1c" class="btn-anim rounded-full border border-slate-300 bg-white py-2.5 text-slate-700 shadow-sm">
                    <?php echo htmlspecialchars(t('nav_cta_hosting_1c'), ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <a href="#contact" data-mobile-nav-link class="btn-anim rounded-full bg-gradient-to-r from-ihcOrange to-ihcBlue py-2.5 text-white shadow-lg shadow-ihcBlue/30">
                    <?php echo htmlspecialchars(t('nav_cta_cere_oferta'), ENT_QUOTES, 'UTF-8'); ?>
                </a>
            </div>
        </div>
    </nav>
</header>

<main id="top" class="pb-20">

    <!-- HERO -->
    <section class="hero-section relative overflow-hidden border-b border-slate-200/60" aria-labelledby="hero-title">
        <div class="hero-bg" aria-hidden="true">
            <video autoplay muted loop playsinline preload="auto" poster="assets/woman-typing-laptop.png">
                <source src="<?php echo htmlspecialchars($hero_video_url, ENT_QUOTES, 'UTF-8'); ?>" type="video/mp4">
            </video>
        </div>
        <div class="hero-inner mx-auto grid max-w-6xl gap-12 px-4 py-16 lg:grid-cols-[1.1fr,0.95fr] lg:items-center lg:px-8 lg:py-20">
            <div class="hero-content-inner flex flex-col justify-center" data-reveal>
                <p class="mb-4 text-xs font-bold uppercase tracking-[0.3em] text-ihcBlue"><?php echo htmlspecialchars(t('hero_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
                <h1 id="hero-title" class="mb-5 text-4xl font-bold leading-[1.15] tracking-tight text-slate-900 sm:text-5xl lg:text-[2.75rem]">
                    <?php echo t('hero_title'); ?>
                </h1>
                <p class="mb-6 max-w-lg text-base text-slate-600 leading-relaxed">
                    <?php echo htmlspecialchars(t('hero_subtitle'), ENT_QUOTES, 'UTF-8'); ?>
                </p>

                <div class="mb-6 flex flex-wrap gap-2">
                    <?php $h = $site_data['hero'] ?? []; ?>
                    <span class="uptime-badge inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200/80 px-3 py-1.5 text-xs font-medium text-emerald-800">
                        <span class="uptime-dot h-2 w-2 rounded-full bg-emerald-500"></span> <?php echo htmlspecialchars($h['badge_1'] ?? t('hero_badge_uptime'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-ihcBlue"></span> <?php echo htmlspecialchars($h['badge_2'] ?? t('hero_badge_migrare'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-ihcOrange"></span> <?php echo htmlspecialchars($h['badge_3'] ?? t('hero_badge_rdp_vpn'), ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button onclick="scrollToSection('1c')" class="btn-anim rounded-full bg-ihcBlue px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/35 hover:bg-ihcBlueDark">
                        <?php echo htmlspecialchars(t('hero_btn_detalii'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                    <button onclick="scrollToSection('contact')" class="btn-anim rounded-full border-2 border-slate-300 bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-700 hover:border-ihcBlue hover:text-ihcBlue">
                        <?php echo htmlspecialchars(t('hero_btn_oferta'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>

                <dl class="mt-8 grid grid-cols-3 gap-4 text-xs">
                    <div><dt class="text-slate-400"><?php echo htmlspecialchars(t('hero_stat_experienta_label'), ENT_QUOTES, 'UTF-8'); ?></dt><dd class="font-semibold text-slate-800"><?php echo htmlspecialchars(t('hero_stat_experienta_value'), ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt class="text-slate-400"><?php echo htmlspecialchars(t('hero_stat_suport_label'), ENT_QUOTES, 'UTF-8'); ?></dt><dd class="font-semibold text-slate-800"><?php echo htmlspecialchars(t('hero_stat_suport_value'), ENT_QUOTES, 'UTF-8'); ?></dd></div>
                    <div><dt class="text-slate-400"><?php echo htmlspecialchars(t('hero_stat_securitate_label'), ENT_QUOTES, 'UTF-8'); ?></dt><dd class="font-semibold text-slate-800"><?php echo htmlspecialchars(t('hero_stat_securitate_value'), ENT_QUOTES, 'UTF-8'); ?></dd></div>
                </dl>
            </div>

            <aside class="relative z-10" data-reveal>
                <div class="glass shadow-strong rounded-3xl border border-white/60 p-5 sm:p-6">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900"><?php echo htmlspecialchars(t('hero_config_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                        <p class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars(t('hero_config_subtitle'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <span class="rounded-full bg-ihcOrange/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ihcOrange"><?php echo htmlspecialchars($site_data['1c']['trial_badge'] ?? t('hero_config_trial_badge'), ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span><?php echo htmlspecialchars(t('hero_config_users'), ENT_QUOTES, 'UTF-8'); ?></span><span id="fg-users-val" class="font-semibold text-slate-900">3</span></div>
                        <input id="fg-users" type="range" min="1" max="30" value="1" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span><?php echo htmlspecialchars(t('hero_config_space'), ENT_QUOTES, 'UTF-8'); ?></span><span id="fg-space-val" class="font-semibold text-slate-900">20 GB</span></div>
                        <input id="fg-space" type="range" min="10" max="1000" step="10" value="10" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span><?php echo htmlspecialchars(t('hero_config_baze'), ENT_QUOTES, 'UTF-8'); ?></span><span id="fg-inst-val" class="font-semibold text-slate-900">1</span></div>
                        <input id="fg-inst" type="range" min="1" max="20" step="1" value="1" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-ihcBlue font-semibold"><?php echo htmlspecialchars(t('hero_config_estimate'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p id="fg-price" class="text-2xl font-bold text-slate-900">190 <?php echo htmlspecialchars(t('hero_config_price_currency'), ENT_QUOTES, 'UTF-8'); ?> <span class="text-sm font-normal text-slate-500"><?php echo htmlspecialchars(t('hero_config_price_suffix'), ENT_QUOTES, 'UTF-8'); ?></span></p>
                    </div>
                </div>
                <button onclick="scrollToSection('contact')" class="btn-anim mt-4 w-full rounded-xl bg-gradient-to-r from-ihcOrange to-ihcBlue py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl">
                    <?php echo htmlspecialchars(t('hero_config_btn'), ENT_QUOTES, 'UTF-8'); ?>
                </button>
                <p class="mt-3 text-[11px] text-slate-500"><?php echo htmlspecialchars($site_data['1c']['migrare_note'] ?? t('hero_config_migrare_note'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </aside>
        </div>
    </section>

    <!-- 1C -->
    <section id="1c" class="border-b border-slate-200/60 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-s font-bold uppercase text-ihcBlue"><?php echo htmlspecialchars(t('section_1c_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="mt-6 section-photo section-photo-banner max-w-4xl" data-reveal>
                <img src="assets/business-man.jpg" alt="<?php echo htmlspecialchars(t('section_1c_image_alt'), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
            </div>
            <div class="mt-10 grid gap-10 lg:grid-cols-[1.05fr,1fr] lg:gap-14">
                <div data-reveal>
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight"><?php echo htmlspecialchars(t('section_1c_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        <?php echo htmlspecialchars(t('section_1c_intro'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_1c_card_migrare_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_1c_card_migrare_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_1c_card_acces_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_1c_card_acces_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_1c_card_backup_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_1c_card_backup_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_1c_card_scalare_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_1c_card_scalare_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <p class="text-xs font-bold text-slate-800 mb-2"><?php echo htmlspecialchars(t('section_1c_included_label'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <ul class="list-disc space-y-1 pl-5 text-xs text-slate-600">
                            <li><?php echo htmlspecialchars(t('section_1c_included_1'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_2'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_3'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_4'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_5'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_6'), ENT_QUOTES, 'UTF-8'); ?></li>
                            <li><?php echo htmlspecialchars(t('section_1c_included_7'), ENT_QUOTES, 'UTF-8'); ?></li>
                        </ul>
                    </div>
                </div>
                <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong scenario-1c-card" data-reveal>
                    <h3 class="text-sm font-bold text-slate-900 mb-1"><?php echo htmlspecialchars(t('section_1c_scenarios_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="text-[11px] text-slate-500 mb-3"><?php echo htmlspecialchars(t('section_1c_scenarios_hint'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <ul class="scenario-1c-list mt-4 space-y-4">
                        <li>
                        <button type="button" class="scenario-1c-item flex gap-3 rounded-xl border border-transparent p-3 transition-colors hover:border-slate-200" data-scenario="firma-mica" aria-haspopup="dialog">
                            <span class="scenario-dot mt-1.5 h-2 w-2 shrink-0 rounded-full bg-ihcBlue"></span>
                            <div>
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars(t('section_1c_scenario_firma_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-600"><?php echo htmlspecialchars(t('section_1c_scenario_firma_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </button>
                        </li>
                        <li>
                        <button type="button" class="scenario-1c-item flex gap-3 rounded-xl border border-transparent p-3 transition-colors hover:border-slate-200" data-scenario="grup" aria-haspopup="dialog">
                            <span class="scenario-dot mt-1.5 h-2 w-2 shrink-0 rounded-full bg-ihcOrange"></span>
                            <div>
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars(t('section_1c_scenario_grup_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-600"><?php echo htmlspecialchars(t('section_1c_scenario_grup_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </button>
                        </li>
                        <li>
                        <button type="button" class="scenario-1c-item flex gap-3 rounded-xl border border-transparent p-3 transition-colors hover:border-slate-200" data-scenario="crm" aria-haspopup="dialog">
                            <span class="scenario-dot mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="font-semibold text-slate-900"><?php echo htmlspecialchars(t('section_1c_scenario_crm_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="text-xs text-slate-600"><?php echo htmlspecialchars(t('section_1c_scenario_crm_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </button>
                        </li>
                    </ul>
                    <button onclick="scrollToSection('contact')" class="btn-cta-primary btn-anim mt-6 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white transition-all duration-300 hover:shadow-lg hover:shadow-ihcBlue/25 hover:-translate-y-0.5">
                        <?php echo htmlspecialchars(t('section_1c_btn_consultant'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- DEZVOLTARE SITE-URI -->
    <section id="dezvoltare" class="dev-section border-b border-slate-200/60">
        <div class="dev-section-bg" aria-hidden="true">
            <img src="assets/code-testing-.jpg" alt="" loading="lazy" decoding="async">
        </div>
        <div class="dev-section-inner mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue"><?php echo htmlspecialchars(t('section_dev_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="mt-2 grid gap-10 lg:grid-cols-[1.15fr,0.85fr] lg:gap-14 lg:items-start">
                <div data-reveal>
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight"><?php echo htmlspecialchars(t('section_dev_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        <?php echo htmlspecialchars(t('section_dev_intro'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="glass-soft rounded-2xl border border-slate-200/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_dev_card_landing_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_dev_card_landing_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="glass-soft rounded-2xl border border-slate-200/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_dev_card_apps_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_dev_card_apps_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="glass-soft rounded-2xl border border-slate-200/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_dev_card_shop_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_dev_card_shop_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="glass-soft rounded-2xl border border-slate-200/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_dev_card_visit_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_dev_card_visit_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <ul class="mt-6 list-disc space-y-1 pl-5 text-sm text-slate-600">
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_1'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_2'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_dev_bullet_3'), ENT_QUOTES, 'UTF-8'); ?></li>
                    </ul>
                    <button onclick="scrollToSection('contact')" class="btn-anim mt-8 rounded-full bg-ihcBlue px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/35 hover:bg-ihcBlueDark">
                        <?php echo htmlspecialchars(t('section_dev_btn'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>
                <div data-reveal>
                    <div class="glass-soft rounded-3xl border border-slate-200/80 p-5 shadow-strong">
                        <p class="text-xs font-bold uppercase tracking-widest text-ihcBlue"><?php echo htmlspecialchars(t('section_dev_portfolio_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <h3 class="mt-2 text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('section_dev_portfolio_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('section_dev_portfolio_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="https://alinabradu.com" target="_blank" rel="noopener noreferrer" class="card-anim mt-5 flex items-center gap-4 rounded-2xl border border-slate-200 bg-white p-4 hover:border-ihcBlue/50">
                            <img src="assets/portfolio/alinabradu-logo.png" alt="Logo Alina Bradu" class="portfolio-logo shrink-0">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-slate-900">alinabradu.com</p>
                                <p class="text-xs text-slate-500 mt-0.5"><?php echo htmlspecialchars(t('section_dev_portfolio_item_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                            <span class="shrink-0 rounded-full bg-ihcBlue/10 px-3 py-1 text-[10px] font-bold uppercase tracking-wider text-ihcBlue"><?php echo htmlspecialchars(t('section_dev_portfolio_btn'), ENT_QUOTES, 'UTF-8'); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- VPS -->
    <section id="vps" class="border-b border-slate-200/60 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-s font-bold uppercase text-ihcBlue"><?php echo htmlspecialchars(t('section_vps_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900"><?php echo htmlspecialchars(t('section_vps_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <div class="grid gap-3 sm:grid-cols-2" data-reveal>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_vps_card_kvm_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_vps_card_kvm_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_vps_card_resurse_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_vps_card_resurse_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_vps_card_dc_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_vps_card_dc_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_vps_card_mgmt_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                        <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_vps_card_mgmt_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
                <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong vps-config-card" data-reveal>
                    <h3 class="text-sm font-bold text-slate-900 mb-3"><?php echo htmlspecialchars(t('section_vps_config_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <ul class="vps-config-list space-y-3">
                        <?php
                        $vps_colors = ['bg-ihcBlue', 'bg-ihcOrange', 'bg-emerald-500'];
                        foreach (($site_data['vps'] ?? []) as $vi => $v) :
                            $dot = $vps_colors[$vi] ?? 'bg-ihcBlue';
                        ?>
                        <li class="vps-config-item flex gap-3 rounded-xl border border-slate-200 bg-white p-3 cursor-default">
                            <span class="vps-dot mt-1.5 h-2 w-2 shrink-0 rounded-full <?php echo $dot; ?>"></span>
                            <div><p class="font-semibold text-slate-900"><?php echo htmlspecialchars($v['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p><p class="text-xs text-slate-600"><?php echo htmlspecialchars(($v['specs'] ?? '') . ' ' . sprintf(t('section_vps_price_from'), (int)($v['price'] ?? 0)), ENT_QUOTES, 'UTF-8'); ?></p></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mt-3 text-[11px] text-slate-600"><?php echo htmlspecialchars(t('section_vps_config_note'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <button onclick="scrollToSection('contact')" class="vps-cta-btn btn-anim mt-4 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white transition-all duration-300 hover:shadow-lg hover:shadow-ihcBlue/25 hover:-translate-y-0.5">
                        <?php echo htmlspecialchars(t('section_vps_btn'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- AUTOMATIZARE ZAPIER -->
    <section id="zapier" class="border-b border-slate-200/60 bg-slate-50/70">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-s font-bold uppercase text-ihcBlue"><?php echo htmlspecialchars(t('section_zapier_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
            <div class="mt-2 grid gap-10 lg:grid-cols-[1.05fr,1fr] lg:gap-14">
                <div data-reveal>
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight"><?php echo htmlspecialchars(t('section_zapier_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        <?php echo htmlspecialchars(t('section_zapier_intro'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_zapier_card_periodice_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_zapier_card_periodice_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_zapier_card_integrari_title'), ENT_QUOTES, 'UTF-8'); ?></p>
                            <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('section_zapier_card_integrari_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                        </div>
                    </div>
                    <ul class="mt-6 list-disc space-y-1 pl-5 text-sm text-slate-600">
                        <li><?php echo htmlspecialchars(t('section_zapier_bullet_1'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_zapier_bullet_2'), ENT_QUOTES, 'UTF-8'); ?></li>
                        <li><?php echo htmlspecialchars(t('section_zapier_bullet_3'), ENT_QUOTES, 'UTF-8'); ?></li>
                    </ul>
                </div>
                <div class="space-y-4" data-reveal>
                    <div class="section-photo section-photo-banner">
                        <img src="assets/ai-nuclear-energy.jpg" alt="<?php echo htmlspecialchars(t('section_zapier_image_alt'), ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" decoding="async">
                    </div>
                    <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong">
                    <h3 class="text-sm font-bold text-slate-900"><?php echo htmlspecialchars(t('section_zapier_example_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="mt-3 text-sm text-slate-600">
                        <?php echo htmlspecialchars(t('section_zapier_example_body'), ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                    <button onclick="scrollToSection('contact')" class="btn-anim mt-6 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white">
                        <?php echo htmlspecialchars(t('section_zapier_btn'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="contact-section border-t border-slate-200/60">
        <div class="contact-section-bg" aria-hidden="true">
            <img src="assets/contact-us-word.jpg" alt="" loading="lazy" decoding="async">
        </div>
        <div class="contact-section-inner mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue"><?php echo htmlspecialchars(t('section_contact_kicker'), ENT_QUOTES, 'UTF-8'); ?></p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900"><?php echo htmlspecialchars(t('section_contact_heading'), ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <?php $contact = $site_data['contact'] ?? []; ?>
                <div class="grid gap-3 sm:grid-cols-2 text-sm" data-reveal>
                    <div class="glass-soft rounded-2xl border border-slate-200/80 p-4">
                        <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars(t('section_contact_email_sales'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['email_sales'] ?? 'sales@smartsolutions.md', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="glass-soft rounded-2xl border border-slate-200/80 p-4">
                        <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars(t('section_contact_email_support'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['email_support'] ?? 'contact@smartsolutions.md', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="glass-soft rounded-2xl border border-slate-200/80 p-4">
                        <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars(t('section_contact_phone'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="glass-soft rounded-2xl border border-slate-200/80 p-4">
                        <p class="text-xs font-bold text-slate-900"><?php echo htmlspecialchars(t('section_contact_schedule'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

                <form method="post" action="#contact" class="glass rounded-3xl border border-white/70 p-5 shadow-strong space-y-4" data-reveal>
                    <?php if (!empty($contact_success)) : ?>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo htmlspecialchars($contact_success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php elseif (!empty($contact_error)) : ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800"><?php echo htmlspecialchars(t('form_label_name'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="text" name="nume" placeholder="<?php echo htmlspecialchars(t('form_placeholder_name'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo isset($nume) ? htmlspecialchars($nume, ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800"><?php echo htmlspecialchars(t('form_label_email'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" name="email" placeholder="<?php echo htmlspecialchars(t('form_placeholder_email'), ENT_QUOTES, 'UTF-8'); ?>" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800"><?php echo htmlspecialchars(t('form_label_message'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <textarea name="mesaj" placeholder="<?php echo htmlspecialchars(t('form_placeholder_message'), ENT_QUOTES, 'UTF-8'); ?>" rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20"><?php echo isset($mesaj) ? htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn-anim w-full rounded-xl bg-gradient-to-r from-ihcOrange to-ihcBlue py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl">
                        <?php echo htmlspecialchars(t('form_submit'), ENT_QUOTES, 'UTF-8'); ?>
                    </button>
                    <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars(t('form_privacy'), ENT_QUOTES, 'UTF-8'); ?></p>
                </form>
            </div>
        </div>
    </section>
</main>

<footer class="border-t border-slate-200 bg-slate-100/80">
    <div class="mx-auto max-w-6xl px-4 py-8 lg:px-8">
        <div class="grid gap-6 md:grid-cols-[2fr,1fr,1fr]">
            <?php $company = $site_data['company'] ?? []; ?>
            <div>
                <div class="mb-2">
                    <img src="assets/LOGO/transparent%20logo.png" alt="<?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?>" class="site-logo-footer">
                </div>
                <p class="text-xs text-slate-600 max-w-sm"><?php echo htmlspecialchars($company['footer_text'] ?? t('footer_default_text'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-800 mb-2"><?php echo htmlspecialchars(t('footer_services'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="space-y-1 text-xs"><a href="#1c" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_services_1c'), ENT_QUOTES, 'UTF-8'); ?></a><a href="#dezvoltare" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_services_dev'), ENT_QUOTES, 'UTF-8'); ?></a><a href="#vps" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_services_vps'), ENT_QUOTES, 'UTF-8'); ?></a><a href="#zapier" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_services_zapier'), ENT_QUOTES, 'UTF-8'); ?></a></div>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-800 mb-2"><?php echo htmlspecialchars(t('footer_company'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="space-y-1 text-xs"><a href="#" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_about'), ENT_QUOTES, 'UTF-8'); ?></a><a href="#" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_privacy'), ENT_QUOTES, 'UTF-8'); ?></a><a href="#" class="block text-slate-600 hover:text-ihcBlue"><?php echo htmlspecialchars(t('footer_terms'), ENT_QUOTES, 'UTF-8'); ?></a></div>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-slate-200 flex flex-col gap-2 text-xs text-slate-500 sm:flex-row sm:justify-between">
            <span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['copyright'] ?? 'OND SOLUTIONS SRL', ENT_QUOTES, 'UTF-8'); ?>.</span>
            <span><?php echo htmlspecialchars(t('footer_payment'), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</footer>

<div id="scenario-modal" class="scenario-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="scenario-modal-title">
    <div class="scenario-modal-backdrop" data-scenario-close></div>
    <div class="scenario-modal-panel">
        <button type="button" class="scenario-modal-close" data-scenario-close aria-label="<?php echo htmlspecialchars(t('modal_close'), ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
        <span id="scenario-modal-dot" class="scenario-modal-dot bg-ihcBlue"></span>
        <h3 id="scenario-modal-title" class="text-xl font-bold text-slate-900 pr-8"></h3>
        <p id="scenario-modal-lead" class="mt-3 text-sm text-slate-600 leading-relaxed"></p>
        <ul id="scenario-modal-list" class="mt-4 space-y-2 text-sm text-slate-700"></ul>
        <button type="button" id="scenario-modal-cta" class="btn-anim mt-6 w-full rounded-xl bg-gradient-to-r from-ihcOrange to-ihcBlue py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl">
            <?php echo htmlspecialchars(t('modal_scenario_cta'), ENT_QUOTES, 'UTF-8'); ?>
        </button>
    </div>
</div>

<button id="back-to-top" type="button" class="back-to-top btn-anim" aria-label="<?php echo htmlspecialchars(t('back_to_top'), ENT_QUOTES, 'UTF-8'); ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M12 19V5"></path>
        <path d="M5 12l7-7 7 7"></path>
    </svg>
</button>

<script>
    var siteData1C = <?php echo json_encode($site_data['1c'] ?? ['base_price'=>330,'per_user'=>30,'per_gb_factor'=>0.08,'per_instance'=>30,'min_price'=>150]); ?>;
    var i18nPrice = <?php echo json_encode(['currency' => t('js_price_currency'), 'perMonth' => t('js_price_per_month'), 'gb' => t('js_price_gb')], JSON_UNESCAPED_UNICODE); ?>;
    function scrollToSection(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var top = el.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    function calcPrice1C() {
        var users = parseInt(document.getElementById('fg-users').value, 10);
        var space = parseInt(document.getElementById('fg-space').value, 10);
        var inst = parseInt(document.getElementById('fg-inst').value, 10);
        document.getElementById('fg-users-val').textContent = users;
        document.getElementById('fg-space-val').textContent = space + ' ' + i18nPrice.gb;
        document.getElementById('fg-inst-val').textContent = inst;
        var c = siteData1C;
        var base = (c.base_price || 330) + (users - 1) * (c.per_user || 30) + (space - 10) / 100 * (c.per_gb_factor || 0.08) * 100 + (inst - 1) * (c.per_instance || 30);
        var price = Math.max(c.min_price || 150, Math.round(base));
        document.getElementById('fg-price').innerHTML = price + ' ' + i18nPrice.currency + ' <span class="text-sm font-normal text-slate-500">' + i18nPrice.perMonth + '</span>';
    }
    ['fg-users', 'fg-space', 'fg-inst'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', calcPrice1C);
    });
    calcPrice1C();

    (function() {
        var header = document.getElementById('site-header');
        var backToTop = document.getElementById('back-to-top');
        var mobileMenuBtn = document.getElementById('mobile-menu-btn');
        var mobileNav = document.getElementById('mobile-nav');
        var menuOpenLabel = <?php echo json_encode(t('nav_menu_open'), JSON_UNESCAPED_UNICODE); ?>;
        var menuCloseLabel = <?php echo json_encode(t('nav_menu_close'), JSON_UNESCAPED_UNICODE); ?>;

        function setMobileMenu(open) {
            if (!mobileMenuBtn || !mobileNav) return;
            mobileMenuBtn.classList.toggle('is-open', open);
            mobileNav.classList.toggle('is-open', open);
            mobileMenuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileMenuBtn.setAttribute('aria-label', open ? menuCloseLabel : menuOpenLabel);
            mobileNav.hidden = !open;
            document.body.style.overflow = open ? 'hidden' : '';
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                setMobileMenu(!mobileNav.classList.contains('is-open'));
            });
        }

        document.querySelectorAll('[data-mobile-nav-link]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                var scrollId = link.getAttribute('data-scroll');
                if (scrollId) {
                    e.preventDefault();
                    scrollToSection(scrollId);
                }
                setMobileMenu(false);
            });
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && mobileNav && mobileNav.classList.contains('is-open')) {
                setMobileMenu(false);
            }
        });

        function onScroll() {
            var y = window.scrollY;
            if (header) header.classList.toggle('is-scrolled', y > 24);
            if (backToTop) backToTop.classList.toggle('is-visible', y > 320);
        }
        if (backToTop) {
            backToTop.addEventListener('click', function() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    })();

    (function() {
        var reveals = document.querySelectorAll('[data-reveal]');
        function reveal() {
            reveals.forEach(function(el) {
                var top = el.getBoundingClientRect().top;
                var win = window.innerHeight - 80;
                if (top < win) {
                    el.classList.add('visible');
                    if (el.classList.contains('vps-config-card')) {
                        var items = el.querySelectorAll('.vps-config-list > li');
                        items.forEach(function(li) { li.classList.add('visible'); });
                    }
                    if (el.classList.contains('scenario-1c-card')) {
                        var items = el.querySelectorAll('.scenario-1c-list > li');
                        items.forEach(function(li) { li.classList.add('visible'); });
                    }
                }
            });
        }
        window.addEventListener('scroll', reveal);
        window.addEventListener('load', reveal);
        reveal();
    })();

    (function() {
        var scenarioDetails = <?php echo json_encode($scenario_details, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

        var modal = document.getElementById('scenario-modal');
        var titleEl = document.getElementById('scenario-modal-title');
        var leadEl = document.getElementById('scenario-modal-lead');
        var listEl = document.getElementById('scenario-modal-list');
        var dotEl = document.getElementById('scenario-modal-dot');
        var ctaEl = document.getElementById('scenario-modal-cta');
        var lastFocus = null;

        function closeScenarioModal() {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (lastFocus) lastFocus.focus();
        }

        function openScenarioModal(key) {
            var data = scenarioDetails[key];
            if (!data || !modal) return;
            lastFocus = document.activeElement;
            dotEl.className = 'scenario-modal-dot ' + data.dotClass;
            titleEl.textContent = data.title;
            leadEl.textContent = data.lead;
            listEl.innerHTML = data.bullets.map(function(item) {
                return '<li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span><span>' + item + '</span></li>';
            }).join('');
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            modal.querySelector('.scenario-modal-close').focus();
        }

        document.querySelectorAll('[data-scenario]').forEach(function(btn) {
            btn.addEventListener('click', function() {
                openScenarioModal(btn.getAttribute('data-scenario'));
            });
        });

        document.querySelectorAll('[data-scenario-close]').forEach(function(el) {
            el.addEventListener('click', closeScenarioModal);
        });

        if (ctaEl) {
            ctaEl.addEventListener('click', function() {
                closeScenarioModal();
                scrollToSection('contact');
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && modal.classList.contains('is-open')) {
                closeScenarioModal();
            }
        });

        window.closeScenarioModal = closeScenarioModal;
    })();
</script>
</body>
</html>
