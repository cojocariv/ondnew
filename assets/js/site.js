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

    /* Process timeline — left-to-right storytelling */
    var processTimeline = document.querySelector('.process-timeline');
    if (processTimeline) {
        var processSteps = processTimeline.querySelectorAll('.process-step');
        var processReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var processTourTimer = null;

        function setProcessHighlight(index) {
            processSteps.forEach(function (step, i) {
                step.classList.toggle('is-active', i === index);
                step.classList.toggle('is-passed', i < index);
            });
        }

        function finishProcessTour() {
            processSteps.forEach(function (step) {
                step.classList.remove('is-active');
                step.classList.add('is-passed');
            });
            processTimeline.classList.add('is-complete');
        }

        function runProcessTour() {
            var idx = 0;
            var stepMs = 1600;

            function tick() {
                if (idx >= processSteps.length) {
                    finishProcessTour();
                    return;
                }
                setProcessHighlight(idx);
                idx += 1;
                processTourTimer = setTimeout(tick, stepMs);
            }

            processTourTimer = setTimeout(tick, 900);
        }

        function startProcessAnimation() {
            processTimeline.classList.add('is-animated');
            if (processReducedMotion) {
                processSteps.forEach(function (step) { step.classList.add('is-passed'); });
                processTimeline.classList.add('is-complete');
                return;
            }
            runProcessTour();
        }

        if (processReducedMotion) {
            startProcessAnimation();
        } else {
            var processObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    processObserver.unobserve(processTimeline);
                    startProcessAnimation();
                });
            }, { threshold: 0.2, rootMargin: '0px 0px -40px 0px' });
            processObserver.observe(processTimeline);
        }
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

    /* Chat widget */
    var chatWidget = document.getElementById('chat-widget');
    if (chatWidget) {
        var chatToggle = document.getElementById('chat-widget-toggle');
        var chatPanel = document.getElementById('chat-widget-panel');

        function setChatOpen(open) {
            if (!chatToggle || !chatPanel) return;
            chatToggle.classList.toggle('is-open', open);
            chatToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            chatPanel.hidden = !open;
        }

        if (chatToggle && chatPanel) {
            chatToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                setChatOpen(chatPanel.hidden);
            });

            document.addEventListener('click', function (e) {
                if (!chatWidget.contains(e.target)) {
                    setChatOpen(false);
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setChatOpen(false);
            });
        }
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

    /* 1C hosting price calculator */
    var usersEl = document.getElementById('fg-users');
    if (usersEl) {
        var i18n = window.i18nPrice || { currency: 'lei', perMonth: '/ lună', gb: 'GB' };
        var c = window.siteData1C || {};

        function calcPrice1C() {
            var users = parseInt(document.getElementById('fg-users').value, 10);
            var space = parseInt(document.getElementById('fg-space').value, 10);
            var inst = parseInt(document.getElementById('fg-inst').value, 10);
            document.getElementById('fg-users-val').textContent = users;
            document.getElementById('fg-space-val').textContent = space + ' ' + i18n.gb;
            document.getElementById('fg-inst-val').textContent = inst;
            var base = (c.base_price || 330) + (users - 1) * (c.per_user || 30)
                + (space - 10) / 100 * (c.per_gb_factor || 0.08) * 100
                + (inst - 1) * (c.per_instance || 30);
            var price = Math.max(c.min_price || 150, Math.round(base));
            document.getElementById('fg-price').innerHTML = price + ' ' + i18n.currency
                + ' <span>' + i18n.perMonth + '</span>';
        }

        ['fg-users', 'fg-space', 'fg-inst'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('input', calcPrice1C);
        });
        calcPrice1C();
    }

    /* 1C scenario modal */
    var scenarioModal = document.getElementById('scenario-modal');
    var scenarioDetails = window.scenarioDetails || {};
    if (scenarioModal) {
        var titleEl = document.getElementById('scenario-modal-title');
        var leadEl = document.getElementById('scenario-modal-lead');
        var listEl = document.getElementById('scenario-modal-list');
        var dotEl = document.getElementById('scenario-modal-dot');
        var lastFocus = null;

        function closeScenarioModal() {
            scenarioModal.classList.remove('is-open');
            scenarioModal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (lastFocus) lastFocus.focus();
        }

        function openScenarioModal(key) {
            var data = scenarioDetails[key];
            if (!data) return;
            lastFocus = document.activeElement;
            dotEl.className = 'hosting-1c-scenario-dot scenario-modal-dot ' + data.dotClass;
            titleEl.textContent = data.title;
            leadEl.textContent = data.lead;
            listEl.innerHTML = data.bullets.map(function (item) {
                return '<li>' + item + '</li>';
            }).join('');
            scenarioModal.classList.add('is-open');
            scenarioModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            scenarioModal.querySelector('.scenario-modal-close').focus();
        }

        document.querySelectorAll('[data-scenario]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openScenarioModal(btn.getAttribute('data-scenario'));
            });
        });

        document.querySelectorAll('[data-scenario-close]').forEach(function (el) {
            el.addEventListener('click', closeScenarioModal);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && scenarioModal.classList.contains('is-open')) {
                closeScenarioModal();
            }
        });
    }
})();
