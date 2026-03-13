(function() {
    'use strict';

    function scrollToSection(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    window.scrollToSection = scrollToSection;

    var navbar = document.getElementById('navbar');
    if (navbar) {
        function onScroll() {
            navbar.classList.toggle('scrolled', window.scrollY > 40);
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    var reveals = document.querySelectorAll('.reveal, [data-reveal]');
    function reveal() {
        var winH = window.innerHeight;
        var threshold = winH * 0.85;
        reveals.forEach(function(el) {
            var top = el.getBoundingClientRect().top;
            if (top < threshold) el.classList.add('visible');
        });
    }
    window.addEventListener('scroll', reveal, { passive: true });
    window.addEventListener('load', reveal);
})();
