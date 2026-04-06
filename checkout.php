<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php?redirect=' . urlencode('/checkout.php'));
}

$cart_items   = getCartItems();
if (empty($cart_items)) {
    redirect(SITE_URL . '/cart.php');
}

$cart_total   = 0;
foreach ($cart_items as $item) {
    $cart_total += ($item['price'] ?? 0) * ($item['quantity'] ?? 1);
}
$shipping_standard = 1500;
$shipping_express  = 3000;
$user_id = (int)($_SESSION['user_id'] ?? 0);
$user    = [];
try {
    $db   = getDB();
    $res  = $db->query("SELECT * FROM users WHERE id=$user_id LIMIT 1");
    $user = $res ? $res->fetch_assoc() : [];
} catch (Exception $e) { $user = []; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — GadgetZone</title>
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
        .nav-links a{color:rgba(255,255,255,.85);padding:8px 12px;border-radius:6px;font-size:.9rem}
        .nav-links a:hover{background:rgba(255,255,255,.1);color:#fff}
        .nav-icons{display:flex;align-items:center;gap:4px}
        .nav-icon{color:rgba(255,255,255,.85);padding:8px 10px;border-radius:6px;font-size:1rem;position:relative}
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
        .checkout-page{padding:40px 0}
        .checkout-steps{display:flex;justify-content:center;gap:0;margin-bottom:32px}
        .step{display:flex;align-items:center;gap:8px;padding:12px 20px;font-size:.85rem;font-weight:600;color:#888}
        .step.active{color:#1e3a5f}
        .step-num{width:28px;height:28px;border-radius:50%;border:2px solid #ddd;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem}
        .step.active .step-num{background:#1e3a5f;border-color:#1e3a5f;color:#fff}
        .step-divider{width:60px;height:2px;background:#ddd;align-self:center}
        .checkout-layout{display:grid;grid-template-columns:1fr 400px;gap:24px}
        .checkout-form-section{display:flex;flex-direction:column;gap:20px}
        .form-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:24px}
        .form-card h3{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:8px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .form-grid.full{grid-template-columns:1fr}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-group.full{grid-column:1/-1}
        label{font-size:.85rem;font-weight:600;color:#555}
        input[type=text],input[type=email],input[type=tel],select,textarea{padding:12px 14px;border:2px solid #eee;border-radius:8px;font-size:.9rem;outline:none;width:100%;transition:border-color .2s;font-family:inherit}
        input:focus,select:focus,textarea:focus{border-color:#1e3a5f}
        .shipping-options{display:flex;flex-direction:column;gap:12px}
        .shipping-option{display:flex;align-items:center;gap:14px;padding:14px;border:2px solid #eee;border-radius:10px;cursor:pointer;transition:all .2s}
        .shipping-option:has(input:checked),.shipping-option.selected{border-color:#1e3a5f;background:#f0f4ff}
        .shipping-option input{accent-color:#1e3a5f}
        .shipping-info{flex:1}
        .shipping-name{font-weight:700;font-size:.9rem;color:#333}
        .shipping-desc{font-size:.8rem;color:#888}
        .shipping-price{font-weight:700;color:#1e3a5f}
        .payment-options{display:flex;flex-direction:column;gap:12px}
        .payment-option{display:flex;align-items:center;gap:14px;padding:16px;border:2px solid #eee;border-radius:10px;cursor:pointer;transition:all .2s}
        .payment-option:has(input:checked),.payment-option.selected{border-color:#ff6b35;background:#fff8f5}
        .payment-option input{accent-color:#ff6b35}
        .payment-icon{font-size:1.8rem;width:40px;text-align:center}
        .payment-info{flex:1}
        .payment-name{font-weight:700;font-size:.95rem;color:#333}
        .payment-desc{font-size:.8rem;color:#888}
        .payment-badge{font-size:.7rem;background:#22c55e;color:#fff;padding:2px 8px;border-radius:10px;font-weight:600}
        .order-summary-card{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);padding:24px;position:sticky;top:80px;height:fit-content}
        .order-summary-card h3{font-size:1rem;font-weight:700;color:#1e3a5f;margin-bottom:16px;padding-bottom:12px;border-bottom:2px solid #f0f0f0}
        .order-items{margin-bottom:16px}
        .order-item{display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f8f9fa}
        .order-item:last-child{border-bottom:none}
        .order-item-thumb{width:48px;height:48px;border-radius:8px;background:linear-gradient(135deg,#f0f4ff,#e8f0fe);display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .order-item-name{flex:1;font-size:.85rem;font-weight:600;color:#333;line-height:1.4}
        .order-item-qty{font-size:.78rem;color:#888}
        .order-item-price{font-weight:700;font-size:.9rem;color:#1e3a5f}
        .summary-line{display:flex;justify-content:space-between;padding:8px 0;font-size:.9rem;color:#555;border-bottom:1px solid #f8f9fa}
        .summary-line:last-of-type{border-bottom:none}
        .summary-line.total{font-size:1.1rem;font-weight:800;color:#1e3a5f;padding:14px 0;border-top:2px solid #f0f0f0;border-bottom:none;margin-top:8px}
        .free-ship{color:#22c55e;font-weight:600}
        .btn-place-order{display:block;width:100%;background:#ff6b35;color:#fff;border:none;padding:18px;border-radius:10px;font-size:1.1rem;font-weight:800;cursor:pointer;transition:background .3s;text-align:center;margin-top:16px}
        .btn-place-order:hover{background:#e55a25}
        .secure-note{text-align:center;font-size:.78rem;color:#888;margin-top:10px}
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
        @media(max-width:768px){.checkout-layout{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:repeat(2,1fr)}}
        @media(max-width:480px){.footer-grid{grid-template-columns:1fr}.checkout-steps{gap:0}.step{padding:8px 10px}.step-divider{width:30px}}
    </style>
</head>
<body>

<nav class="navbar" id="navbar">
    <div class="container">
        <div class="nav-brand"><a href="<?= SITE_URL ?>/index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">GadgetZone</span></a></div>
        <div class="nav-search">
            <form action="<?= SITE_URL ?>/shop.php" method="GET" class="search-form">
                <input type="text" name="q" placeholder="Search..." class="search-input">
                <button type="submit" class="search-btn">🔍</button>
            </form>
        </div>
        <div class="nav-links">
            <a href="<?= SITE_URL ?>/index.php">Home</a>
            <a href="<?= SITE_URL ?>/shop.php">Shop</a>
            <a href="<?= SITE_URL ?>/blog.php">Blog</a>
            <a href="<?= SITE_URL ?>/contact.php">Contact</a>
        </div>
        <div class="nav-icons">
            <a href="<?= SITE_URL ?>/wishlist.php" class="nav-icon" title="Wishlist">♡</a>
            <a href="<?= SITE_URL ?>/dashboard.php" class="nav-icon" title="Profile">👤</a>
            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon cart-icon" title="Cart">🛒 <span class="cart-badge" id="cartBadge"><?= getCartCount() ?></span></a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header"><span class="logo-text">GadgetZone</span><button class="close-menu" id="closeMenu">✕</button></div>
    <nav class="mobile-nav-links">
        <a href="<?= SITE_URL ?>/index.php">🏠 Home</a><a href="<?= SITE_URL ?>/shop.php">🛍️ Shop</a>
        <a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a><a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>Checkout</h1>
        <div class="breadcrumb">
            <a href="<?= SITE_URL ?>/index.php">Home</a> / <a href="<?= SITE_URL ?>/cart.php">Cart</a> / Checkout
        </div>
    </div>
</div>

<div class="checkout-page">
    <div class="container">
        <div class="checkout-steps">
            <div class="step active"><span class="step-num">1</span> Cart</div>
            <div class="step-divider"></div>
            <div class="step active"><span class="step-num">2</span> Details</div>
            <div class="step-divider"></div>
            <div class="step"><span class="step-num">3</span> Payment</div>
            <div class="step-divider"></div>
            <div class="step"><span class="step-num">4</span> Confirm</div>
        </div>

        <form id="checkoutForm" method="POST" action="<?= SITE_URL ?>/api/order.php" onsubmit="submitOrder(event)">
            <div class="checkout-layout">
                <div class="checkout-form-section">
                    <!-- Billing Info -->
                    <div class="form-card">
                        <h3>📋 Billing &amp; Shipping Details</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="full_name">Full Name *</label>
                                <input type="text" id="full_name" name="full_name" placeholder="John Doe" value="<?= htmlspecialchars($user['full_name'] ?? $user['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number *</label>
                                <input type="tel" id="phone" name="phone" placeholder="080XXXXXXXX" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" placeholder="Lagos" value="<?= htmlspecialchars($user['city'] ?? '') ?>" required>
                            </div>
                            <div class="form-group full">
                                <label for="address">Delivery Address *</label>
                                <input type="text" id="address" name="address" placeholder="123 Street Name, Area" value="<?= htmlspecialchars($user['address'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="state">State *</label>
                                <select id="state" name="state" required>
                                    <option value="">Select State</option>
                                    <?php foreach(['Abia','Abuja','Adamawa','Akwa Ibom','Anambra','Bauchi','Bayelsa','Benue','Borno','Cross River','Delta','Ebonyi','Edo','Ekiti','Enugu','Gombe','Imo','Jigawa','Kaduna','Kano','Katsina','Kebbi','Kogi','Kwara','Lagos','Nasarawa','Niger','Ogun','Ondo','Osun','Oyo','Plateau','Rivers','Sokoto','Taraba','Yobe','Zamfara'] as $s): ?>
                                    <option value="<?= $s ?>" <?= ($user['state']??'')===$s?'selected':'' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="postal">Postal Code</label>
                                <input type="text" id="postal" name="postal" placeholder="100001">
                            </div>
                        </div>
                    </div>

                    <!-- Shipping -->
                    <div class="form-card">
                        <h3>🚚 Shipping Method</h3>
                        <div class="shipping-options">
                            <label class="shipping-option">
                                <input type="radio" name="shipping_method" value="standard" checked onchange="updateShipping(1500)">
                                <div class="shipping-info">
                                    <div class="shipping-name">Standard Delivery</div>
                                    <div class="shipping-desc">3–5 business days</div>
                                </div>
                                <div class="shipping-price">₦1,500</div>
                            </label>
                            <label class="shipping-option">
                                <input type="radio" name="shipping_method" value="express" onchange="updateShipping(3000)">
                                <div class="shipping-info">
                                    <div class="shipping-name">Express Delivery</div>
                                    <div class="shipping-desc">Same day / Next day</div>
                                </div>
                                <div class="shipping-price">₦3,000</div>
                            </label>
                        </div>
                    </div>

                    <!-- Payment -->
                    <div class="form-card">
                        <h3>💳 Payment Method</h3>
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="paystack" checked>
                                <div class="payment-icon">💳</div>
                                <div class="payment-info">
                                    <div class="payment-name">Paystack <span class="payment-badge">Recommended</span></div>
                                    <div class="payment-desc">Cards, Bank Transfer, USSD</div>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="flutterwave">
                                <div class="payment-icon">🦋</div>
                                <div class="payment-info">
                                    <div class="payment-name">Flutterwave</div>
                                    <div class="payment-desc">Cards, Mobile Money, Bank</div>
                                </div>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="pay_on_delivery">
                                <div class="payment-icon">💵</div>
                                <div class="payment-info">
                                    <div class="payment-name">Pay on Delivery</div>
                                    <div class="payment-desc">Pay cash when you receive your order</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="order-summary-card">
                    <h3>Order Summary</h3>
                    <div class="order-items">
                        <?php foreach ($cart_items as $item):
                            $ip = (float)($item['price']??0); $iq = (int)($item['quantity']??1);
                        ?>
                        <div class="order-item">
                            <div class="order-item-thumb">📱</div>
                            <div class="order-item-name">
                                <?= htmlspecialchars($item['name']??'Product') ?>
                                <div class="order-item-qty">Qty: <?= $iq ?></div>
                            </div>
                            <div class="order-item-price"><?= formatPrice($ip*$iq) ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="summary-line"><span>Subtotal</span><span><?= formatPrice($cart_total) ?></span></div>
                    <div class="summary-line">
                        <span>Shipping</span>
                        <span id="shippingDisplay"><?= $cart_total>=50000 ? '<span class="free-ship">FREE</span>' : '₦1,500' ?></span>
                    </div>
                    <div class="summary-line total">
                        <span>Total</span>
                        <span id="orderTotal"><?= formatPrice($cart_total + ($cart_total>=50000?0:1500)) ?></span>
                    </div>

                    <input type="hidden" name="cart_total" value="<?= $cart_total ?>">
                    <input type="hidden" name="shipping_fee" id="shippingFeeInput" value="<?= $cart_total>=50000?0:1500 ?>">

                    <button type="submit" class="btn-place-order">🔒 Place Order</button>
                    <div class="secure-note">🔒 Secured by 256-bit SSL encryption</div>

                    <div style="margin-top:16px;padding:12px;background:#f0f4ff;border-radius:8px;font-size:.8rem;color:#555;line-height:1.6">
                        ✅ 100% Genuine Products<br>
                        🔄 30-Day Return Policy<br>
                        📞 24/7 Customer Support
                    </div>
                </div>
            </div>
        </form>
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
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛒</span><span class="bottom-nav-label">Cart</span></a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
const CART_TOTAL = <?= (float)$cart_total ?>;

function updateShipping(fee) {
    const freeShip = CART_TOTAL >= 50000;
    const actual   = freeShip ? 0 : fee;
    document.getElementById('shippingDisplay').innerHTML = actual === 0 ? '<span style="color:#22c55e;font-weight:600">FREE</span>' : '₦'+actual.toLocaleString();
    document.getElementById('shippingFeeInput').value = actual;
    document.getElementById('orderTotal').textContent = '₦'+(CART_TOTAL + actual).toLocaleString();
}

function submitOrder(e) {
    e.preventDefault();
    const btn = e.target.querySelector('.btn-place-order');
    btn.textContent = '⏳ Processing...';
    btn.disabled = true;

    const data = new FormData(e.target);
    fetch('<?= SITE_URL ?>/api/order.php', { method:'POST', body: data })
    .then(r=>r.json()).then(d=>{
        if (d.success) {
            if (d.payment_url) {
                window.location.href = d.payment_url;
            } else {
                window.location.href = '<?= SITE_URL ?>/orders.php?order_success=1&id=' + (d.order_id||'');
            }
        } else {
            alert(d.message || 'Order failed. Please try again.');
            btn.textContent = '🔒 Place Order'; btn.disabled = false;
        }
    }).catch(()=>{
        alert('Unable to process order. Please check your connection.');
        btn.textContent = '🔒 Place Order'; btn.disabled = false;
    });
}
</script>
</body>
</html>
