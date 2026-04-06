<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();
$db = getDB();

if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = sanitize($_POST['order_status']);
    $allowed = ['pending','confirmed','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $db->query("UPDATE orders SET order_status = '$status' WHERE id = $order_id");
    }
    header('Location: admin_orders.php?msg=updated');
    exit;
}

$search = sanitize($_GET['q'] ?? '');
$status_filter = sanitize($_GET['status'] ?? '');
$where = "WHERE 1=1";
if ($search) {
    $s = $db->real_escape_string($search);
    $where .= " AND (o.order_number LIKE '%$s%' OR o.customer_name LIKE '%$s%' OR o.customer_email LIKE '%$s%')";
}
if ($status_filter) {
    $sf = $db->real_escape_string($status_filter);
    $where .= " AND o.order_status = '$sf'";
}
$orders = $db->query("SELECT o.* FROM orders o $where ORDER BY o.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Orders - GadgetZone Admin</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <div class="sidebar-brand">⚡ GadgetZone Admin</div>
    <nav class="sidebar-nav">
      <a href="admin_dashboard.php" class="sidebar-link">📊 Dashboard</a>
      <a href="admin_products.php" class="sidebar-link">📦 Products</a>
      <a href="admin_add_product.php" class="sidebar-link">➕ Add Product</a>
      <a href="admin_categories.php" class="sidebar-link">📁 Categories</a>
      <a href="admin_orders.php" class="sidebar-link active">🛒 Orders</a>
      <a href="admin_users.php" class="sidebar-link">👥 Users</a>
      <a href="admin_videos.php" class="sidebar-link">🎥 Videos</a>
      <a href="admin_channels.php" class="sidebar-link">📺 Channels</a>
      <a href="admin_coupons.php" class="sidebar-link">🎟️ Coupons</a>
      <a href="admin_banner.php" class="sidebar-link">🖼️ Banners</a>
      <a href="admin_settings.php" class="sidebar-link">⚙️ Settings</a>
      <a href="admin_payment.php" class="sidebar-link">💳 Payments</a>
      <a href="admin_logo.php" class="sidebar-link">🏷️ Logo</a>
      <a href="admin_logout.php" class="sidebar-link logout">🚪 Logout</a>
    </nav>
  </aside>
  <main class="admin-content">
    <header class="admin-header"><h1>Manage Orders</h1></header>
    <?php if (isset($_GET['msg'])): ?><div class="alert alert-success">Order updated successfully.</div><?php endif; ?>
    <div class="admin-card">
      <form method="GET" class="admin-search-form" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search orders..." class="admin-input" style="flex:1;">
        <select name="status" class="admin-input" style="width:150px;">
          <option value="">All Statuses</option>
          <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
          <option value="<?php echo $s; ?>" <?php echo $status_filter === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
      </form>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Payment</th><th>Order Status</th><th>Date</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php if ($orders && $orders->num_rows > 0): ?>
              <?php while ($o = $orders->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($o['order_number']); ?></td>
                <td><?php echo htmlspecialchars($o['customer_name']); ?><br><small><?php echo htmlspecialchars($o['customer_email']); ?></small></td>
                <td>₦<?php echo number_format($o['total'], 2); ?></td>
                <td><span class="status-badge status-<?php echo $o['payment_status']; ?>"><?php echo ucfirst($o['payment_status']); ?></span></td>
                <td>
                  <form method="POST" style="display:inline;">
                    <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                    <select name="order_status" class="admin-input" style="width:130px;padding:4px;">
                      <?php foreach (['pending','confirmed','processing','shipped','delivered','cancelled'] as $s): ?>
                      <option value="<?php echo $s; ?>" <?php echo $o['order_status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <button type="submit" name="update_status" class="btn btn-sm btn-primary">Update</button>
                  </form>
                </td>
                <td><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                <td><a href="#" class="btn btn-sm btn-outline" onclick="alert('Order details: #<?php echo $o['order_number']; ?>')">View</a></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No orders found</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<script src="../js/admin.js"></script>
</body>
</html>
