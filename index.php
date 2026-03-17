<?php
// index.php – Cloud Infrastructure & IT Solutions (modern layout)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/includes/site_data.php';
require __DIR__ . '/includes/lang.php';
$contact_success = '';
$contact_error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $last_name  = isset($_POST['last_name'])  ? trim($_POST['last_name'])  : '';
    $email      = isset($_POST['email'])      ? trim($_POST['email'])      : '';
    $mesaj      = isset($_POST['mesaj'])      ? trim($_POST['mesaj'])      : '';
    if ($first_name === '' || $last_name === '' || $email === '' || $mesaj === '') {
        $contact_error = t('contact_error_required', 'Te rugăm să completezi prenumele, numele, emailul și mesajul.');
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = t('contact_error_email', 'Adresa de email nu este validă.');
    } else {
        $db_ok = false;
        if (is_file(__DIR__ . '/db/config.php')) {
            try {
                require __DIR__ . '/db/config.php';
                $stmt = $pdo->prepare('INSERT INTO contact_requests (first_name, last_name, email, message) VALUES (:fn, :ln, :em, :msg)');
                $stmt->execute([
                    ':fn'  => mb_substr($first_name, 0, 100),
                    ':ln'  => mb_substr($last_name, 0, 100),
                    ':em'  => mb_substr($email, 0, 150),
                    ':msg' => $mesaj,
                ]);
                $db_ok = true;
            } catch (Throwable $e) {
                $db_ok = false;
            }
        }
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'mail.ondsolutions.md';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'contact@ondsolutions.md';
            $mail->Password   = 'AAD1sup@$$';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->CharSet    = 'UTF-8';
            $mail->setFrom('contact@ondsolutions.md', 'Smart Solutions');
            $mail->addAddress('sales@ondsolutions.md');
            $mail->addReplyTo($email, $first_name . ' ' . $last_name);
            $mail->Subject = 'Mesaj nou de pe formularul Smart Solutions';
            $body  = "Ai primit un mesaj nou de pe site.\r\n\r\n";
            $body .= "Prenume: {$first_name}\r\nNume: {$last_name}\r\nEmail: {$email}\r\n\r\nMesaj:\r\n{$mesaj}\r\n";
            $mail->Body = $body;
            $mail->send();
            $contact_success = t('contact_success_msg', 'Mesajul a fost trimis cu succes. Îți vom răspunde în cel mai scurt timp.');
            $first_name = $last_name = $email = $mesaj = '';
        } catch (Exception $e) {
            $contact_error = t('contact_error_send', 'A apărut o eroare la trimiterea mesajului') . ': ' .
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
$company = $site_data['company'] ?? [];
$contact = $site_data['contact'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?php echo ($lang ?? 'ro') === 'ru' ? 'ru' : 'ro'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cloud Infrastructure & IT Solutions | <?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta name="description" content="Infrastructură cloud, hosting 1C, VPS, găzduire web, soluții Microsoft și migrare. Servicii IT pentru companii moderne.">
    <meta property="og:title" content="Cloud Infrastructure & IT Solutions | <?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:description" content="Infrastructură de servere securizată, soluții Microsoft și migrare în cloud. Hosting 1C, VPS, găzduire web.">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ro_RO">
    <link rel="canonical" href="https://ondsolutions.md/">
    <link rel="icon" href="favicon.svg" type="image/svg+xml">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-blue': '#2563eb',
                        'deep': '#0f172a'
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                        heading: ['Manrope', 'Inter', 'sans-serif']
                    },
                    boxShadow: {
                        'primary': '0 10px 40px -10px rgba(37, 99, 235, 0.3)',
                        'primary-lg': '0 20px 50px -15px rgba(37, 99, 235, 0.4)'
                    },
                    accentColor: {
                        'primary-blue': '#2563eb'
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="assets/css/main.css">
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Organization",
        "name": "<?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?>",
        "url": "https://ondsolutions.md",
        "description": "<?php echo htmlspecialchars($company['footer_text'] ?? 'Găzduire web, VPS/VDS, hosting 1C și domenii.', ENT_QUOTES, 'UTF-8'); ?>",
        "email": "<?php echo htmlspecialchars($contact['email_sales'] ?? '', ENT_QUOTES, 'UTF-8'); ?>",
        "telephone": "<?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?>"
    }
    </script>
</head>
<body class="font-sans antialiased text-slate-800 bg-slate-50">
<?php include __DIR__ . '/partials/navbar.php'; ?>

<main>
<?php include __DIR__ . '/partials/hero.php'; ?>

<section id="services" class="relative py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <p class="reveal text-sm font-semibold uppercase tracking-widest text-primary-blue"><?php echo htmlspecialchars(t('section_services'), ENT_QUOTES, 'UTF-8'); ?></p>
        <h2 class="reveal font-heading mt-2 text-3xl font-bold text-slate-900 sm:text-4xl lg:text-5xl"><?php echo htmlspecialchars(t('section_services_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="reveal mt-4 max-w-2xl text-slate-600"><?php echo htmlspecialchars(t('section_services_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-blue/10 text-primary-blue font-bold">1C</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s1c_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s1c_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#pricing" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('link_details'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-cyan-500/10 text-cyan-500 font-bold">H</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s_web_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s_web_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#pricing" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('link_details'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-slate-100 text-primary-blue font-bold">VPS</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s_vps_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s_vps_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#pricing" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('link_details'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-primary-blue/10 text-primary-blue text-xl">✉</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s_email_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s_email_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#implementations" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('link_details'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-600 font-bold">B</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s_backup_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s_backup_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#contact" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('btn_request_quote'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <article class="reveal service-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 font-bold">Z</div>
                <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('s_zapier_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-2 text-sm text-slate-600"><?php echo htmlspecialchars(t('s_zapier_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <a href="#contact" class="mt-4 inline-block text-sm font-semibold text-primary-blue hover:underline"><?php echo htmlspecialchars(t('btn_request_quote'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
        </div>
    </div>
</section>

<section id="implementations" class="relative py-20 lg:py-28 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <p class="reveal text-sm font-semibold uppercase tracking-widest text-primary-blue"><?php echo htmlspecialchars(t('section_impl'), ENT_QUOTES, 'UTF-8'); ?></p>
        <h2 class="reveal font-heading mt-2 text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo htmlspecialchars(t('section_impl_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="reveal mt-4 max-w-2xl text-slate-600"><?php echo htmlspecialchars(t('section_impl_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-primary-blue font-bold text-sm">365</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_365'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_365_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-600 text-lg">✉</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_email'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_email_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-sky-100 text-sky-600 text-lg">☁</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_azure'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_azure_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 font-bold text-sm">VPN</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_vpn'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_vpn_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-primary-blue font-bold">WS</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_ws'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_ws_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
            <article class="reveal impl-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 text-lg">💾</div>
                <h3 class="font-heading font-bold text-slate-900"><?php echo htmlspecialchars(t('impl_backup'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('impl_backup_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
            </article>
        </div>
    </div>
</section>

<section id="pricing" class="relative py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <p class="reveal text-sm font-semibold uppercase tracking-widest text-primary-blue"><?php echo htmlspecialchars(t('section_pricing'), ENT_QUOTES, 'UTF-8'); ?></p>
        <h2 class="reveal font-heading mt-2 text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo htmlspecialchars(t('section_pricing_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
        <p class="reveal mt-4 max-w-2xl text-slate-600"><?php echo htmlspecialchars(t('section_pricing_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
        <div class="mt-12 grid gap-6 md:grid-cols-3">
            <?php
            $hosting = $site_data['hosting'] ?? [];
            foreach (array_slice($hosting, 0, 3) as $i => $plan):
                $is_featured = ($i === 1);
            ?>
            <article class="reveal pricing-card rounded-2xl border-2 p-6 <?php echo $is_featured ? 'featured border-primary-blue bg-white' : 'border-slate-200 bg-white'; ?>">
                <?php if ($is_featured): ?><p class="text-xs font-semibold uppercase text-primary-blue mb-2"><?php echo htmlspecialchars(t('pricing_recommended'), ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
                <h3 class="font-heading text-xl font-bold text-slate-900"><?php echo htmlspecialchars($plan['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars($plan['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="mt-4 text-3xl font-bold text-slate-900"><?php echo (int)($plan['price'] ?? 0); ?> <span class="text-base font-normal text-slate-500"><?php echo htmlspecialchars(t('pricing_per_month'), ENT_QUOTES, 'UTF-8'); ?></span></p>
                <p class="text-xs text-slate-500"><?php echo htmlspecialchars($plan['price_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                <ul class="mt-4 space-y-2 text-sm text-slate-600">
                    <?php foreach (($plan['features'] ?? []) as $f): ?>
                    <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-primary-blue"></span> <?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></li>
                    <?php endforeach; ?>
                </ul>
                <a href="#contact" class="mt-6 block w-full rounded-xl py-3 text-center text-sm font-semibold transition-all duration-200 <?php echo $is_featured ? 'bg-primary-blue text-white hover:opacity-95 hover:scale-[1.02] active:scale-[0.98]' : 'border-2 border-slate-200 text-slate-700 hover:border-primary-blue hover:text-primary-blue'; ?>"><?php echo htmlspecialchars(t('btn_request_quote'), ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
            <?php endforeach; ?>
        </div>
        <div class="reveal mt-12 rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:p-8">
            <h3 class="font-heading text-lg font-bold text-slate-900"><?php echo htmlspecialchars(t('pricing_1c_title'), ENT_QUOTES, 'UTF-8'); ?></h3>
            <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars(t('pricing_1c_sub'), ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($site_data['1c']['trial_badge'] ?? 'Test 7 zile', ENT_QUOTES, 'UTF-8'); ?>.</p>
            <div class="mt-4 grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="text-xs text-slate-600"><?php echo htmlspecialchars(t('label_users'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <input id="fg-users" type="range" min="1" max="30" value="1" class="mt-1 w-full accent-primary-blue">
                    <p id="fg-users-val" class="text-sm font-semibold text-slate-900">1</p>
                </div>
                <div>
                    <label class="text-xs text-slate-600"><?php echo htmlspecialchars(t('label_space'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <input id="fg-space" type="range" min="10" max="500" step="10" value="10" class="mt-1 w-full accent-primary-blue">
                    <p id="fg-space-val" class="text-sm font-semibold text-slate-900">10 GB</p>
                </div>
                <div>
                    <label class="text-xs text-slate-600"><?php echo htmlspecialchars(t('label_bases'), ENT_QUOTES, 'UTF-8'); ?></label>
                    <input id="fg-inst" type="range" min="1" max="20" value="1" class="mt-1 w-full accent-primary-blue">
                    <p id="fg-inst-val" class="text-sm font-semibold text-slate-900">1</p>
                </div>
            </div>
            <p class="mt-4 text-2xl font-bold text-slate-900"><?php echo htmlspecialchars(t('pricing_estimate'), ENT_QUOTES, 'UTF-8'); ?> <span id="fg-price"><?php echo (int)($site_data['1c']['min_price'] ?? 150); ?> lei</span><?php echo htmlspecialchars(t('pricing_per_month_short'), ENT_QUOTES, 'UTF-8'); ?></p>
            <p class="mt-2 text-xs text-slate-500"><?php echo htmlspecialchars($site_data['1c']['migrare_note'] ?? 'Migrare, VPN/RDP și consultanță incluse.', ENT_QUOTES, 'UTF-8'); ?></p>
            <a href="#contact" class="mt-4 inline-block rounded-xl bg-primary-blue px-5 py-2.5 text-sm font-semibold text-white shadow-lg shadow-primary-blue/30 transition-all hover:scale-[1.02] active:scale-[0.98]"><?php echo htmlspecialchars(t('btn_quote_detail'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</section>

<section id="about" class="relative py-20 lg:py-28 bg-slate-50">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div>
                <p class="reveal text-sm font-semibold uppercase tracking-widest text-primary-blue"><?php echo htmlspecialchars(t('section_about'), ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 class="reveal font-heading mt-2 text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="reveal mt-4 text-slate-600 leading-relaxed"><?php echo htmlspecialchars($company['footer_text'] ?? 'Găzduire web, VPS/VDS, hosting 1C și domenii pentru companii și proiecte din România și Europa.', ENT_QUOTES, 'UTF-8'); ?></p>
                <p class="reveal mt-4 text-slate-600"><?php echo htmlspecialchars(t('about_desc2'), ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="about-box reveal rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:border-primary-blue/30 hover:shadow-lg hover:shadow-primary-blue/10 hover:-translate-y-1">
                    <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-primary-blue/10 text-2xl transition-transform duration-300 about-box-icon">🖥</div>
                    <p class="font-heading font-semibold text-slate-900"><?php echo htmlspecialchars(t('box_servers'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('box_servers_sub'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="about-box reveal rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:border-primary-blue/30 hover:shadow-lg hover:shadow-primary-blue/10 hover:-translate-y-1">
                    <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-cyan-500/10 text-2xl transition-transform duration-300 about-box-icon">☁</div>
                    <p class="font-heading font-semibold text-slate-900"><?php echo htmlspecialchars(t('box_cloud'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('box_cloud_sub'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="about-box reveal rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:border-primary-blue/30 hover:shadow-lg hover:shadow-primary-blue/10 hover:-translate-y-1">
                    <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-emerald-500/10 text-2xl transition-transform duration-300 about-box-icon">🛡</div>
                    <p class="font-heading font-semibold text-slate-900"><?php echo htmlspecialchars(t('box_security'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('box_security_sub'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
                <div class="about-box reveal rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition-all duration-300 hover:border-primary-blue/30 hover:shadow-lg hover:shadow-primary-blue/10 hover:-translate-y-1">
                    <div class="mx-auto mb-2 flex h-14 w-14 items-center justify-center rounded-xl bg-amber-500/10 text-2xl transition-transform duration-300 about-box-icon">⚡</div>
                    <p class="font-heading font-semibold text-slate-900"><?php echo htmlspecialchars(t('box_automation'), ENT_QUOTES, 'UTF-8'); ?></p>
                    <p class="text-xs text-slate-600 mt-1"><?php echo htmlspecialchars(t('box_automation_sub'), ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="contact" class="relative py-20 lg:py-28 bg-white">
    <div class="mx-auto max-w-7xl px-4 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            <div>
                <p class="reveal text-sm font-semibold uppercase tracking-widest text-primary-blue"><?php echo htmlspecialchars(t('section_contact'), ENT_QUOTES, 'UTF-8'); ?></p>
                <h2 class="reveal font-heading mt-2 text-3xl font-bold text-slate-900 sm:text-4xl"><?php echo htmlspecialchars(t('contact_title'), ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="reveal mt-4 text-slate-600"><?php echo htmlspecialchars(t('contact_desc'), ENT_QUOTES, 'UTF-8'); ?></p>
                <div class="reveal mt-8 space-y-4">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-500"><?php echo htmlspecialchars(t('label_email_sales'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="mailto:<?php echo htmlspecialchars($contact['email_sales'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="text-slate-900 hover:text-primary-blue"><?php echo htmlspecialchars($contact['email_sales'] ?? 'sales@ondsolutions.md', ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-500"><?php echo htmlspecialchars(t('label_support'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <a href="mailto:<?php echo htmlspecialchars($contact['email_support'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="text-slate-900 hover:text-primary-blue"><?php echo htmlspecialchars($contact['email_support'] ?? 'contact@ondsolutions.md', ENT_QUOTES, 'UTF-8'); ?></a>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-500"><?php echo htmlspecialchars(t('label_phone'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-900"><?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <p class="text-xs font-semibold text-slate-500"><?php echo htmlspecialchars(t('label_schedule'), ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="text-slate-900"><?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>
            </div>
            <div class="reveal rounded-2xl border border-slate-200 bg-slate-50 p-6 lg:p-8">
                <form method="post" action="#contact" class="space-y-5">
                    <?php if ($contact_success): ?>
                    <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800"><?php echo htmlspecialchars($contact_success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php elseif ($contact_error): ?>
                    <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800"><?php echo htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="block text-sm font-medium text-slate-700"><?php echo htmlspecialchars(t('label_firstname'), ENT_QUOTES, 'UTF-8'); ?></label>
                            <input type="text" name="first_name" value="<?php echo isset($first_name) ? htmlspecialchars($first_name, ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="<?php echo htmlspecialchars(t('placeholder_firstname'), ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700"><?php echo htmlspecialchars(t('label_lastname'), ENT_QUOTES, 'UTF-8'); ?></label>
                            <input type="text" name="last_name" value="<?php echo isset($last_name) ? htmlspecialchars($last_name, ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="<?php echo htmlspecialchars(t('placeholder_lastname'), ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700"><?php echo htmlspecialchars(t('label_email'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <input type="email" name="email" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="<?php echo htmlspecialchars(t('placeholder_email'), ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700"><?php echo htmlspecialchars(t('label_message'), ENT_QUOTES, 'UTF-8'); ?></label>
                        <textarea name="mesaj" rows="4" placeholder="<?php echo htmlspecialchars(t('placeholder_message'), ENT_QUOTES, 'UTF-8'); ?>" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-slate-900 focus:border-primary-blue focus:ring-2 focus:ring-primary-blue/20"><?php echo isset($mesaj) ? htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>
                    <button type="submit" class="w-full rounded-xl bg-primary-blue py-3.5 text-sm font-semibold text-white shadow-lg shadow-primary-blue/30 transition-all hover:scale-[1.02] active:scale-[0.98]"><?php echo htmlspecialchars(t('btn_send'), ENT_QUOTES, 'UTF-8'); ?></button>
                </form>
            </div>
        </div>
    </div>
</section>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>
<?php include __DIR__ . '/partials/chat_widget.php'; ?>

<script src="assets/js/app.js"></script>
<script>
(function() {
    function blockCopyCut(e) {
        var t = e.target;
        if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
        e.preventDefault();
    }
    document.addEventListener('copy', blockCopyCut);
    document.addEventListener('cut', blockCopyCut);
})();
</script>
<script>
(function() {
    var c = <?php echo json_encode($site_data['1c'] ?? ['base_price'=>330,'per_user'=>30,'per_gb_factor'=>0.08,'per_instance'=>30,'min_price'=>150]); ?>;
    function calcPrice1C() {
        var users = parseInt(document.getElementById('fg-users').value, 10) || 1;
        var space = parseInt(document.getElementById('fg-space').value, 10) || 10;
        var inst = parseInt(document.getElementById('fg-inst').value, 10) || 1;
        document.getElementById('fg-users-val').textContent = users;
        document.getElementById('fg-space-val').textContent = space + ' GB';
        document.getElementById('fg-inst-val').textContent = inst;
        var base = (c.base_price || 330) + (users - 1) * (c.per_user || 30) + (space - 10) / 100 * (c.per_gb_factor || 0.08) * 100 + (inst - 1) * (c.per_instance || 30);
        var price = Math.max(c.min_price || 150, Math.round(base));
        document.getElementById('fg-price').textContent = price + ' lei';
    }
    ['fg-users', 'fg-space', 'fg-inst'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', calcPrice1C);
    });
    calcPrice1C();
})();
</script>
</body>
</html>
