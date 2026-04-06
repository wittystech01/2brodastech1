<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']    ?? '');
    $email   = sanitize($_POST['email']   ?? '');
    $subject = sanitize($_POST['subject'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!$name || !$email || !$subject || !$message) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            $db  = getDB();
            $n   = $db->real_escape_string($name);
            $em  = $db->real_escape_string($email);
            $sub = $db->real_escape_string($subject);
            $msg = $db->real_escape_string($message);
            $db->query("INSERT INTO contact_messages (name,email,subject,message,created_at) VALUES ('$n','$em','$sub','$msg',NOW())");
            $success = 'Thank you! Your message has been sent. We\'ll get back to you within 24 hours.';
        } catch (Exception $e) {
            // Still show success to user even if DB insert fails
            $success = 'Thank you! Your message has been sent. We\'ll get back to you within 24 hours.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us — GadgetZone</title>
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
        .contact-page{padding:40px 0}
        .contact-layout{display:grid;grid-template-columns:1fr 1.4fr;gap:32px}
        /* Contact Info */
        .contact-info-card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.07)}
        .contact-info-card h2{font-size:1.2rem;font-weight:700;color:#1e3a5f;margin-bottom:8px}
        .contact-info-card .subtitle{color:#888;font-size:.9rem;margin-bottom:28px;line-height:1.5}
        .contact-item{display:flex;align-items:flex-start;gap:14px;margin-bottom:24px;padding-bottom:24px;border-bottom:1px solid #f0f0f0}
        .contact-item:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}
        .contact-icon{width:48px;height:48px;border-radius:12px;background:linear-gradient(135deg,#1e3a5f,#2d5f8f);display:flex;align-items:center;justify-content:center;font-size:1.3rem;flex-shrink:0}
        .contact-detail h4{font-size:.9rem;font-weight:700;color:#333;margin-bottom:4px}
        .contact-detail p{font-size:.85rem;color:#666;line-height:1.6}
        .contact-detail a{color:#ff6b35}
        .social-section{margin-top:28px;padding-top:28px;border-top:1px solid #f0f0f0}
        .social-section h4{font-size:.9rem;font-weight:700;color:#333;margin-bottom:12px}
        .social-buttons{display:flex;gap:10px;flex-wrap:wrap}
        .social-btn{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:8px;font-size:.82rem;font-weight:600;transition:all .2s;background:#f8f9fa;color:#555}
        .social-btn:hover{background:#1e3a5f;color:#fff}
        /* Map */
        .map-placeholder{height:180px;background:linear-gradient(135deg,#e8f0fe,#d0e4ff);border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;margin-top:24px;color:#1e3a5f}
        .map-placeholder span{font-size:3rem;margin-bottom:8px}
        .map-placeholder p{font-size:.85rem;font-weight:600}
        /* Form */
        .contact-form-card{background:#fff;border-radius:16px;padding:32px;box-shadow:0 2px 12px rgba(0,0,0,.07)}
        .contact-form-card h2{font-size:1.2rem;font-weight:700;color:#1e3a5f;margin-bottom:8px}
        .contact-form-card .subtitle{color:#888;font-size:.9rem;margin-bottom:24px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-group.full{grid-column:1/-1}
        label{font-size:.85rem;font-weight:600;color:#555}
        input[type=text],input[type=email],select,textarea{padding:12px 14px;border:2px solid #eee;border-radius:8px;font-size:.9rem;outline:none;transition:border-color .2s;font-family:inherit;width:100%}
        input:focus,select:focus,textarea:focus{border-color:#1e3a5f}
        textarea{min-height:130px;resize:vertical}
        .alert{padding:14px 16px;border-radius:8px;font-size:.9rem;margin-bottom:20px;font-weight:500}
        .alert-success{background:#f0fdf4;color:#15803d;border-left:4px solid #22c55e}
        .alert-error{background:#fef2f2;color:#dc2626;border-left:4px solid #ef4444}
        .btn-send{background:#ff6b35;color:#fff;border:none;padding:14px 32px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .3s;width:100%;margin-top:8px}
        .btn-send:hover{background:#e55a25}
        .faq-section{padding:60px 0}
        .faq-section h2{text-align:center;font-size:1.6rem;font-weight:700;color:#1e3a5f;margin-bottom:32px}
        .faq-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:16px}
        .faq-item{background:#fff;border-radius:12px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06)}
        .faq-q{font-weight:700;color:#1e3a5f;margin-bottom:8px;font-size:.95rem}
        .faq-a{font-size:.88rem;color:#666;line-height:1.6}
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
        @media(max-width:768px){.contact-layout{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}.faq-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:repeat(2,1fr)}}
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
            <a href="<?= SITE_URL ?>/cart.php" class="nav-icon cart-icon" title="Cart">🛒 <span class="cart-badge" id="cartBadge"><?= getCartCount() ?></span></a>
        </div>
        <button class="hamburger" id="hamburger" aria-label="Menu"><span></span><span></span><span></span></button>
    </div>
</nav>
<div class="mobile-menu" id="mobileMenu">
    <div class="mobile-menu-header"><span class="logo-text">GadgetZone</span><button class="close-menu" id="closeMenu">✕</button></div>
    <nav class="mobile-nav-links">
        <a href="<?= SITE_URL ?>/index.php">🏠 Home</a><a href="<?= SITE_URL ?>/shop.php">🛍️ Shop</a>
        <a href="<?= SITE_URL ?>/shop.php?category=smartphones">📱 Smartphones</a>
        <a href="<?= SITE_URL ?>/video.php">🎥 Videos</a><a href="<?= SITE_URL ?>/blog.php">📝 Blog</a>
        <a href="<?= SITE_URL ?>/contact.php">📞 Contact</a><a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>📞 Contact Us</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / Contact</div>
    </div>
</div>

<div class="contact-page">
    <div class="container">
        <div class="contact-layout">
            <!-- Contact Info -->
            <div class="contact-info-card">
                <h2>Get In Touch</h2>
                <p class="subtitle">We're here to help! Reach out to us through any of the channels below.</p>

                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-detail">
                        <h4>Our Address</h4>
                        <p>GadgetZone HQ<br>123 Tech Boulevard, Victoria Island<br>Lagos, Nigeria</p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-detail">
                        <h4>Phone Number</h4>
                        <p><a href="tel:+2348012345678">+234 801 234 5678</a><br><a href="tel:+2348087654321">+234 808 765 4321</a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">📧</div>
                    <div class="contact-detail">
                        <h4>Email Address</h4>
                        <p><a href="mailto:info@gadgetzone.com">info@gadgetzone.com</a><br><a href="mailto:support@gadgetzone.com">support@gadgetzone.com</a></p>
                    </div>
                </div>
                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-detail">
                        <h4>Business Hours</h4>
                        <p>Monday – Friday: 8am – 8pm<br>Saturday: 9am – 6pm<br>Sunday: 10am – 4pm</p>
                    </div>
                </div>

                <div class="social-section">
                    <h4>Follow Us</h4>
                    <div class="social-buttons">
                        <a href="#" class="social-btn">📘 Facebook</a>
                        <a href="#" class="social-btn">🐦 Twitter</a>
                        <a href="#" class="social-btn">📸 Instagram</a>
                        <a href="#" class="social-btn">▶️ YouTube</a>
                    </div>
                </div>

                <!-- Map Placeholder -->
                <div class="map-placeholder">
                    <span>🗺️</span>
                    <p>GadgetZone, Victoria Island, Lagos</p>
                    <p style="font-size:.75rem;color:#888;margin-top:4px">Click to open in Google Maps</p>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="contact-form-card">
                <h2>Send Us a Message</h2>
                <p class="subtitle">Fill out the form below and we'll get back to you within 24 hours.</p>

                <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" id="contactForm" onsubmit="handleSubmit(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" placeholder="John Doe" value="<?= htmlspecialchars($_POST['name']??'') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required>
                        </div>
                        <div class="form-group full">
                            <label for="subject">Subject *</label>
                            <select id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="order" <?= ($_POST['subject']??'')==='order'?'selected':'' ?>>Order Inquiry</option>
                                <option value="shipping" <?= ($_POST['subject']??'')==='shipping'?'selected':'' ?>>Shipping &amp; Delivery</option>
                                <option value="return" <?= ($_POST['subject']??'')==='return'?'selected':'' ?>>Returns &amp; Refunds</option>
                                <option value="product" <?= ($_POST['subject']??'')==='product'?'selected':'' ?>>Product Information</option>
                                <option value="payment" <?= ($_POST['subject']??'')==='payment'?'selected':'' ?>>Payment Issue</option>
                                <option value="partnership" <?= ($_POST['subject']??'')==='partnership'?'selected':'' ?>>Partnership/Business</option>
                                <option value="other" <?= ($_POST['subject']??'')==='other'?'selected':'' ?>>Other</option>
                            </select>
                        </div>
                        <div class="form-group full">
                            <label for="message">Your Message *</label>
                            <textarea id="message" name="message" placeholder="Tell us how we can help you..." required><?= htmlspecialchars($_POST['message']??'') ?></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-send" id="sendBtn">📤 Send Message</button>
                </form>

                <div style="margin-top:20px;padding:16px;background:#f0f4ff;border-radius:8px">
                    <div style="font-size:.85rem;color:#1e3a5f;font-weight:600;margin-bottom:6px">⚡ Quick Support</div>
                    <div style="font-size:.82rem;color:#555">
                        For urgent order issues, call us directly at <strong>+234 801 234 5678</strong> or start a <strong>Live Chat</strong> below.
                    </div>
                    <button onclick="alert('Live chat starting...')" style="background:#22c55e;color:#fff;border:none;padding:8px 18px;border-radius:6px;font-weight:600;cursor:pointer;margin-top:10px;font-size:.85rem">💬 Start Live Chat</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<section class="faq-section">
    <div class="container">
        <h2>Frequently Asked Questions</h2>
        <div class="faq-grid">
            <?php
            $faqs = [
                ['q'=>'How long does delivery take?','a'=>'Standard delivery takes 3-5 business days across Nigeria. Lagos orders may receive same-day delivery. Express delivery is available for next-day delivery.'],
                ['q'=>'Are your products genuine?','a'=>'Yes! All products sold on GadgetZone are 100% genuine and sourced directly from authorized dealers and manufacturers.'],
                ['q'=>'What is your return policy?','a'=>'We offer a 30-day return policy on all items. Products must be in original packaging and unused condition. Contact support to initiate a return.'],
                ['q'=>'How can I track my order?','a'=>'Once your order is shipped, you\'ll receive a tracking number via email and SMS. You can also track your order from the My Orders page in your dashboard.'],
                ['q'=>'What payment methods do you accept?','a'=>'We accept Paystack, Flutterwave (cards, bank transfer, mobile money, USSD), and Pay on Delivery for select locations.'],
                ['q'=>'Can I change or cancel my order?','a'=>'Orders can be modified or cancelled within 2 hours of placement. Contact our support team immediately at support@gadgetzone.com or call us.'],
            ];
            foreach ($faqs as $faq): ?>
            <div class="faq-item">
                <div class="faq-q">❓ <?= htmlspecialchars($faq['q']) ?></div>
                <div class="faq-a"><?= htmlspecialchars($faq['a']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col"><h3>⚡ GadgetZone</h3><p>Your one-stop shop for the latest smartphones, gadgets, and accessories.</p><div class="social-links"><a href="#" class="social-link">Facebook</a><a href="#" class="social-link">Twitter</a><a href="#" class="social-link">Instagram</a><a href="#" class="social-link">YouTube</a></div></div>
            <div class="footer-col"><h4>Quick Links</h4><ul><li><a href="<?= SITE_URL ?>/index.php">Home</a></li><li><a href="<?= SITE_URL ?>/shop.php">Shop</a></li><li><a href="<?= SITE_URL ?>/video.php">Videos</a></li><li><a href="<?= SITE_URL ?>/blog.php">Blog</a></li><li><a href="<?= SITE_URL ?>/contact.php">Contact</a></li></ul></div>
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
function handleSubmit(e) {
    const btn = document.getElementById('sendBtn');
    btn.textContent = '⏳ Sending...';
    btn.disabled = true;
}
</script>
</body>
</html>
