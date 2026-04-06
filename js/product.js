// GadgetZone - Product Page JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // PRODUCT IMAGE GALLERY
    // ============================================================
    const mainImage = document.querySelector('.main-image-wrap img');
    const thumbnails = document.querySelectorAll('.thumbnail-img');

    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function () {
            if (!mainImage) return;
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            mainImage.src = this.dataset.full || this.src;
            mainImage.alt = this.alt;
        });
    });

    // ============================================================
    // IMAGE ZOOM ON HOVER
    // ============================================================
    const mainImageWrap = document.querySelector('.main-image-wrap');
    if (mainImageWrap && mainImage) {
        mainImageWrap.addEventListener('mousemove', function (e) {
            const rect = this.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            mainImage.style.transformOrigin = `${x}% ${y}%`;
            mainImage.style.transform = 'scale(1.5)';
            mainImage.style.cursor = 'zoom-in';
        });
        mainImageWrap.addEventListener('mouseleave', function () {
            mainImage.style.transform = 'scale(1)';
            mainImage.style.cursor = 'zoom-in';
        });
    }

    // ============================================================
    // STORAGE / VARIANT SELECTION
    // ============================================================
    document.querySelectorAll('.storage-option').forEach(opt => {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.storage-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
            updateProductPrice();
        });
    });

    document.querySelectorAll('.color-option').forEach(opt => {
        opt.addEventListener('click', function () {
            document.querySelectorAll('.color-option').forEach(o => o.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ============================================================
    // UPDATE PRICE ON VARIANT CHANGE
    // ============================================================
    function updateProductPrice() {
        const activeStorage = document.querySelector('.storage-option.active');
        if (!activeStorage) return;
        const variantPrice = activeStorage.dataset.price;
        const variantOriginal = activeStorage.dataset.original;
        const priceEl = document.querySelector('.product-detail .price-current');
        const originalEl = document.querySelector('.product-detail .price-original');
        if (priceEl && variantPrice) priceEl.textContent = variantPrice;
        if (originalEl && variantOriginal) originalEl.textContent = variantOriginal;
    }

    // ============================================================
    // PRODUCT TABS (Description / Specs / Reviews)
    // ============================================================
    const tabBtns = document.querySelectorAll('[data-tab]');
    const tabPanels = document.querySelectorAll('[data-tab-panel]');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const target = this.dataset.tab;
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanels.forEach(p => p.style.display = 'none');
            this.classList.add('active');
            const panel = document.querySelector(`[data-tab-panel="${target}"]`);
            if (panel) panel.style.display = 'block';
        });
    });

    // Activate first tab by default
    if (tabBtns.length > 0) tabBtns[0].click();

    // ============================================================
    // REVIEW STAR RATING INPUT
    // ============================================================
    const starInputs = document.querySelectorAll('.star-rating-input span');
    starInputs.forEach((star, index) => {
        star.addEventListener('click', function () {
            const ratingInput = document.querySelector('input[name="rating"]');
            if (ratingInput) ratingInput.value = index + 1;
            starInputs.forEach((s, i) => {
                s.textContent = i <= index ? '★' : '☆';
                s.style.color = i <= index ? '#f0b429' : '#ccc';
            });
        });
        star.addEventListener('mouseover', function () {
            starInputs.forEach((s, i) => {
                s.textContent = i <= index ? '★' : '☆';
                s.style.color = i <= index ? '#f0b429' : '#ccc';
            });
        });
    });

    const starRatingWrap = document.querySelector('.star-rating-input');
    if (starRatingWrap) {
        starRatingWrap.addEventListener('mouseleave', function () {
            const ratingInput = document.querySelector('input[name="rating"]');
            const current = parseInt(ratingInput?.value || 0);
            starInputs.forEach((s, i) => {
                s.textContent = i < current ? '★' : '☆';
                s.style.color = i < current ? '#f0b429' : '#ccc';
            });
        });
    }

    // ============================================================
    // SUBMIT REVIEW FORM (AJAX)
    // ============================================================
    const reviewForm = document.querySelector('#review-form');
    if (reviewForm) {
        reviewForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(this));
            const btn = this.querySelector('[type="submit"]');
            if (btn) { btn.disabled = true; btn.textContent = 'Submitting...'; }

            fetch('api/reviews.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        showToast('Review submitted! Thank you.', 'success');
                        reviewForm.reset();
                    } else {
                        showToast(res.message || 'Could not submit review.', 'error');
                    }
                })
                .catch(() => showToast('Network error.', 'error'))
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.textContent = 'Submit Review'; }
                });
        });
    }

    // ============================================================
    // RELATED PRODUCTS CAROUSEL (simple scroll)
    // ============================================================
    const relatedGrid = document.querySelector('.related-products .products-grid');
    if (relatedGrid) {
        const prevBtn = document.querySelector('.carousel-prev');
        const nextBtn = document.querySelector('.carousel-next');
        const scrollAmount = 280;
        prevBtn && prevBtn.addEventListener('click', () => {
            relatedGrid.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
        });
        nextBtn && nextBtn.addEventListener('click', () => {
            relatedGrid.scrollBy({ left: scrollAmount, behavior: 'smooth' });
        });
    }

    // ============================================================
    // STICKY ADD TO CART BAR
    // ============================================================
    const productDetail = document.querySelector('.product-detail');
    const stickyBar = document.querySelector('.sticky-add-bar');
    if (productDetail && stickyBar) {
        const observer = new IntersectionObserver(([entry]) => {
            stickyBar.style.display = entry.isIntersecting ? 'none' : 'flex';
        }, { threshold: 0.1 });
        observer.observe(productDetail);
    }

});
