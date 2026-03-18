<section id="hero" class="relative min-h-[90vh] flex items-center justify-center overflow-hidden px-4 pt-24 pb-16">
    <div class="hero-gradient absolute inset-0"></div>
    <div class="hero-grid absolute inset-0 opacity-[0.03]"></div>
    <div class="floating-shapes">
        <span class="shape shape-1"></span>
        <span class="shape shape-2"></span>
        <span class="shape shape-3"></span>
        <span class="shape shape-4"></span>
        <span class="shape shape-5"></span>
    </div>
    <div class="relative z-10 mx-auto max-w-5xl text-center">
        <p class="reveal mb-4 text-sm font-semibold uppercase tracking-widest text-cyan-500"><?php echo htmlspecialchars(t('hero_kicker', 'Infrastructură cloud & soluții IT'), ENT_QUOTES, 'UTF-8'); ?></p>
        <h1 class="reveal hero-title mb-6 text-4xl font-bold leading-[1.1] tracking-tight text-slate-900 sm:text-5xl md:text-6xl lg:text-7xl">
            <?php echo htmlspecialchars(t('hero_title1', 'Cloud Infrastructure &'), ENT_QUOTES, 'UTF-8'); ?><br>
            <span class="bg-gradient-to-r from-primary-blue to-cyan-500 bg-clip-text text-transparent"><?php echo htmlspecialchars(t('hero_title2', 'IT Solutions'), ENT_QUOTES, 'UTF-8'); ?></span> <?php echo htmlspecialchars(t('hero_title3', 'for Modern Business'), ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p class="reveal mx-auto mb-8 max-w-2xl text-lg text-slate-600 sm:text-xl">
            <?php echo htmlspecialchars(t('hero_desc', 'Infrastructură de servere securizată...'), ENT_QUOTES, 'UTF-8'); ?>
        </p>
        <div class="reveal flex flex-wrap items-center justify-center gap-4">
            <a href="#contact" class="rounded-xl bg-primary-blue px-6 py-3.5 text-sm font-semibold text-white shadow-primary transition-all duration-200 hover:scale-[1.03] hover:shadow-primary-lg active:scale-[0.98]"><?php echo htmlspecialchars(t('btn_consult', 'Solicită consultanță'), ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="#services" class="btn-secondary rounded-xl border-2 border-slate-300 bg-white px-6 py-3.5 text-sm font-semibold text-slate-700 transition-all duration-200 hover:border-primary-blue hover:text-primary-blue hover:scale-[1.02] active:scale-[0.98]"><?php echo htmlspecialchars(t('btn_see_services', 'Vezi servicii'), ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
        <div class="reveal mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-slate-500">
            <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-emerald-500"></span> <?php echo htmlspecialchars(t('badge_uptime', 'Uptime 99,9%+'), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-primary-blue"></span> <?php echo htmlspecialchars(t('badge_migration', 'Migrare inclusă'), ENT_QUOTES, 'UTF-8'); ?></span>
            <span class="flex items-center gap-2"><span class="h-2 w-2 rounded-full bg-cyan-500"></span> <?php echo htmlspecialchars(t('badge_rdp', 'Acces RDP/VPN'), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</section>
