<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        try {
            $db   = getDB();
            $esc  = $db->real_escape_string($email);
            $res  = $db->query("SELECT * FROM users WHERE email='$esc' AND status='active' LIMIT 1");
            $user = $res ? $res->fetch_assoc() : null;

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['name'] ?? 'User';
                $_SESSION['user_email']= $user['email'];

                if ($remember) {
                    $token = generateToken();
                    setcookie('gz_remember', $token, time()+60*60*24*30, '/', '', false, true);
                }

                $redirect = $_GET['redirect'] ?? (SITE_URL . '/dashboard.php');
                redirect($redirect);
            } else {
                $error = 'Invalid email or password. Please try again.';
            }
        } catch (Exception $e) {
            $error = 'Login service is temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — GadgetZone</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/mobile.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/css/responsive.css">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#1e3a5f">
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Segoe UI',system-ui,sans-serif;color:#333;background:linear-gradient(135deg,#1e3a5f 0%,#2d5f8f 100%);min-height:100vh;display:flex;flex-direction:column}
        a{text-decoration:none;color:inherit}
        .auth-page{flex:1;display:flex;align-items:center;justify-content:center;padding:40px 20px}
        .auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,.2)}
        .auth-logo{text-align:center;margin-bottom:28px}
        .auth-logo a{display:inline-flex;align-items:center;gap:8px;font-size:1.8rem;font-weight:800;color:#1e3a5f}
        .auth-logo .logo-icon{font-size:2rem}
        .auth-title{text-align:center;margin-bottom:28px}
        .auth-title h1{font-size:1.6rem;font-weight:700;color:#1e3a5f;margin-bottom:6px}
        .auth-title p{color:#888;font-size:.9rem}
        .alert{padding:12px 16px;border-radius:8px;font-size:.9rem;margin-bottom:20px;font-weight:500}
        .alert-error{background:#fef2f2;color:#dc2626;border-left:4px solid #dc2626}
        .alert-success{background:#f0fdf4;color:#16a34a;border-left:4px solid #16a34a}
        .form-group{margin-bottom:18px}
        label{display:block;font-size:.85rem;font-weight:600;color:#555;margin-bottom:6px}
        .input-wrap{position:relative}
        .input-wrap input{width:100%;padding:13px 16px 13px 44px;border:2px solid #eee;border-radius:10px;font-size:.95rem;outline:none;transition:border-color .2s;background:#fff}
        .input-wrap input:focus{border-color:#1e3a5f}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:1rem;color:#aaa;pointer-events:none}
        .input-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;color:#aaa}
        .form-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .remember-label{display:flex;align-items:center;gap:8px;font-size:.85rem;color:#555;cursor:pointer}
        .remember-label input{accent-color:#1e3a5f;width:16px;height:16px}
        .forgot-link{font-size:.85rem;color:#ff6b35;font-weight:600}
        .forgot-link:hover{text-decoration:underline}
        .btn-auth{width:100%;background:#ff6b35;color:#fff;border:none;padding:14px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .3s}
        .btn-auth:hover{background:#e55a25}
        .divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#ccc;font-size:.85rem}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#eee}
        .social-auth{display:flex;flex-direction:column;gap:10px}
        .btn-social{display:flex;align-items:center;justify-content:center;gap:10px;padding:12px;border:2px solid #eee;border-radius:10px;font-size:.9rem;font-weight:600;cursor:pointer;background:#fff;transition:border-color .2s;color:#333}
        .btn-social:hover{border-color:#1e3a5f}
        .auth-footer{text-align:center;margin-top:24px;font-size:.9rem;color:#666}
        .auth-footer a{color:#ff6b35;font-weight:700}
        .auth-footer a:hover{text-decoration:underline}
        .bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem}
        .bottom-nav-item:hover{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
    </style>
</head>
<body>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <a href="<?= SITE_URL ?>/index.php">
                <span class="logo-icon">⚡</span>
                <span>GadgetZone</span>
            </a>
        </div>
        <div class="auth-title">
            <h1>Welcome Back!</h1>
            <p>Sign in to your account to continue</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" id="loginForm" onsubmit="handleSubmit(event)">
            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrap">
                    <span class="input-icon">📧</span>
                    <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required autocomplete="email">
                </div>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                    <button type="button" class="input-toggle" onclick="togglePwd()" title="Show/hide password">👁</button>
                </div>
            </div>
            <div class="form-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="<?= SITE_URL ?>/forgot-password.php" class="forgot-link">Forgot Password?</a>
            </div>
            <button type="submit" class="btn-auth" id="submitBtn">Sign In</button>
        </form>

        <div class="divider">or continue with</div>
        <div class="social-auth">
            <button class="btn-social" onclick="alert('Social login coming soon!')">🌐 Continue with Google</button>
        </div>

        <div class="auth-footer">
            Don't have an account? <a href="<?= SITE_URL ?>/register.php">Sign Up Free</a>
        </div>
    </div>
</div>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item"><span class="bottom-nav-icon">🏠</span><span>Home</span></a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛍️</span><span>Shop</span></a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛒</span><span>Cart</span></a>
    <a href="<?= SITE_URL ?>/dashboard.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span>Profile</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
function togglePwd(){
    const i=document.getElementById('password');
    i.type = i.type==='password'?'text':'password';
}
function handleSubmit(e){
    const btn=document.getElementById('submitBtn');
    btn.textContent='Signing in...';
    btn.disabled=true;
}
</script>
</body>
</html>
