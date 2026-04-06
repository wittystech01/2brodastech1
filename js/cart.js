// GadgetZone - Cart JavaScript

document.addEventListener('DOMContentLoaded', function () {

    // ============================================================
    // CART PAGE: LIVE QUANTITY UPDATE & REMOVE
    // ============================================================
    const cartTable = document.querySelector('.cart-table');
    if (!cartTable) return;

    // Handle quantity change
    cartTable.addEventListener('change', function (e) {
        if (!e.target.classList.contains('qty-input')) return;
        const input = e.target;
        const key = input.dataset.key;
        const qty = Math.max(1, parseInt(input.value) || 1);
        input.value = qty;
        updateCartItem(key, qty);
    });

    // Handle remove button
    cartTable.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-btn');
        if (!btn) return;
        e.preventDefault();
        const key = btn.dataset.key;
        if (!key) return;
        removeCartItem(key, btn.closest('tr'));
    });

    // ============================================================
    // UPDATE CART ITEM QTY
    // ============================================================
    function updateCartItem(key, quantity) {
        fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'update', key, quantity })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    refreshCartSummary(data);
                    updateCartBadge(data.cart_count);
                } else {
                    showToast(data.message || 'Update failed.', 'error');
                }
            })
            .catch(() => showToast('Network error.', 'error'));
    }

    // ============================================================
    // REMOVE CART ITEM
    // ============================================================
    function removeCartItem(key, row) {
        fetch('api/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'remove', key })
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    row && row.remove();
                    refreshCartSummary(data);
                    updateCartBadge(data.cart_count);
                    showToast('Item removed from cart.', 'info');
                    if (data.cart_count === 0) {
                        showEmptyCart();
                    }
                }
            })
            .catch(() => showToast('Network error.', 'error'));
    }

    // ============================================================
    // REFRESH CART SUMMARY TOTALS
    // ============================================================
    function refreshCartSummary(data) {
        const subtotalEl = document.querySelector('[data-summary="subtotal"]');
        const totalEl = document.querySelector('[data-summary="total"]');
        const shippingEl = document.querySelector('[data-summary="shipping"]');
        const discountEl = document.querySelector('[data-summary="discount"]');

        if (subtotalEl && data.subtotal !== undefined) subtotalEl.textContent = data.subtotal_formatted;
        if (totalEl && data.total !== undefined) totalEl.textContent = data.total_formatted;
        if (shippingEl && data.shipping !== undefined) shippingEl.textContent = data.shipping_formatted;
        if (discountEl && data.discount !== undefined) discountEl.textContent = '-' + data.discount_formatted;
    }

    // ============================================================
    // SHOW EMPTY CART STATE
    // ============================================================
    function showEmptyCart() {
        const layout = document.querySelector('.cart-layout');
        if (layout) {
            layout.innerHTML = `
                <div style="text-align:center; padding:60px 20px; grid-column:1/-1;">
                    <div style="font-size:4rem; margin-bottom:16px;">🛒</div>
                    <h2 style="color:#1e3a5f; margin-bottom:8px;">Your cart is empty</h2>
                    <p style="color:#777; margin-bottom:24px;">Add some products to get started!</p>
                    <a href="shop.php" class="btn btn-primary">Continue Shopping</a>
                </div>`;
        }
    }

    // ============================================================
    // COUPON CODE APPLICATION
    // ============================================================
    const couponForm = document.querySelector('.coupon-form');
    if (couponForm) {
        couponForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const input = couponForm.querySelector('input');
            const code = input?.value.trim();
            if (!code) return;

            const btn = couponForm.querySelector('.btn');
            if (btn) { btn.disabled = true; btn.textContent = 'Applying...'; }

            fetch('api/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'apply_coupon', code })
            })
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        refreshCartSummary(data);
                        showToast('Coupon applied! 🎉', 'success');
                    } else {
                        showToast(data.message || 'Invalid coupon code.', 'error');
                    }
                })
                .catch(() => showToast('Network error.', 'error'))
                .finally(() => {
                    if (btn) { btn.disabled = false; btn.textContent = 'Apply'; }
                });
        });
    }

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
    // MINI CART FETCH (for cart dropdown if implemented)
    // ============================================================
    window.fetchMiniCart = function () {
        fetch('api/cart.php?action=get')
            .then(r => r.json())
            .then(data => {
                const miniCartEl = document.querySelector('.mini-cart-items');
                if (!miniCartEl) return;
                if (!data.items || data.items.length === 0) {
                    miniCartEl.innerHTML = '<p style="padding:16px;text-align:center;color:#777;">Cart is empty</p>';
                    return;
                }
                miniCartEl.innerHTML = data.items.map(item => `
                    <div class="mini-cart-item">
                        <img src="${item.image}" alt="${item.name}" width="48" height="48">
                        <div>
                            <div class="mini-cart-name">${item.name}</div>
                            <div class="mini-cart-price">${item.price_formatted} × ${item.quantity}</div>
                        </div>
                    </div>`).join('');
            })
            .catch(() => {});
    };

});
