(function () {
    'use strict';

    var MAIN_SITE = 'https://smartsolutions.md';
    var LOGO_URL = MAIN_SITE + '/assets/LOGO/transparent%20logo.png';

    function injectTopbar() {
        if (document.querySelector('.ss-shop-topbar')) return;

        var bar = document.createElement('div');
        bar.className = 'ss-shop-topbar';
        bar.innerHTML =
            '<div class="ss-shop-topbar-inner">' +
                '<a href="' + MAIN_SITE + '">← Smart Solutions</a>' +
                '<span>Magazin online</span>' +
            '</div>';
        document.body.insertBefore(bar, document.body.firstChild);
    }

    function useMainLogo() {
        var logo = document.querySelector('#logo img');
        if (!logo) return;
        if (logo.dataset.ssLogoApplied === '1') return;
        logo.src = LOGO_URL;
        logo.alt = 'Smart Solutions';
        logo.dataset.ssLogoApplied = '1';
    }

    function onScroll() {
        document.body.classList.toggle('is-scrolled', window.scrollY > 24);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.body.classList.add('ss-shop');
        injectTopbar();
        useMainLogo();
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
    });
})();
