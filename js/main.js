// GadgetZone - Main JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // NAVBAR SCROLL EFFECT
    // ============================================================
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    }

    // ============================================================
    // MOBILE MENU
    // ============================================================
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu');
    const mobileOverlay = document.querySelector('.mobile-overlay');
    const closeMenu = document.querySelector('.close-menu');

    function openMobileMenu() {
        mobileMenu && mobileMenu.classList.add('open');
        mobileOverlay && mobileOverlay.classList.add('show');
        hamburger && hamburger.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileMenu() {
        mobileMenu && mobileMenu.classList.remove('open');
        mobileOverlay && mobileOverlay.classList.remove('show');
        hamburger && hamburger.classList.remove('active');
        document.body.style.overflow = '';
    }

    hamburger && hamburger.addEventListener('click', openMobileMenu);
    closeMenu && closeMenu.addEventListener('click', closeMobileMenu);
    mobileOverlay && mobileOverlay.addEventListener('click', closeMobileMenu);

    // ============================================================
    // FLASH SALE COUNTDOWN
    // ============================================================
    const countdownEl = document.querySelector('.countdown');
    if (countdownEl) {
        // Default: 4 hours from now
        const endTime = new Date().getTime() + (4 * 60 * 60 * 1000);

        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance <= 0) {
                countdownEl.innerHTML = '<span class="time-unit">SALE ENDED</span>';
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const hoursEl = countdownEl.querySelector('[data-unit="hours"]');
            const minutesEl = countdownEl.querySelector('[data-unit="minutes"]');
            const secondsEl = countdownEl.querySelector('[data-unit="seconds"]');

            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // ============================================================
    // TOAST NOTIFICATIONS
    // ============================================================
    window.showToast = function (message, type = 'success') {
        const existing = document.querySelector('.toast');
        if (existing) existing.remove();

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    };

    // ============================================================
    // ADD TO CART (AJAX)
    // ============================================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-add-cart, [data-action="add-to-cart"]');
        if (!btn) return;

        e.preventDefault();
        const productId = btn.dataset.id || btn.closest('[data-product-id]')?.dataset.productId;
        if (!productId) return;

        const quantity = parseInt(document.querySelector('.qty-input')?.value || 1);
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Adding...';

        fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'add', product_id: productId, quantity })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updateCartBadge(data.cart_count);
                    showToast('Added to cart! 🛒', 'success');
                } else {
                    showToast(data.message || 'Could not add to cart.', 'error');
                }
            })
            .catch(() => showToast('Network error. Please try again.', 'error'))
            .finally(() => {
                btn.disabled = false;
                btn.textContent = originalText;
            });
    });

    // ============================================================
    // WISHLIST TOGGLE (AJAX)
    // ============================================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action="toggle-wishlist"]');
        if (!btn) return;
        e.preventDefault();

        const productId = btn.dataset.id;
        fetch('api/wishlist.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'toggle', product_id: productId })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('active', data.in_wishlist);
                    showToast(data.in_wishlist ? 'Added to wishlist ❤️' : 'Removed from wishlist', 'info');
                }
            })
            .catch(() => showToast('Please log in to use wishlist.', 'error'));
    });

    // ============================================================
    // UPDATE CART BADGE
    // ============================================================
    function updateCartBadge(count) {
        const badge = document.querySelector('.cart-badge');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'flex' : 'none';
        }
    }

    // ============================================================
    // CART QUANTITY CONTROLS (cart page)
    // ============================================================
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.qty-btn');
        if (!btn) return;
        const input = btn.closest('.qty-control')?.querySelector('.qty-input');
        if (!input) return;
        const val = parseInt(input.value) || 1;
        if (btn.dataset.action === 'increase') {
            input.value = val + 1;
        } else if (btn.dataset.action === 'decrease' && val > 1) {
            input.value = val - 1;
        }
        input.dispatchEvent(new Event('change'));
    });

    // ============================================================
    // NEWSLETTER FORM
    // ============================================================
    const newsletterForms = document.querySelectorAll('.newsletter-form, .footer-newsletter');
    newsletterForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = form.querySelector('input[type="email"]');
            if (!input || !input.value) return;

            fetch('api/newsletter.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: input.value })
            })
                .then(r => r.json())
                .then(data => {
                    showToast(data.message || 'Subscribed successfully!', data.success ? 'success' : 'error');
                    if (data.success) input.value = '';
                })
                .catch(() => showToast('Subscription failed. Try again.', 'error'));
        });
    });

    // ============================================================
    // SEARCH FORM SUBMIT
    // ============================================================
    const searchForm = document.querySelector('.search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            const input = searchForm.querySelector('.search-input');
            if (!input?.value.trim()) {
                e.preventDefault();
                input?.focus();
            }
        });
    }

    // ============================================================
    // LAZY LOAD IMAGES
    // ============================================================
    if ('IntersectionObserver' in window) {
        const images = document.querySelectorAll('img[data-src]');
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    imageObserver.unobserve(img);
                }
            });
        });
        images.forEach(img => imageObserver.observe(img));
    }

    // ============================================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const offset = 80;
                const top = target.getBoundingClientRect().top + window.scrollY - offset;
                window.scrollTo({ top, behavior: 'smooth' });
            }
        });
    });

    // ============================================================
    // REGISTER SERVICE WORKER (PWA)
    // ============================================================
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/2brodastech1/service-worker.js')
            .catch(err => console.warn('SW registration failed:', err));
    }

});
