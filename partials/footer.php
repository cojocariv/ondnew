<footer class="border-t border-slate-200/80 bg-slate-900 text-slate-300">
    <div class="mx-auto max-w-7xl px-4 py-12 lg:px-8">
        <div class="grid gap-8 md:grid-cols-2 lg:grid-cols-4">
            <div class="lg:col-span-2">
                <a href="#hero" class="inline-flex items-center gap-2 mb-4">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-primary-blue to-cyan-500 text-white font-bold text-sm">S</div>
                    <span class="font-semibold text-white"><?php echo htmlspecialchars($site_data['company']['name'] ?? 'Smart Solutions', ENT_QUOTES, 'UTF-8'); ?></span>
                </a>
                <p class="text-sm text-slate-400 max-w-md"><?php echo htmlspecialchars($site_data['company']['footer_text'] ?? 'Găzduire web, VPS/VDS, hosting 1C și domenii pentru companii și proiecte.', ENT_QUOTES, 'UTF-8'); ?></p>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-white mb-3"><?php echo htmlspecialchars(t('footer_services', 'Servicii'), ENT_QUOTES, 'UTF-8'); ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#services" class="hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars(t('nav_services', 'Servicii'), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li><a href="#implementations" class="hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars(t('nav_implementations', 'Implementări'), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li><a href="#pricing" class="hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars(t('nav_pricing', 'Prețuri'), ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li><a href="#contact" class="hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars(t('nav_contact', 'Contact'), ENT_QUOTES, 'UTF-8'); ?></a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-white mb-3"><?php echo htmlspecialchars(t('footer_contact', 'Contact'), ENT_QUOTES, 'UTF-8'); ?></h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="mailto:<?php echo htmlspecialchars($site_data['contact']['email_sales'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" class="hover:text-cyan-400 transition-colors"><?php echo htmlspecialchars($site_data['contact']['email_sales'] ?? 'sales@ondsolutions.md', ENT_QUOTES, 'UTF-8'); ?></a></li>
                    <li><?php echo htmlspecialchars($site_data['contact']['phone'] ?? '', ENT_QUOTES, 'UTF-8'); ?></li>
                </ul>
            </div>
        </div>
        <div class="mt-10 pt-8 border-t border-slate-700 flex flex-col gap-2 text-xs text-slate-500 sm:flex-row sm:justify-between">
            <span>© <?php echo date('Y'); ?> <?php echo htmlspecialchars($site_data['company']['copyright'] ?? 'OND SOLUTIONS SRL', ENT_QUOTES, 'UTF-8'); ?></span>
            <span><?php echo htmlspecialchars(t('footer_payment', 'Plată: card, transfer bancar, facturare.'), ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>
</footer>
