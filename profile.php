<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isLoggedIn()) {
    redirect(SITE_URL . '/login.php?redirect=' . urlencode('/profile.php'));
}

$user_id  = (int)$_SESSION['user_id'];
$user     = [];
$success  = '';
$error    = '';

try {
    $db  = getDB();
    $res = $db->query("SELECT * FROM users WHERE id=$user_id AND status='active' LIMIT 1");
    $user = $res ? $res->fetch_assoc() : [];
} catch (Exception $e) { $user = []; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $full_name = sanitize($_POST['full_name'] ?? '');
        $email     = sanitize($_POST['email']     ?? '');
        $phone     = sanitize($_POST['phone']     ?? '');
        $address   = sanitize($_POST['address']   ?? '');

        if (!$full_name || !$email) {
            $error = 'Name and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } else {
            try {
                $db  = getDB();
                $fn  = $db->real_escape_string($full_name);
                $em  = $db->real_escape_string($email);
                $ph  = $db->real_escape_string($phone);
                $ad  = $db->real_escape_string($address);
                $db->query("UPDATE users SET full_name='$fn',email='$em',phone='$ph',address='$ad' WHERE id=$user_id");
                $_SESSION['user_name']  = $full_name;
                $_SESSION['user_email'] = $email;
                $success = 'Profile updated successfully!';
                $user['full_name'] = $full_name; $user['email'] = $email; $user['phone'] = $phone; $user['address'] = $address;
            } catch (Exception $e) { $error = 'Failed to update profile.'; }
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $newpwd  = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!$current || !$newpwd || !$confirm) {
            $error = 'All password fields are required.';
        } elseif (strlen($newpwd) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($newpwd !== $confirm) {
            $error = 'New passwords do not match.';
        } else {
            try {
                $db  = getDB();
                $res = $db->query("SELECT password FROM users WHERE id=$user_id LIMIT 1");
                $row = $res ? $res->fetch_assoc() : null;
                if ($row && password_verify($current, $row['password'])) {
                    $hash = password_hash($newpwd, PASSWORD_DEFAULT);
                    $db->query("UPDATE users SET password='$hash' WHERE id=$user_id");
                    $success = 'Password changed successfully!';
                } else {
                    $error = 'Current password is incorrect.';
                }
            } catch (Exception $e) { $error = 'Failed to change password.'; }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Settings — GadgetZone</title>
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
        .profile-page{padding:40px 0}
        .profile-layout{display:grid;grid-template-columns:280px 1fr;gap:24px}
        .profile-sidebar{height:fit-content;position:sticky;top:80px}
        .avatar-card{background:#fff;border-radius:14px;padding:24px;text-align:center;box-shadow:0 2px 10px rgba(0,0,0,.06)}
        .avatar-wrap{position:relative;display:inline-block;margin-bottom:14px}
        .user-avatar{width:100px;height:100px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#ff6b35);display:flex;align-items:center;justify-content:center;font-size:3rem;cursor:pointer}
        .avatar-edit{position:absolute;bottom:0;right:0;background:#ff6b35;border-radius:50%;width:30px;height:30px;display:flex;align-items:center;justify-content:center;font-size:.75rem;cursor:pointer;color:#fff}
        .user-name{font-size:1.1rem;font-weight:700;color:#1e3a5f;margin-bottom:4px}
        .user-email{font-size:.85rem;color:#888;margin-bottom:12px}
        .member-badge{display:inline-block;background:#e8f0fe;color:#1e3a5f;padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600}
        .sidebar-nav{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden;margin-top:16px}
        .sidebar-nav a{display:flex;align-items:center;gap:10px;padding:14px 20px;color:#555;font-size:.9rem;font-weight:500;border-bottom:1px solid #f8f9fa;transition:all .2s}
        .sidebar-nav a:hover,.sidebar-nav a.active{background:#f0f4ff;color:#1e3a5f;font-weight:600}
        .sidebar-nav a.logout{color:#ef4444}
        .sidebar-nav a.logout:hover{background:#fef2f2}
        .profile-sections{display:flex;flex-direction:column;gap:24px}
        .form-section{background:#fff;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,.06);overflow:hidden}
        .section-head{padding:20px 24px;border-bottom:2px solid #f0f0f0;display:flex;align-items:center;gap:10px}
        .section-head h3{font-size:1rem;font-weight:700;color:#1e3a5f}
        .section-body{padding:24px}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-group.full{grid-column:1/-1}
        label{font-size:.85rem;font-weight:600;color:#555}
        input[type=text],input[type=email],input[type=tel],input[type=password],textarea{padding:12px 14px;border:2px solid #eee;border-radius:8px;font-size:.9rem;outline:none;transition:border-color .2s;font-family:inherit;width:100%}
        input:focus,textarea:focus{border-color:#1e3a5f}
        .btn-save{background:#ff6b35;color:#fff;border:none;padding:12px 28px;border-radius:8px;font-weight:700;font-size:.95rem;cursor:pointer;transition:background .3s}
        .btn-save:hover{background:#e55a25}
        .alert{padding:12px 16px;border-radius:8px;font-size:.9rem;margin-bottom:16px;font-weight:500}
        .alert-success{background:#f0fdf4;color:#15803d;border-left:4px solid #22c55e}
        .alert-error{background:#fef2f2;color:#dc2626;border-left:4px solid #ef4444}
        .avatar-upload{display:none}
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
        @media(max-width:768px){.profile-layout{grid-template-columns:1fr}.profile-sidebar{position:static}.form-grid{grid-template-columns:1fr}.footer-grid{grid-template-columns:repeat(2,1fr)}}
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
        <a href="<?= SITE_URL ?>/dashboard.php">👤 My Account</a><a href="<?= SITE_URL ?>/cart.php">🛒 Cart</a>
    </nav>
</div>
<div class="mobile-overlay" id="mobileOverlay"></div>

<div class="page-header">
    <div class="container">
        <h1>⚙️ Profile Settings</h1>
        <div class="breadcrumb"><a href="<?= SITE_URL ?>/index.php">Home</a> / <a href="<?= SITE_URL ?>/dashboard.php">Dashboard</a> / Profile</div>
    </div>
</div>

<div class="profile-page">
    <div class="container">
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="avatar-card">
                    <div class="avatar-wrap" onclick="document.getElementById('avatarFile').click()">
                        <div class="user-avatar">👤</div>
                        <div class="avatar-edit">✏️</div>
                    </div>
                    <input type="file" id="avatarFile" class="avatar-upload" accept="image/*" onchange="previewAvatar(this)">
                    <div class="user-name"><?= htmlspecialchars($user['full_name'] ?? $_SESSION['user_name'] ?? 'User') ?></div>
                    <div class="user-email"><?= htmlspecialchars($user['email'] ?? '') ?></div>
                    <div class="member-badge">⚡ GadgetZone Member</div>
                </div>
                <nav class="sidebar-nav">
                    <a href="<?= SITE_URL ?>/dashboard.php">🏠 Dashboard</a>
                    <a href="<?= SITE_URL ?>/orders.php">📦 My Orders</a>
                    <a href="<?= SITE_URL ?>/wishlist.php">❤️ Wishlist</a>
                    <a href="<?= SITE_URL ?>/profile.php" class="active">⚙️ Profile Settings</a>
                    <a href="<?= SITE_URL ?>/api/auth.php?action=logout" class="logout">🚪 Sign Out</a>
                </nav>
            </aside>

            <!-- Main -->
            <div class="profile-sections">
                <?php if ($success): ?>
                <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <!-- Personal Info -->
                <div class="form-section">
                    <div class="section-head">
                        <span style="font-size:1.3rem">👤</span>
                        <h3>Personal Information</h3>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email Address *</label>
                                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
                                </div>
                                <div class="form-group full">
                                    <label for="address">Delivery Address</label>
                                    <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Street, area, postal code">
                                </div>
                            </div>
                            <div style="margin-top:20px">
                                <button type="submit" class="btn-save">💾 Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="form-section">
                    <div class="section-head">
                        <span style="font-size:1.3rem">🔒</span>
                        <h3>Change Password</h3>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            <div class="form-grid">
                                <div class="form-group full">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                                </div>
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" placeholder="Min. 8 characters">
                                </div>
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat new password">
                                </div>
                            </div>
                            <div style="margin-top:20px">
                                <button type="submit" class="btn-save">🔑 Update Password</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Avatar Upload Section -->
                <div class="form-section">
                    <div class="section-head">
                        <span style="font-size:1.3rem">📷</span>
                        <h3>Profile Photo</h3>
                    </div>
                    <div class="section-body">
                        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
                            <div id="avatarPreview" style="width:80px;height:80px;border-radius:50%;background:linear-gradient(135deg,#1e3a5f,#ff6b35);display:flex;align-items:center;justify-content:center;font-size:2rem;overflow:hidden"></div>
                            <div>
                                <p style="color:#555;font-size:.9rem;margin-bottom:12px">Upload a profile photo (JPG, PNG — max 2MB)</p>
                                <button type="button" onclick="document.getElementById('avatarFile').click()" style="background:#1e3a5f;color:#fff;border:none;padding:10px 20px;border-radius:8px;font-weight:600;cursor:pointer">📷 Choose Photo</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item active"><span class="bottom-nav-icon">👤</span><span class="bottom-nav-label">Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/cart.js"></script>
<script src="<?= SITE_URL ?>/js/mobile-nav.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            const prev = document.getElementById('avatarPreview');
            prev.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>
