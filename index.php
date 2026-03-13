<?php
// index.php – Landing hosting 1C + hosting/VPS/domenii, cu trimitere email prin SMTP
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require __DIR__ . '/phpmailer/src/Exception.php';
require __DIR__ . '/phpmailer/src/PHPMailer.php';
require __DIR__ . '/phpmailer/src/SMTP.php';
require __DIR__ . '/includes/site_data.php';
$contact_success = '';
$contact_error   = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nume   = isset($_POST['nume'])   ? trim($_POST['nume'])   : '';
    $email  = isset($_POST['email'])  ? trim($_POST['email'])  : '';
    $mesaj  = isset($_POST['mesaj'])  ? trim($_POST['mesaj'])  : '';
    if ($nume === '' || $email === '' || $mesaj === '') {
        $contact_error = 'Te rugăm să completezi numele, emailul și mesajul.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $contact_error = 'Adresa de email nu este validă.';
    } else {
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
            $mail->addReplyTo($email, $nume);
            $mail->Subject = 'Mesaj nou de pe formularul Smart Solutions';
            $body  = "Ai primit un mesaj nou de pe site.\r\n\r\n";
            $body .= "Nume: {$nume}\r\n";
            $body .= "Email: {$email}\r\n\r\n";
            $body .= "Mesaj:\r\n{$mesaj}\r\n";
            $mail->Body = $body;
            $mail->send();
            $contact_success = 'Mesajul a fost trimis cu succes. Îți vom răspunde în cel mai scurt timp.';
            $nume = $email = $mesaj = '';
        } catch (Exception $e) {
            $contact_error = 'A apărut o eroare la trimiterea mesajului: ' .
                htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Hosting 1C & VPS modern | Smart Solutions</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

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

        .logo-glow {
            background: linear-gradient(135deg, #2563EB 0%, #7C3AED 50%, #F97316 100%);
            box-shadow: 0 4px 20px rgba(37, 99, 235, 0.35);
        }
        .glass { background: rgba(255,255,255,0.92); backdrop-filter: blur(20px); }
        .glass-soft { background: rgba(255,255,255,0.9); backdrop-filter: blur(16px); }
        .shadow-strong { box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.15), 0 0 0 1px rgba(0,0,0,0.03); }
        .pill-dot { box-shadow: 0 0 0 5px rgba(34,197,94,0.18); }

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
    </style>
</head>
<body class="text-slate-900 antialiased">

<!-- HEADER -->
<header class="sticky top-0 z-50 border-b border-slate-200/80 bg-white/90 backdrop-blur-xl shadow-sm">
    <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3 lg:px-8">
        <a href="#top" class="flex items-center gap-3 group">
            <div class="logo-glow flex h-10 w-10 items-center justify-center rounded-xl text-sm font-bold text-white transition group-hover:scale-105">
                S
            </div>
            <div>
                <span class="block text-xs font-bold uppercase tracking-widest text-slate-800"><?php echo htmlspecialchars($site_data['company']['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="block text-[10px] text-slate-500"><?php echo htmlspecialchars($site_data['company']['tagline'] ?? 'Hosting 1C • VPS • Domenii', ENT_QUOTES, 'UTF-8'); ?></span>
            </div>
        </a>

        <nav class="hidden items-center gap-8 text-sm font-medium text-slate-600 md:flex">
            <a href="#1c" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">Hosting 1C</a>
            <a href="#tarife" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">Găzduire web</a>
            <a href="#vps" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">VPS / VDS</a>
            <a href="#zapier" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">Automatizări</a>
            <a href="#domenii" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">Domenii</a>
            <a href="#contact" class="relative py-2 hover:text-ihcBlue after:absolute after:bottom-0 after:left-0 after:h-0.5 after:w-0 after:bg-ihcBlue after:transition-all after:duration-200 hover:after:w-full">Contact</a>
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            <button onclick="scrollToSection('1c')" class="btn-anim rounded-full border border-slate-300 bg-white px-4 py-2 text-xs font-semibold uppercase tracking-wider text-slate-700 shadow-sm hover:border-ihcBlue hover:text-ihcBlue hover:shadow">
                Hosting 1C
            </button>
            <a href="#contact" class="btn-anim inline-flex items-center rounded-full bg-gradient-to-r from-ihcOrange to-ihcBlue px-4 py-2 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl hover:shadow-ihcBlue/40">
                Cere ofertă
            </a>
        </div>
    </div>
</header>

<main id="top" class="pb-20">

    <!-- HERO -->
    <section class="relative overflow-hidden border-b border-slate-200/60 bg-gradient-to-b from-white via-slate-50/50 to-transparent">
        <div class="mx-auto grid max-w-6xl gap-12 px-4 py-16 lg:grid-cols-[1.1fr,0.95fr] lg:px-8 lg:py-20">
            <div class="flex flex-col justify-center" data-reveal>
                <p class="mb-4 text-xs font-bold uppercase tracking-[0.3em] text-ihcBlue">Hosting 1C în cloud</p>
                <h1 class="mb-5 text-4xl font-bold leading-[1.15] tracking-tight text-slate-900 sm:text-5xl lg:text-[2.75rem]">
                    1C în cloud, <span class="gradient-text">accesibil de oriunde</span> și oricând.
                </h1>
                <p class="mb-6 max-w-lg text-base text-slate-600 leading-relaxed">
                    Mutăm 1C în cloud, cu backup automat și acces securizat pentru echipa ta — contabilitatea și procesele interne fără întreruperi.
                </p>

                <div class="mb-6 flex flex-wrap gap-2">
                    <?php $h = $site_data['hero'] ?? []; ?>
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 border border-emerald-200/80 px-3 py-1.5 text-xs font-medium text-emerald-800">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 pill-dot"></span> <?php echo htmlspecialchars($h['badge_1'] ?? 'Uptime 99,9%+', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-ihcBlue"></span> <?php echo htmlspecialchars($h['badge_2'] ?? 'Migrare inclusă', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                    <span class="inline-flex items-center gap-2 rounded-full bg-slate-100 border border-slate-200 px-3 py-1.5 text-xs font-medium text-slate-700">
                        <span class="h-2 w-2 rounded-full bg-ihcOrange"></span> <?php echo htmlspecialchars($h['badge_3'] ?? 'Acces RDP/VPN', ENT_QUOTES, 'UTF-8'); ?>
                    </span>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button onclick="scrollToSection('1c')" class="btn-anim rounded-full bg-ihcBlue px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/35 hover:bg-ihcBlueDark">
                        Detalii hosting 1C
                    </button>
                    <button onclick="scrollToSection('contact')" class="btn-anim rounded-full border-2 border-slate-300 bg-white px-5 py-2.5 text-xs font-semibold uppercase tracking-wider text-slate-700 hover:border-ihcBlue hover:text-ihcBlue">
                        Cere ofertă
                    </button>
                </div>

                <dl class="mt-8 grid grid-cols-3 gap-4 text-xs">
                    <div><dt class="text-slate-400">Experiență</dt><dd class="font-semibold text-slate-800">Migrare 1C</dd></div>
                    <div><dt class="text-slate-400">Suport</dt><dd class="font-semibold text-slate-800">24/7</dd></div>
                    <div><dt class="text-slate-400">Securitate</dt><dd class="font-semibold text-slate-800">Backup & DDoS</dd></div>
                </dl>
            </div>

            <aside class="glass shadow-strong rounded-3xl border border-slate-200/80 p-5 sm:p-6" data-reveal>
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Configurează pachet 1C</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Utilizatori, spațiu, baze.</p>
                    </div>
                    <span class="rounded-full bg-ihcOrange/15 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-ihcOrange"><?php echo htmlspecialchars($site_data['1c']['trial_badge'] ?? 'Test 7 zile', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <div class="space-y-4">
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span>Utilizatori 1C</span><span id="fg-users-val" class="font-semibold text-slate-900">3</span></div>
                        <input id="fg-users" type="range" min="1" max="30" value="1" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span>Spațiu DB (GB)</span><span id="fg-space-val" class="font-semibold text-slate-900">20 GB</span></div>
                        <input id="fg-space" type="range" min="10" max="1000" step="10" value="10" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                    <div>
                        <div class="flex justify-between text-xs text-slate-600 mb-1"><span>Baze 1C</span><span id="fg-inst-val" class="font-semibold text-slate-900">1</span></div>
                        <input id="fg-inst" type="range" min="1" max="20" step="1" value="1" class="w-full h-2 rounded-full accent-ihcBlue">
                    </div>
                </div>
                <div class="mt-5 flex items-end justify-between">
                    <div>
                        <p class="text-[10px] uppercase tracking-widest text-ihcBlue font-semibold">Estimare lunară</p>
                        <p id="fg-price" class="text-2xl font-bold text-slate-900">190 lei <span class="text-sm font-normal text-slate-500">/ lună</span></p>
                    </div>
                </div>
                <button onclick="scrollToSection('contact')" class="btn-anim mt-4 w-full rounded-xl bg-gradient-to-r from-ihcOrange to-ihcBlue py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl">
                    Cere ofertă detaliată
                </button>
                <p class="mt-3 text-[11px] text-slate-500"><?php echo htmlspecialchars($site_data['1c']['migrare_note'] ?? 'Migrare, VPN/RDP și consultanță incluse.', ENT_QUOTES, 'UTF-8'); ?></p>
            </aside>
        </div>
    </section>

    <!-- 1C -->
    <section id="1c" class="border-b border-slate-200/60 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Servicii 1C</p>
            <div class="mt-2 grid gap-10 lg:grid-cols-[1.05fr,1fr] lg:gap-14">
                <div data-reveal>
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight">Datele 1C într-un cloud securizat, în câteva ore</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        Nu mai depinzi de un singur PC sau server din birou. Mutăm bazele 1C într-un datacenter sigur, cu backup și acces criptat.
                    </p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Migrare 1C inclusă</p>
                            <p class="text-xs text-slate-600 mt-1">Baze existente mutate în cloud cu timp minim de oprire.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Acces securizat</p>
                            <p class="text-xs text-slate-600 mt-1">VPN/RDP, parole complexe, politici pe utilizator.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Backup automat</p>
                            <p class="text-xs text-slate-600 mt-1">Copii zilnice, restaurare rapidă.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Scalare</p>
                            <p class="text-xs text-slate-600 mt-1">Mai mulți useri/spațiu fără investiții hardware.</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <p class="text-xs font-bold text-slate-800 mb-2">Servicii incluse:</p>
                        <ul class="list-disc space-y-1 pl-5 text-xs text-slate-600">
                            <li>Configurare domeniu + integrare Cloudflare</li>
                            <li>Instalare și configurare platformă 1C pe server</li>
                            <li>Instalare și configurare baze SQL</li>
                            <li>Conectare pe stații + suport la implementare</li>
                            <li>Management utilizatori și accese</li>
                            <li>Suport tehnic și consultanță</li>
                            <li>Transfer date din baze vechi (clienți, mărfuri)</li>
                        </ul>
                    </div>
                </div>
                <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong" data-reveal>
                    <h3 class="text-sm font-bold text-slate-900">Scenarii hosting 1C</h3>
                    <ul class="mt-4 space-y-4">
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-ihcBlue"></span>
                            <div>
                                <p class="font-semibold text-slate-900">Contabilitate firmă mică</p>
                                <p class="text-xs text-slate-600">3–5 useri, o bază, acces birou/acasă, backup și suport.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-ihcOrange"></span>
                            <div>
                                <p class="font-semibold text-slate-900">Grup de companii</p>
                                <p class="text-xs text-slate-600">Mai multe baze, departamente în orașe diferite.</p>
                            </div>
                        </li>
                        <li class="flex gap-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                            <div>
                                <p class="font-semibold text-slate-900">Integrare CRM / facturare</p>
                                <p class="text-xs text-slate-600">1C cu CRM, facturare online, servicii bancare.</p>
                            </div>
                        </li>
                    </ul>
                    <button onclick="scrollToSection('contact')" class="btn-anim mt-6 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white">
                        Discută cu un consultant 1C
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- TARIFE -->
    <section id="tarife" class="border-b border-slate-200/60 bg-slate-50/70">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Servicii</p>
            <div class="mt-2 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <h2 class="text-3xl font-bold text-slate-900">Găzduire web pentru orice proiect</h2>
                <p class="max-w-md text-sm text-slate-600">Site-uri de prezentare, magazine online, aplicații PHP. Pachete scalabile.</p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <?php
                $hosting_icons = ['H', 'P', 'B'];
                $hosting_subtitle_class = ['text-slate-700', 'text-ihcBlueDark', 'text-slate-700'];
                foreach (($site_data['hosting'] ?? []) as $i => $plan) :
                    $icon = $plan['icon'] ?? $hosting_icons[$i] ?? 'H';
                    $subclass = $hosting_subtitle_class[$i] ?? 'text-slate-700';
                    $features = $plan['features'] ?? [];
                ?>
                <article class="no-select card-anim glass-soft rounded-3xl border border-slate-200 p-5 pb-6" data-reveal>
                    <div class="mb-3 flex items-center gap-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                        <span class="text-xs font-semibold <?php echo $subclass; ?>"><?php echo htmlspecialchars($plan['subtitle'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>
                    <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-xl <?php echo $i === 1 ? 'bg-ihcBlue/10 text-ihcBlueDark' : 'bg-slate-100 text-ihcBlue'; ?> text-sm font-bold"><?php echo htmlspecialchars($icon, ENT_QUOTES, 'UTF-8'); ?></div>
                    <h3 class="text-lg font-bold text-slate-900"><?php echo htmlspecialchars($plan['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p class="mt-1 text-sm text-slate-600"><?php echo htmlspecialchars($plan['description'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-700">
                        <?php foreach ($features as $f) : ?>
                        <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> <?php echo htmlspecialchars($f, ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="mt-5">
                        <p class="text-xs text-slate-500">De la</p>
                        <p class="text-2xl font-bold text-slate-900"><?php echo (int)($plan['price'] ?? 0); ?> lei <span class="text-sm font-normal text-slate-500">/ lună</span></p>
                        <p class="text-[11px] text-slate-500"><?php echo htmlspecialchars($plan['price_note'] ?? '', ENT_QUOTES, 'UTF-8'); ?><?php if (!empty($plan['price_note_alt'])) : ?> <span class="text-slate-400"><?php echo htmlspecialchars($plan['price_note_alt'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?></p>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- VPS -->
    <section id="vps" class="border-b border-slate-200/60 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Servere virtuale</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900">VPS / VDS pe SSD NVMe în Europa</h2>
            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <div class="grid gap-3 sm:grid-cols-2" data-reveal>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900">KVM & virtualizare</h3>
                        <p class="text-xs text-slate-600 mt-1">Linux sau Windows, acces complet.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900">Resurse garantate</h3>
                        <p class="text-xs text-slate-600 mt-1">RAM/CPU dedicate, scalare rapidă.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900">Datacentere UE</h3>
                        <p class="text-xs text-slate-600 mt-1">Latență mică, conexiuni redundante.</p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4 hover:border-ihcBlue/30 hover:shadow transition">
                        <h3 class="text-sm font-bold text-slate-900">Management opțional</h3>
                        <p class="text-xs text-slate-600 mt-1">Update, securitate, monitorizare 24/7.</p>
                    </div>
                </div>
                <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong" data-reveal>
                    <h3 class="text-sm font-bold text-slate-900 mb-3">Configurații VPS</h3>
                    <ul class="space-y-3">
                        <?php
                        $vps_colors = ['bg-ihcBlue', 'bg-ihcOrange', 'bg-emerald-500'];
                        foreach (($site_data['vps'] ?? []) as $vi => $v) :
                            $dot = $vps_colors[$vi] ?? 'bg-ihcBlue';
                        ?>
                        <li class="flex gap-3 rounded-xl border border-slate-200 bg-white p-3">
                            <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full <?php echo $dot; ?>"></span>
                            <div><p class="font-semibold text-slate-900"><?php echo htmlspecialchars($v['name'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p><p class="text-xs text-slate-600"><?php echo htmlspecialchars($v['specs'] ?? '', ENT_QUOTES, 'UTF-8'); ?> – de la <?php echo (int)($v['price'] ?? 0); ?> lei/lună</p></div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="mt-3 text-[11px] text-slate-600">IP dedicat, firewall, ispmanager, cPanel, Plesk.</p>
                    <button onclick="scrollToSection('contact')" class="btn-anim mt-4 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white">
                        Cere ofertă personalizată
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- AUTOMATIZARE ZAPIER -->
    <section id="zapier" class="border-b border-slate-200/60 bg-slate-50/70">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Automatizări</p>
            <div class="mt-2 grid gap-10 lg:grid-cols-[1.05fr,1fr] lg:gap-14">
                <div data-reveal>
                    <h2 class="text-3xl font-bold text-slate-900 leading-tight">Automatizare Zapier — fluxuri de email și procese recurente</h2>
                    <p class="mt-4 text-slate-600 leading-relaxed">
                        Pentru companii care trebuie să transmită periodic un flux de emailuri — de exemplu acte de verificare, solicitări către parteneri sau clienți, raporturi — acest flux poate fi automatizat. Configurăm Zapier astfel încât trimiterile să se declanșeze la intervalul stabilit, fără intervenție manuală.
                    </p>
                    <div class="mt-6 grid gap-3 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Fluxuri periodice</p>
                            <p class="text-xs text-slate-600 mt-1">Acte de verificare, solicitări, mementouri trimise automat la data sau la intervalul stabilit.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white/80 p-4 transition hover:border-ihcBlue/40 hover:shadow-md">
                            <p class="text-sm font-bold text-slate-900">Integrări</p>
                            <p class="text-xs text-slate-600 mt-1">Conectăm Gmail, Outlook, CRM, foi de calcul și alte aplicații în fluxuri clare și ușor de modificat.</p>
                        </div>
                    </div>
                    <ul class="mt-6 list-disc space-y-1 pl-5 text-sm text-slate-600">
                        <li>Configurare Zapier pentru trimiteri automate de emailuri</li>
                        <li>Fluxuri la perioadă fixă sau declanșate de evenimente</li>
                        <li>Șabloane și personalizare pentru fiecare tip de solicitare</li>
                    </ul>
                </div>
                <div class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong" data-reveal>
                    <h3 class="text-sm font-bold text-slate-900">Exemplu de utilizare</h3>
                    <p class="mt-3 text-sm text-slate-600">
                        O firmă trebuie să ceară lunar de la parteneri acte de verificare și documente. În loc să trimită manual zeci de emailuri, configurăm un Zap care, la o anumită dată sau la un semnal (ex. intrare în lună nouă), trimite automat solicitările către lista definită, cu șablon personalizat. Economisești timp și reduci erorile omise.
                    </p>
                    <button onclick="scrollToSection('contact')" class="btn-anim mt-6 w-full rounded-xl border-2 border-ihcBlue bg-white py-2.5 text-xs font-bold uppercase tracking-wider text-ihcBlue hover:bg-ihcBlue hover:text-white">
                        Cere ofertă pentru automatizare
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- DOMENII -->
    <section id="domenii" class="border-b border-slate-200/60 bg-slate-50/70">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Domenii</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900">Verifică disponibilitatea domeniului</h2>
            <div class="mt-6 glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong" data-reveal>
                <p class="text-sm text-slate-600">Introdu domeniul dorit; îți răspundem cu disponibilitatea și oferta.</p>
                <form class="mt-4 flex flex-col gap-3 sm:flex-row" onsubmit="handleDomainCheck(event)">
                    <input id="domain-input" type="text" placeholder="exemplu.ro" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20">
                    <button type="submit" class="btn-anim rounded-xl bg-ihcBlue px-5 py-2.5 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:bg-ihcBlueDark">
                        Verifică domeniu
                    </button>
                </form>
                <p id="domain-result" class="mt-3 text-xs text-slate-600"></p>
                <div class="mt-4 grid gap-2 sm:grid-cols-4 text-xs">
                    <?php foreach (($site_data['domains'] ?? []) as $d) : ?>
                    <div class="flex justify-between rounded-xl border border-slate-200 bg-white px-3 py-2"><span class="font-semibold text-slate-900"><?php echo htmlspecialchars($d['tld'] ?? '', ENT_QUOTES, 'UTF-8'); ?></span><span class="text-slate-500"><?php echo (int)($d['price'] ?? 0); ?> lei/an</span></div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT -->
    <section id="contact" class="bg-white">
        <div class="mx-auto max-w-6xl px-4 py-16 lg:px-8 lg:py-20">
            <p class="section-kicker text-xs font-bold uppercase text-ihcBlue">Contact</p>
            <h2 class="mt-2 text-3xl font-bold text-slate-900">Mutăm 1C și site-urile tale pe un hosting mai bun</h2>
            <div class="mt-8 grid gap-8 lg:grid-cols-2">
                <?php $contact = $site_data['contact'] ?? []; ?>
                <div class="grid gap-3 sm:grid-cols-2 text-sm" data-reveal>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-xs font-bold text-slate-900">Email vânzări</p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['email_sales'] ?? 'sales@ondsolutions.md', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-xs font-bold text-slate-900">Suport</p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['email_support'] ?? 'support@ondsolutions.md', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-xs font-bold text-slate-900">Telefon</p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                    <div class="rounded-2xl border border-slate-200 bg-slate-50/80 p-4">
                        <p class="text-xs font-bold text-slate-900">Program</p>
                        <p class="text-slate-600"><?php echo htmlspecialchars($contact['schedule'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                    </div>
                </div>

                <form method="post" action="#contact" class="glass-soft rounded-3xl border border-slate-200 p-5 shadow-strong space-y-4" data-reveal>
                    <?php if (!empty($contact_success)) : ?>
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700"><?php echo htmlspecialchars($contact_success, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php elseif (!empty($contact_error)) : ?>
                        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"><?php echo htmlspecialchars($contact_error, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800">Nume complet</label>
                        <input type="text" name="nume" placeholder="Numele tău" value="<?php echo isset($nume) ? htmlspecialchars($nume, ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800">Email</label>
                        <input type="email" name="email" placeholder="email" value="<?php echo isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-800">Mesaj</label>
                        <textarea name="mesaj" placeholder="Lasă-ne un mesaj aici..." rows="4" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm focus:border-ihcBlue focus:ring-2 focus:ring-ihcBlue/20"><?php echo isset($mesaj) ? htmlspecialchars($mesaj, ENT_QUOTES, 'UTF-8') : ''; ?></textarea>
                    </div>
                    <button type="submit" class="btn-anim w-full rounded-xl bg-gradient-to-r from-ihcOrange to-ihcBlue py-3 text-xs font-bold uppercase tracking-wider text-white shadow-lg shadow-ihcBlue/30 hover:shadow-xl">
                        Trimite cererea
                    </button>
                    <p class="text-[11px] text-slate-500">Prin trimitere ești de acord cu prelucrarea datelor conform politicii de confidențialitate.</p>
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
                <div class="flex items-center gap-2 mb-2">
                    <div class="logo-glow flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold text-white">S</div>
                    <span class="text-xs font-bold uppercase tracking-widest text-slate-800"><?php echo htmlspecialchars($company['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></span>
                </div>
                <p class="text-xs text-slate-600 max-w-sm"><?php echo htmlspecialchars($company['footer_text'] ?? 'Găzduire web, VPS/VDS, hosting 1C și domenii pentru companii și proiecte din România și Europa.', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-800 mb-2">Servicii</p>
                <div class="space-y-1 text-xs"><a href="#1c" class="block text-slate-600 hover:text-ihcBlue">Hosting 1C</a><a href="#tarife" class="block text-slate-600 hover:text-ihcBlue">Găzduire</a><a href="#vps" class="block text-slate-600 hover:text-ihcBlue">VPS / VDS</a><a href="#zapier" class="block text-slate-600 hover:text-ihcBlue">Automatizări Zapier</a><a href="#domenii" class="block text-slate-600 hover:text-ihcBlue">Domenii</a></div>
            </div>
            <div>
                <p class="text-xs font-bold text-slate-800 mb-2">Companie</p>
                <div class="space-y-1 text-xs"><a href="#" class="block text-slate-600 hover:text-ihcBlue">Despre noi</a><a href="#" class="block text-slate-600 hover:text-ihcBlue">Confidențialitate</a><a href="#" class="block text-slate-600 hover:text-ihcBlue">Termeni</a></div>
            </div>
        </div>
        <div class="mt-6 pt-6 border-t border-slate-200 flex flex-col gap-2 text-xs text-slate-500 sm:flex-row sm:justify-between">
            <span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($company['copyright'] ?? 'OND SOLUTIONS SRL', ENT_QUOTES, 'UTF-8'); ?>.</span>
            <span>Plată: card, transfer bancar, facturare.</span>
        </div>
    </div>
</footer>

<script>
    var siteData1C = <?php echo json_encode($site_data['1c'] ?? ['base_price'=>330,'per_user'=>30,'per_gb_factor'=>0.08,'per_instance'=>30,'min_price'=>150]); ?>;
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
        document.getElementById('fg-space-val').textContent = space + ' GB';
        document.getElementById('fg-inst-val').textContent = inst;
        var c = siteData1C;
        var base = (c.base_price || 330) + (users - 1) * (c.per_user || 30) + (space - 10) / 100 * (c.per_gb_factor || 0.08) * 100 + (inst - 1) * (c.per_instance || 30);
        var price = Math.max(c.min_price || 150, Math.round(base));
        document.getElementById('fg-price').innerHTML = price + ' lei <span class="text-sm font-normal text-slate-500">/ lună</span>';
    }
    ['fg-users', 'fg-space', 'fg-inst'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', calcPrice1C);
    });
    calcPrice1C();

    function handleDomainCheck(e) {
        e.preventDefault();
        var input = document.getElementById('domain-input');
        var result = document.getElementById('domain-result');
        var value = (input.value || '').trim();
        if (!value) { result.textContent = 'Introdu un nume de domeniu.'; return; }
        result.textContent = 'Am trimis „' + value + '” către host.md. Vezi rezultatul în tab-ul deschis.';
        window.open('https://www.host.md/?pag=domains&domain=' + encodeURIComponent(value), '_blank');
    }

    (function() {
        var reveals = document.querySelectorAll('[data-reveal]');
        function reveal() {
            reveals.forEach(function(el) {
                var top = el.getBoundingClientRect().top;
                var win = window.innerHeight - 80;
                if (top < win) el.classList.add('visible');
            });
        }
        window.addEventListener('scroll', reveal);
        window.addEventListener('load', reveal);
        reveal();
    })();
</script>
</body>
</html>
