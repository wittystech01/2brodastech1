<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
if (isAdminLoggedIn()) { header('Location: ' . SITE_URL . '/admin/admin_dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ? AND status = 'active'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_name'] = $admin['name'];
            $_SESSION['admin_role'] = $admin['role'];
            header('Location: ' . SITE_URL . '/admin/admin_dashboard.php'); exit;
        } else { $error = 'Invalid email or password.'; }
    } catch (Exception $e) { $error = 'Database error. Please try again.'; }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Admin Login – GadgetZone</title>
<link rel="stylesheet" href="<?= SITE_URL ?>/css/admin.css">
<style>body{font-family:'Segoe UI',sans-serif;}</style></head>
<body class="admin-body">
<div class="admin-login-page">
  <div class="admin-login-card">
    <div class="admin-login-logo"><span style="font-size:2rem">⚡</span><div class="logo-text">GadgetZone</div><div class="admin-login-subtitle">Admin Panel</div></div>
    <?php if ($error): ?><div class="admin-alert admin-alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <form method="POST">
      <div class="admin-form-group"><label class="admin-form-label">Email Address</label><input type="email" name="email" class="admin-form-control" required placeholder="admin@gadgetzone.com"></div>
      <div class="admin-form-group"><label class="admin-form-label">Password</label><input type="password" name="password" class="admin-form-control" required placeholder="••••••••"></div>
      <button type="submit" class="btn-admin btn-admin-primary" style="width:100%;justify-content:center;padding:12px;font-size:1rem">Sign In →</button>
    </form>
    <p style="text-align:center;margin-top:20px;font-size:0.85rem;color:#777"><a href="<?= SITE_URL ?>/index.php" style="color:#1e3a5f">← Back to Website</a></p>
  </div>
</div>
</body></html>
