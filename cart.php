<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$cart_items  = getCartItems();
$cart_total  = 0;
foreach ($cart_items as $item) {
    $cart_total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
}
$shipping_fee  = ($cart_total >= 50000) ? 0 : 1500;
$order_total   = $cart_total + $shipping_fee;
$cart_count    = getCartCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart — GadgetZone</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/mobile.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;color:#333;background:#f8f9fa}
        a{text-decoration:none;color:inherit}
        .container{max-width:1200px;margin:0 auto;padding:0 20px}
        .navbar{background:#1e3a5f;padding:0;position:sticky;top:0;z-index:999;box-shadow:0 2px 10px rgba(0,0,0,.15)}
        .navbar .container{display:flex;align-items:center;gap:20px;height:64px}
        .nav-brand .logo{display:flex;align-items:center;gap:8px;color:#fff;font-size:1.4rem;font-weight:800}
        .nav-search{flex:1;max-width:400px}
        .search-form{display:flex;background:rgba(255,255,255,.12);border-radius:8px;overflow:hidden}
        .search-input{flex:1;background:transparent;border:none;padding:10px 14px;color:#fff;font-size:.9rem;outline:none}
        .search-input::placeholder{color:rgba(255,255,255,.6)}
        .search-btn{background:transparent;border:none;color:#fff;padding:0 14px;cursor:pointer}
        .nav-links{display:flex;gap:4px}
        .nav-links a{color:rgba(255,255,255,.85);padding:8px 12px;border-radius:6px;font-size:.9rem;transition:all .2s}
        .nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
        .nav-icons{display:flex;align-items:center;gap:4px}
        .nav-icon{color:rgba(255,255,255,.85);padding:8px 10px;border-radius:6px;font-size:1rem;position:relative;transition:all .2s}
        .nav-icon:hover{background:rgba(255,255,255,.1);color:#fff}
        .cart-badge{background:#ff6b35;color:#fff;border-radius:50%;width:18px;height:18px;font-size:.65rem;font-weight:700;display:inline-flex;align-items:center;justify-content:center;position:absolute;top:4px;right:4px}
        .hamburger{display:none;background:none;border:none;cursor:pointer;padding:4px}
        .hamburger span{display:block;width:24px;height:2px;background:#fff;margin:5px 0}
        .mobile-menu{display:none;position:fixed;top:0;right:-100%;width:280px;height:100vh;background:#fff;z-index:1001;transition:right .3s;overflow-y:auto}
        .mobile-menu.open{right:0}
        .mobile-menu-header{background:#1e3a5f;color:#fff;padding:20px;display:flex;justify-content:space-between;align-items:center}
        .mobile-menu-header .logo-text{font-size:1.3rem;font-weight:800}
        .close-menu{background:none;border:none;color:#fff;font-size:1.2rem;cursor:pointer}
        .mobile-nav-links{padding:16px 0}
        .mobile-nav-links a{display:block;padding:14px 20px;color:#333;border-bottom:1px solid #f0f0f0;font-weight:500}
        .mobile-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000}
        .mobile-overlay.open{display:block}
        .page-header{background:#1e3a5f;color:#fff;padding:32px 0}
        .page-header h1{font-size:1.8rem;font-weight:700;margin-bottom:6px}
        .breadcrumb{display:flex;align-items:center;gap:8px;font-size:.85rem;opacity:.8}
        .breadcrumb a{color:rgba(255,255,255,.8)}
        .breadcrumb a:hover{color:#ff6b35}
        .cart-page{padding:40px 0}
        .cart-layout{display:grid;grid-template-columns:1fr 360px;gap:24px}
        .cart-table-wrap{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
        .cart-table{width:100%;border-collapse:collapse}
        .cart-table th{padding:16px 20px;text-align:left;font-size:.85rem;font-weight:700;color:#666;background:#f8f9fa;border-bottom:2px solid #eee;text-transform:uppercase;letter-spacing:.5px}
        .cart-table td{padding:16px 20px;border-bottom:1px solid #f0f0f0;vertical-align:middle}
        .cart-table tr:last-child td{border-bottom:none}
        .cart-product{display:flex;align-items:center;gap:14px}
        .cart-thumb{width:70px;height:70px;border-radius:10px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:30px;flex-shrink:0;overflow:hidden}
        .cart-thumb img{width:100%;height:100%;object-fit:cover}
        .cart-product-name{font-weight:600;color:#333;font-size:.9rem;line-height:1.4}
        .cart-product-meta{font-size:.78rem;color:#888;margin-top:3px}
        .cart-price{font-weight:700;color:#1e3a5f;font-size:1rem}
        .qty-control{display:flex;align-items:center;gap:0;border:2px solid #eee;border-radius:8px;overflow:hidden;width:fit-content}
        .qty-ctrl-btn{background:#f8f9fa;border:none;padding:8px 12px;cursor:pointer;font-size:1rem;font-weight:700;transition:background .2s}
        .qty-ctrl-btn:hover{background:#ddd}
        .qty-display{padding:8px 14px;font-weight:700;font-size:.9rem;min-width:40px;text-align:center;border:none;outline:none}
        .cart-subtotal{font-weight:700;color:#1e3a5f;font-size:1rem}
        .btn-remove{background:none;border:none;color:#ccc;font-size:1.2rem;cursor:pointer;padding:4px;border-radius:4px;transition:color .2s}
        .btn-remove:hover{color:#ef4444}
        .empty-cart{text-align:center;padding:80px 20px}
        .empty-cart .empty-icon{font-size:5rem;margin-bottom:20px}
        .empty-cart h2{font-size:1.5rem;color:#1e3a5f;margin-bottom:8px}
        .empty-cart p{color:#888;margin-bottom:24px}
        .cart-summary{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:24px;height:fit-content;position:sticky;top:80px}
        .cart-summary h3{font-size:1.1rem;font-weight:700;color:#1e3a5f;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0}
        .summary-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;font-size:.9rem;color:#555;border-bottom:1px solid #f8f9fa}
        .summary-row:last-of-type{border-bottom:none}
        .summary-row.total{font-size:1.1rem;font-weight:800;color:#1e3a5f;padding:16px 0;margin-top:8px;border-top:2px solid #f0f0f0;border-bottom:none}
        .free-shipping{color:#22c55e;font-weight:600}
        .coupon-section{margin:16px 0}
        .coupon-section h4{font-size:.85rem;font-weight:700;color:#555;margin-bottom:10px}
        .coupon-form{display:flex;gap:8px}
        .coupon-input{flex:1;padding:10px 14px;border:2px solid #eee;border-radius:8px;font-size:.9rem;outline:none;transition:border-color .2s}
        .coupon-input:focus{border-color:#1e3a5f}
        .btn-coupon{background:#1e3a5f;color:#fff;border:none;padding:10px 16px;border-radius:8px;font-weight:600;cursor:pointer;font-size:.85rem;white-space:nowrap}
        .btn-coupon:hover{background:#0f2840}
        .btn-checkout{display:block;width:100%;background:#ff6b35;color:#fff;border:none;padding:16px;border-radius:10px;font-size:1.05rem;font-weight:800;cursor:pointer;transition:background .3s;text-align:center;margin-top:16px}
        .btn-checkout:hover{background:#e55a25}
        .continue-link{display:block;text-align:center;margin-top:14px;font-size:.85rem;color:#666}
        .continue-link:hover{color:#1e3a5f}
        .cart-benefits{margin-top:16px;padding:12px;background:#f0f4ff;border-radius:8px;font-size:.8rem;color:#1e3a5f}
        .cart-benefits div{margin-bottom:4px}
        .footer{background:#1e3a5f;color:#fff;padding:60px 0 20px}
        .footer-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:40px;margin-bottom:40px}
        .footer h3,.footer h4{margin-bottom:16px}
        .footer p{opacity:.8;line-height:1.6;font-size:.9rem}
        .footer ul{list-style:none}
        .footer ul li{margin-bottom:8px}
        .footer ul a{opacity:.8;font-size:.9rem}
        .footer ul a:hover{opacity:1;color:#ff6b35}
        .social-links{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
        .social-link{background:rgba(255,255,255,.1);padding:6px 14px;border-radius:20px;font-size:.8rem}
        .newsletter-form{display:flex;gap:8px;margin-top:8px;flex-wrap:wrap}
        .newsletter-form input{flex:1;padding:10px 14px;border-radius:6px;border:none;font-size:.9rem;min-width:140px}
        .newsletter-form .btn-primary{background:#ff6b35;color:#fff;padding:10px 16px;border:none;border-radius:6px;font-weight:600;cursor:pointer}
        .trust-badges{display:flex;flex-wrap:wrap;gap:8px}
        .trust-badge{background:rgba(255,255,255,.1);padding:4px 10px;border-radius:12px;font-size:.75rem}
        .footer-bottom{border-top:1px solid rgba(255,255,255,.1);padding-top:20px;text-align:center;font-size:.85rem;opacity:.7}
        .footer-bottom a{opacity:.8;margin:0 4px}
        .bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem}
        .bottom-nav-item:hover,.bottom-nav-item.active{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
        @media(max-width:1024px){.nav-links{display:none}.hamburger{display:block}}
        @media(max-width:768px){.cart-layout{grid-template-columns:1fr}.cart-table .hide-mobile{display:none}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.footer-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-brand"><a href="<?= SITE_URL ?>/index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">GadgetZone</span></a></div>
        <div class="nav-search">
            <form action="<?= SITE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search smartphones, gadgets..." class="search-input">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php">Home</a>
            <a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a>
            <a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a>
            <a href="<?= SITE_URL ?>/shop.php">Gadgets</a>
            <a href="<?= SITE_URL ?>/blog.php">Blog</a>
            <a href="<?= SITE_URL ?>/contact.php">Contact</a>
        </div>
        <div class="nav-icons">
            <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon" title="Wishlist">♡</a>
            <a href="<?= SITE_URL ?>/dashboard.php" class="nav-icon" title="Profile">👤</a>
            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon cart-icon" title="Cart">🛒 <span class="cart-badge" id="cartBadge"><?= $cart_count ?></span></a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header"><span class="logo-text">GadgetZone</span><button class="close-menu" id="closeMenu">✕</button></div>
    <nav class="mobile-nav-links">
        <a href="<?= SITE_URL ?>/index.php">🏠 Home</a>
        <a href="<?= SITE_URL ?>/shop.php">🛍️ Shop</a>
        <a href="<?= SITE_URL ?>/shop.php?category=smartphones">📱 Smartphones</a>
        <a href="<?= SITE_URL ?>/shop.php?category=accessories">🎧 Accessories</a>
        <a href="<?= SITE_URL ?>/video.php">🎥 Video Gallery</a>
        <a href="<?= SITE_URL ?>/blog.php">📝 Blog</a>
        <a href="<?= SITE_URL ?>/contact.php">📞 Contact</a>
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
        <a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>🛒 Shopping Cart</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/index.php">Home</a> / Cart
        </div>
    </div>
</div>

<div class="cart-page">
    <div class="container">
        <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <div class="empty-icon">🛒</div>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added anything to your cart yet.</p>
            <a href="<?= SITE_URL ?>/shop.php" style="display:inline-block;background:#ff6b35;color:#fff;padding:14px 32px;border-radius:10px;font-weight:700;font-size:1rem">🛍️ Continue Shopping</a>
        </div>
        <?php else: ?>
        <div class="cart-layout">
            <!-- Cart Table -->
            <div class="cart-table-wrap">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th colspan="2">Product</th>
                            <th class="hide-mobile">Price</th>
                            <th>Quantity</th>
                            <th class="hide-mobile">Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                    <?php foreach ($cart_items as $key => $item):
                        $item_price    = (float)($item['price'] ?? 0);
                        $item_qty      = (int)($item['quantity'] ?? 1);
                        $item_subtotal = $item_price * $item_qty;
                        $pEmoji        = '📱';
                    ?>
                    <tr data-key="<?= htmlspecialchars($key) ?>">
                        <td style="width:90px">
                            <div class="cart-thumb">
                                <?php if (!empty($item['image'])): ?>
                                    <img src="<?= SITE_URL ?>/<?= htmlspecialchars($item['image']) ?>" alt="">
                                <?php else: echo $pEmoji; endif; ?>
                            </div>
                        </td>
                        <td>
                            <div class="cart-product-name"><?= htmlspecialchars($item['name'] ?? 'Product') ?></div>
                            <?php if (!empty($item['options'])): ?>
                            <div class="cart-product-meta"><?= htmlspecialchars(is_array($item['options']) ? implode(', ',$item['options']) : $item['options']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="hide-mobile"><span class="cart-price"><?= formatPrice($item_price) ?></span></td>
                        <td>
                            <div class="qty-control">
                                <button class="qty-ctrl-btn" onclick="updateQty('<?= htmlspecialchars($key) ?>',-1)">−</button>
                                <span class="qty-display" id="qty-<?= htmlspecialchars($key) ?>"><?= $item_qty ?></span>
                                <button class="qty-ctrl-btn" onclick="updateQty('<?= htmlspecialchars($key) ?>',1)">+</button>
                            </div>
                        </td>
                        <td class="hide-mobile"><span class="cart-subtotal" id="sub-<?= htmlspecialchars($key) ?>"><?= formatPrice($item_subtotal) ?></span></td>
                        <td><button class="btn-remove" onclick="removeItem('<?= htmlspecialchars($key) ?>')" title="Remove">✕</button></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Cart Summary -->
            <div class="cart-summary">
                <h3>Order Summary</h3>
                <div class="summary-row">
                    <span>Subtotal (<?= $cart_count ?> items)</span>
                    <span id="summarySubtotal"><?= formatPrice($cart_total) ?></span>
                </div>
                <div class="summary-row">
                    <span>Shipping</span>
                    <span id="summaryShipping"><?= $shipping_fee == 0 ? '<span class="free-shipping">FREE</span>' : formatPrice($shipping_fee) ?></span>
                </div>
                <?php if ($shipping_fee > 0): ?>
                <div style="font-size:.78rem;color:#888;padding:6px 0">Add <?= formatPrice(50000 - $cart_total) ?> more for free shipping!</div>
                <?php endif; ?>

                <div class="coupon-section">
                    <h4>🏷️ Have a coupon?</h4>
                    <div class="coupon-form">
                        <input type="text" id="couponCode" class="coupon-input" placeholder="Enter coupon code">
                        <button class="btn-coupon" onclick="applyCoupon()">Apply</button>
                    </div>
                    <div id="couponMsg" style="font-size:.8rem;margin-top:6px"></div>
                </div>

                <div class="summary-row total">
                    <span>Total</span>
                    <span id="summaryTotal"><?= formatPrice($order_total) ?></span>
                </div>

                <a href="<?= SITE_URL ?>/checkout.php" class="btn-checkout">Proceed to Checkout →</a>
                <a href="<?= SITE_URL ?>/shop.php" class="continue-link">← Continue Shopping</a>

                <div class="cart-benefits">
                    <div>✅ Genuine products guaranteed</div>
                    <div>🔒 Secure checkout</div>
                    <div>🚚 Fast nationwide delivery</div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><h3>⚡ GadgetZone</h3><p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p><div class="social-links"><a href="#" class="social-link">Facebook</a><a href="#" class="social-link">Twitter</a><a href="#" class="social-link">Instagram</a></div></div>
            <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="<?= SITE_URL ?>/index.php">Home</a></li><li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li><li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li><li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li></ul></div>
            <div class="footer-col"><h4>Categories</h4><ul><li><a href="<?= SITE_URL ?>/shop.php?category=smartphones">Smartphones</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=accessories">Accessories</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=smartwatches">Smartwatches</a></li><li><a href="<?= SITE_URL ?>/shop.php?category=tablets">Tablets</a></li></ul></div>
            <div class="footer-col"><h4>Newsletter</h4><p>Subscribe for deals!</p><form class="newsletter-form" onsubmit="event.preventDefault()"><input type="email" placeholder="Your email" required><button type="submit" class="btn-primary">Go</button></form><div class="trust-badges" style="margin-top:12px"><div class="trust-badge">🔒 Secure</div><div class="trust-badge">🚚 Fast</div></div></div>
        </div>
        <div class="footer-bottom"><p>© 2024 GadgetZone. All rights reserved. | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p></div>
    </div>
</footer>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item"><span class="bottom-nav-icon">🏠</span><span class="bottom-nav-label">Home</span></a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛍️</span><span class="bottom-nav-label">Shop</span></a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item active"><span class="bottom-nav-icon">🛒</span><span class="bottom-nav-label">Cart</span></a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
function updateQty(key, delta) {
    fetch('<?= SITE_URL ?>/api/cart.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'update', key, delta})
    }).then(r=>r.json()).then(d=>{
        if (d.removed) {
            document.querySelector(`tr[data-key="${key}"]`)?.remove();
        } else {
            const qEl = document.getElementById('qty-'+key);
            const sEl = document.getElementById('sub-'+key);
            if (qEl) qEl.textContent = d.quantity;
            if (sEl) sEl.textContent = d.subtotal_formatted;
        }
        const badge = document.getElementById('cartBadge');
        if (badge && d.cart_count !== undefined) badge.textContent = d.cart_count;
        if (document.getElementById('summarySubtotal')) document.getElementById('summarySubtotal').textContent = d.subtotal_formatted || '';
        if (document.getElementById('summaryTotal')) document.getElementById('summaryTotal').textContent = d.total_formatted || '';
    }).catch(()=>{
        // Fallback: reload
        location.reload();
    });
}

function removeItem(key) {
    if (!confirm('Remove this item from your cart?')) return;
    fetch('<?= SITE_URL ?>/api/cart.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'remove', key})
    }).then(r=>r.json()).then(d=>{
        document.querySelector(`tr[data-key="${key}"]`)?.remove();
        const badge = document.getElementById('cartBadge');
        if (badge && d.cart_count !== undefined) badge.textContent = d.cart_count;
        if (d.cart_count === 0) location.reload();
    }).catch(()=>location.reload());
}

function applyCoupon() {
    const code = document.getElementById('couponCode').value.trim();
    const msg  = document.getElementById('couponMsg');
    if (!code) { msg.textContent='Please enter a coupon code.'; msg.style.color='#ef4444'; return; }
    msg.textContent = 'Checking...'; msg.style.color = '#888';
    setTimeout(()=>{ msg.textContent = 'Invalid or expired coupon code.'; msg.style.color='#ef4444'; }, 800);
}
</script>
</body>
</html>
