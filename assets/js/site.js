(function () {
    'use strict';

    function scrollToSection(id) {
        var el = document.getElementById(id);
        if (!el) return;
        var top = el.getBoundingClientRect().top + window.pageYOffset - 80;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }
    window.scrollToSection = scrollToSection;

    /* Header scroll state */
    var header = document.getElementById('site-header');
    var hero = document.querySelector('.hero');

    function onScroll() {
        var y = window.scrollY;
        if (header) {
            header.classList.toggle('is-scrolled', y > 40);
            if (hero) {
                var heroBottom = hero.offsetHeight - 100;
                header.classList.toggle('is-dark', y < heroBottom);
            }
        }
        var backToTop = document.getElementById('back-to-top');
        if (backToTop) backToTop.classList.toggle('is-visible', y > 400);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* Mobile menu */
    var menuBtn = document.getElementById('mobile-menu-btn');
    var mobileNav = document.getElementById('mobile-nav');
    var openLabel = menuBtn ? menuBtn.getAttribute('data-open-label') : '';
    var closeLabel = menuBtn ? menuBtn.getAttribute('data-close-label') : '';

    function setMenu(open) {
        if (!menuBtn || !mobileNav) return;
        menuBtn.classList.toggle('is-open', open);
        menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        menuBtn.setAttribute('aria-label', open ? closeLabel : openLabel);
        mobileNav.classList.toggle('is-open', open);
        mobileNav.hidden = !open;
        document.body.style.overflow = open ? 'hidden' : '';
    }

    if (menuBtn) {
        menuBtn.addEventListener('click', function () {
            setMenu(!mobileNav.classList.contains('is-open'));
        });
    }
    document.querySelectorAll('[data-mobile-nav]').forEach(function (link) {
        link.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') setMenu(false);
    });

    /* Scroll reveal */
    var reveals = document.querySelectorAll('.reveal');
    if (reveals.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        reveals.forEach(function (el) { revealObserver.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* Animated counters */
    var counters = document.querySelectorAll('[data-counter]');
    if (counters.length) {
        var counterObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                var target = parseFloat(el.getAttribute('data-counter')) || 0;
                var suffix = el.getAttribute('data-suffix') || '';
                var prefix = el.getAttribute('data-prefix') || '';
                var decimals = parseInt(el.getAttribute('data-decimals') || '0', 10);
                var duration = 1800;
                var start = performance.now();

                function tick(now) {
                    var progress = Math.min((now - start) / duration, 1);
                    var eased = 1 - Math.pow(1 - progress, 3);
                    var value = target * eased;
                    el.textContent = prefix + (decimals ? value.toFixed(decimals) : Math.round(value)) + suffix;
                    if (progress < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
                counterObserver.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { counterObserver.observe(el); });
    }

    /* Parallax */
    var parallaxLayers = document.querySelectorAll('[data-parallax]');
    if (parallaxLayers.length && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        window.addEventListener('scroll', function () {
            var y = window.scrollY;
            parallaxLayers.forEach(function (layer) {
                var speed = parseFloat(layer.getAttribute('data-parallax')) || 0.3;
                var rect = layer.parentElement.getBoundingClientRect();
                if (rect.top < window.innerHeight && rect.bottom > 0) {
                    layer.style.transform = 'translateY(' + (y * speed * 0.15) + 'px)';
                }
            });
        }, { passive: true });
    }

    /* Back to top */
    var backToTop = document.getElementById('back-to-top');
    if (backToTop) {
        backToTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* Hero scroll hint */
    var scrollHint = document.querySelector('[data-scroll-hint]');
    if (scrollHint) {
        scrollHint.addEventListener('click', function () {
            scrollToSection('trust');
        });
    }
})();
