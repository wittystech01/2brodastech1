<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect(SITE_URL . '/dashboard.php');
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email     = sanitize($_POST['email']     ?? '');
    $phone     = sanitize($_POST['phone']     ?? '');
    $password  = $_POST['password']          ?? '';
    $confirm   = $_POST['confirm_password']  ?? '';
    $terms     = isset($_POST['terms']);

    if (!$full_name || !$email || !$phone || !$password || !$confirm) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!$terms) {
        $error = 'You must agree to the Terms & Conditions.';
    } else {
        try {
            $db  = getDB();
            $esc = $db->real_escape_string($email);
            $res = $db->query("SELECT id FROM users WHERE email='$esc' LIMIT 1");
            if ($res && $res->num_rows > 0) {
                $error = 'An account with this email already exists.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $fn   = $db->real_escape_string($full_name);
                $ph   = $db->real_escape_string($phone);
                $db->query("INSERT INTO users (full_name,email,phone,password,status,created_at) VALUES ('$fn','$esc','$ph','$hash','active',NOW())");
                $_SESSION['user_id']    = $db->insert_id;
                $_SESSION['user_name']  = $full_name;
                $_SESSION['user_email'] = $email;
                redirect(SITE_URL . '/dashboard.php?welcome=1');
            }
        } catch (Exception $e) {
            $error = 'Registration service is temporarily unavailable. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — GadgetZone</title>
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
        .auth-card{background:#fff;border-radius:20px;padding:40px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,.2)}
        .auth-logo{text-align:center;margin-bottom:24px}
        .auth-logo a{display:inline-flex;align-items:center;gap:8px;font-size:1.8rem;font-weight:800;color:#1e3a5f}
        .auth-logo .logo-icon{font-size:2rem}
        .auth-title{text-align:center;margin-bottom:24px}
        .auth-title h1{font-size:1.6rem;font-weight:700;color:#1e3a5f;margin-bottom:6px}
        .auth-title p{color:#888;font-size:.9rem}
        .alert{padding:12px 16px;border-radius:8px;font-size:.9rem;margin-bottom:20px;font-weight:500}
        .alert-error{background:#fef2f2;color:#dc2626;border-left:4px solid #dc2626}
        .alert-success{background:#f0fdf4;color:#16a34a;border-left:4px solid #16a34a}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:0 16px}
        .form-group{margin-bottom:16px}
        .form-group.full{grid-column:1/-1}
        label{display:block;font-size:.85rem;font-weight:600;color:#555;margin-bottom:6px}
        .input-wrap{position:relative}
        .input-wrap input{width:100%;padding:12px 16px 12px 44px;border:2px solid #eee;border-radius:10px;font-size:.9rem;outline:none;transition:border-color .2s}
        .input-wrap input:focus{border-color:#1e3a5f}
        .input-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:1rem;color:#aaa;pointer-events:none}
        .input-toggle{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem;color:#aaa}
        .password-strength{margin-top:6px}
        .strength-bar{height:4px;border-radius:2px;background:#eee;overflow:hidden}
        .strength-fill{height:100%;width:0;transition:all .3s;border-radius:2px}
        .strength-text{font-size:.75rem;color:#888;margin-top:3px}
        .terms-label{display:flex;align-items:flex-start;gap:10px;font-size:.85rem;color:#555;cursor:pointer;line-height:1.5}
        .terms-label input{accent-color:#1e3a5f;width:16px;height:16px;margin-top:2px;flex-shrink:0}
        .terms-label a{color:#ff6b35;font-weight:600}
        .btn-auth{width:100%;background:#ff6b35;color:#fff;border:none;padding:14px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;transition:background .3s;margin-top:20px}
        .btn-auth:hover{background:#e55a25}
        .divider{display:flex;align-items:center;gap:12px;margin:20px 0;color:#ccc;font-size:.85rem}
        .divider::before,.divider::after{content:'';flex:1;height:1px;background:#eee}
        .auth-footer{text-align:center;margin-top:20px;font-size:.9rem;color:#666}
        .auth-footer a{color:#ff6b35;font-weight:700}
        .auth-footer a:hover{text-decoration:underline}
        .benefits{display:flex;justify-content:center;gap:20px;margin-top:16px;flex-wrap:wrap}
        .benefit{font-size:.78rem;color:#888;display:flex;align-items:center;gap:4px}
        .bottom-nav{display:flex;position:fixed;bottom:0;left:0;right:0;background:#fff;border-top:1px solid #eee;z-index:1000;padding:8px 0;justify-content:space-around}
        .bottom-nav-item{display:flex;flex-direction:column;align-items:center;padding:4px 12px;color:#666;font-size:.65rem}
        .bottom-nav-item:hover{color:#1e3a5f}
        .bottom-nav-icon{font-size:1.4rem;margin-bottom:2px}
        @media(max-width:480px){.form-grid{grid-template-columns:1fr}}
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
            <h1>Create Your Account</h1>
            <p>Join thousands of satisfied customers</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" id="registerForm" onsubmit="handleSubmit(event)">
            <div class="form-grid">
                <div class="form-group full">
                    <label for="full_name">Full Name *</label>
                    <div class="input-wrap">
                        <span class="input-icon">👤</span>
                        <input type="text" id="full_name" name="full_name" placeholder="Your full name" value="<?= htmlspecialchars($_POST['full_name']??'') ?>" required autocomplete="name">
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <div class="input-wrap">
                        <span class="input-icon">📧</span>
                        <input type="email" id="email" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email']??'') ?>" required autocomplete="email">
                    </div>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number *</label>
                    <div class="input-wrap">
                        <span class="input-icon">📞</span>
                        <input type="tel" id="phone" name="phone" placeholder="080XXXXXXXX" value="<?= htmlspecialchars($_POST['phone']??'') ?>" required autocomplete="tel">
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password *</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔒</span>
                        <input type="password" id="password" name="password" placeholder="Min. 8 characters" required autocomplete="new-password" oninput="checkStrength(this.value)">
                        <button type="button" class="input-toggle" onclick="togglePwd('password')">👁</button>
                    </div>
                    <div class="password-strength">
                        <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
                        <div class="strength-text" id="strengthText"></div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <div class="input-wrap">
                        <span class="input-icon">🔑</span>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required autocomplete="new-password">
                        <button type="button" class="input-toggle" onclick="togglePwd('confirm_password')">👁</button>
                    </div>
                </div>
            </div>
            <div class="form-group" style="margin-top:4px">
                <label class="terms-label">
                    <input type="checkbox" name="terms" required>
                    I agree to the <a href="#">Terms &amp; Conditions</a> and <a href="#">Privacy Policy</a>
                </label>
            </div>
            <button type="submit" class="btn-auth" id="submitBtn">Create Account</button>
        </form>

        <div class="divider">Already have an account?</div>
        <div class="auth-footer"><a href="<?= SITE_URL ?>/login.php">Sign In to Your Account →</a></div>

        <div class="benefits">
            <div class="benefit">✅ Free registration</div>
            <div class="benefit">🛒 Track orders</div>
            <div class="benefit">💝 Exclusive deals</div>
        </div>
    </div>
</div>

<nav class="bottom-nav">
    <a href="<?= SITE_URL ?>/index.php" class="bottom-nav-item"><span class="bottom-nav-icon">🏠</span><span>Home</span></a>
    <a href="<?= SITE_URL ?>/shop.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛍️</span><span>Shop</span></a>
    <a href="<?= SITE_URL ?>/cart.php" class="bottom-nav-item"><span class="bottom-nav-icon">🛒</span><span>Cart</span></a>
    <a href="<?= SITE_URL ?>/login.php" class="bottom-nav-item"><span class="bottom-nav-icon">👤</span><span>Sign In</span></a>
</nav>

<script src="<?= SITE_URL ?>/js/main.js"></script>
<script src="<?= SITE_URL ?>/js/pwa.js"></script>
<script>
function togglePwd(id){const i=document.getElementById(id);i.type=i.type==='password'?'text':'password'}

function checkStrength(val){
    const fill=document.getElementById('strengthFill');
    const text=document.getElementById('strengthText');
    let score=0;
    if(val.length>=8) score++;
    if(/[A-Z]/.test(val)) score++;
    if(/[0-9]/.test(val)) score++;
    if(/[^A-Za-z0-9]/.test(val)) score++;
    const levels=[
        {pct:'25%',color:'#ef4444',label:'Weak'},
        {pct:'50%',color:'#f59e0b',label:'Fair'},
        {pct:'75%',color:'#3b82f6',label:'Good'},
        {pct:'100%',color:'#22c55e',label:'Strong'},
    ];
    if(val.length===0){fill.style.width='0';text.textContent='';return}
    const l=levels[score-1]||levels[0];
    fill.style.width=l.pct; fill.style.background=l.color; text.textContent=l.label; text.style.color=l.color;
}

function handleSubmit(e){
    const btn=document.getElementById('submitBtn');
    const pwd=document.getElementById('password').value;
    const cpwd=document.getElementById('confirm_password').value;
    if(pwd!==cpwd){e.preventDefault();alert('Passwords do not match!');return}
    btn.textContent='Creating account...'; btn.disabled=true;
}
</script>
</body>
</html>
