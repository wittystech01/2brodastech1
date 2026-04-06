<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();
$db = getDB();
$total_orders = $db->query("SELECT COUNT(*) as cnt FROM orders")->fetch_assoc()['cnt'] ?? 0;
$total_revenue = $db->query("SELECT SUM(total) as sum FROM orders WHERE payment_status='paid'")->fetch_assoc()['sum'] ?? 0;
$total_users = $db->query("SELECT COUNT(*) as cnt FROM users")->fetch_assoc()['cnt'] ?? 0;
$total_products = $db->query("SELECT COUNT(*) as cnt FROM products")->fetch_assoc()['cnt'] ?? 0;
$recent_orders = $db->query("SELECT o.*, u.name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id ORDER BY o.created_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - GadgetZone</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <div class="sidebar-brand">⚡ GadgetZone Admin</div>
    <nav class="sidebar-nav">
      <a href="admin_dashboard.php" class="sidebar-link active">📊 Dashboard</a>
      <a href="admin_products.php" class="sidebar-link">📦 Products</a>
      <a href="admin_add_product.php" class="sidebar-link">➕ Add Product</a>
      <a href="admin_categories.php" class="sidebar-link">📁 Categories</a>
      <a href="admin_orders.php" class="sidebar-link">🛒 Orders</a>
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
    <header class="admin-header">
      <h1>Dashboard</h1>
      <div class="admin-header-right">
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
      </div>
    </header>
    <div class="admin-tabs">
      <a href="admin_settings.php" class="admin-tab">Setup</a>
      <a href="admin_products.php" class="admin-tab">Products</a>
      <a href="admin_orders.php" class="admin-tab">Orders</a>
      <a href="admin_users.php" class="admin-tab">Customers</a>
      <a href="admin_coupons.php" class="admin-tab">Promo</a>
      <a href="#" class="admin-tab">Wishlist</a>
      <a href="admin_banner.php" class="admin-tab">Banners</a>
    </div>
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-info">
          <div class="stat-value"><?php echo number_format($total_orders); ?></div>
          <div class="stat-label">Total Orders</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">💰</div>
        <div class="stat-info">
          <div class="stat-value">₦<?php echo number_format($total_revenue, 0); ?></div>
          <div class="stat-label">Total Revenue</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-info">
          <div class="stat-value"><?php echo number_format($total_users); ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🛍️</div>
        <div class="stat-info">
          <div class="stat-value"><?php echo number_format($total_products); ?></div>
          <div class="stat-label">Total Products</div>
        </div>
      </div>
    </div>
    <div class="admin-card">
      <div class="card-header">
        <h2>Recent Orders</h2>
        <a href="admin_orders.php" class="btn btn-sm btn-primary">View All</a>
      </div>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Customer Name</th>
              <th>Products</th>
              <th>Status</th>
              <th>Date</th>
              <th>Total Price</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($recent_orders && $recent_orders->num_rows > 0): ?>
              <?php while ($order = $recent_orders->fetch_assoc()): ?>
              <tr>
                <td>#<?php echo htmlspecialchars($order['order_number']); ?></td>
                <td><?php echo htmlspecialchars($order['customer_name'] ?? 'Guest'); ?></td>
                <td>
                  <?php
                  $items = $db->query("SELECT product_name FROM order_items WHERE order_id = " . (int)$order['id'] . " LIMIT 2");
                  $item_names = [];
                  if ($items) while ($item = $items->fetch_assoc()) $item_names[] = $item['product_name'];
                  echo htmlspecialchars(implode(', ', $item_names) ?: 'N/A');
                  ?>
                </td>
                <td><span class="status-badge status-<?php echo htmlspecialchars($order['order_status']); ?>"><?php echo ucfirst(htmlspecialchars($order['order_status'])); ?></span></td>
                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                <td>₦<?php echo number_format($order['total'], 2); ?></td>
                <td><a href="admin_orders.php?id=<?php echo (int)$order['id']; ?>" class="btn btn-sm btn-outline">View</a></td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="7" class="text-center">No orders yet</td></tr>
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
