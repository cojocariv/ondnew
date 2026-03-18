<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="navbar">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 lg:px-8">
        <a href="#hero" class="flex items-center gap-2">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-blue to-cyan-500 text-white font-bold shadow-lg shadow-primary-blue/30">S</div>
            <span class="font-semibold text-slate-900"><?php echo htmlspecialchars($site_data['company']['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></span>
        </a>
        <div class="hidden items-center gap-8 md:flex">
            <a href="#services" class="nav-link text-sm font-medium text-slate-600 hover:text-primary-blue"><?php echo htmlspecialchars(t('nav_services', 'Servicii'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#implementations" class="nav-link text-sm font-medium text-slate-600 hover:text-primary-blue"><?php echo htmlspecialchars(t('nav_implementations', 'Implementări'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#pricing" class="nav-link text-sm font-medium text-slate-600 hover:text-primary-blue"><?php echo htmlspecialchars(t('nav_pricing', 'Prețuri'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#about" class="nav-link text-sm font-medium text-slate-600 hover:text-primary-blue"><?php echo htmlspecialchars(t('nav_about', 'Despre noi'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#contact" class="nav-link text-sm font-medium text-slate-600 hover:text-primary-blue"><?php echo htmlspecialchars(t('nav_contact', 'Contact'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
        <div class="flex items-center gap-3">
            <a href="?lang=ro" class="text-sm font-medium <?php echo ($lang ?? 'ro') === 'ro' ? 'text-primary-blue' : 'text-slate-500 hover:text-slate-700'; ?>">RO</a>
            <span class="text-slate-300">|</span>
            <a href="?lang=ru" class="text-sm font-medium <?php echo ($lang ?? 'ro') === 'ru' ? 'text-primary-blue' : 'text-slate-500 hover:text-slate-700'; ?>">RU</a>
            <a href="client/" class="rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:border-primary-blue hover:text-primary-blue hover:bg-slate-50"><?php echo htmlspecialchars(t('nav_client_area', 'Cabinet client'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#contact" class="rounded-lg bg-primary-blue px-4 py-2.5 text-sm font-semibold text-white shadow-primary transition-all duration-200 hover:scale-[1.02] hover:shadow-primary-lg active:scale-[0.98]"><?php echo htmlspecialchars(t('btn_request_quote', 'Solicită ofertă'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </nav>
</header>
