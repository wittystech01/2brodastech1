<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdminLogin();
$db = getDB();

// Handle delete
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->query("UPDATE products SET status = 'inactive' WHERE id = $id");
    header('Location: admin_products.php?msg=deleted');
    exit;
}

$search = sanitize($_GET['q'] ?? '');
$where = "WHERE 1=1";
if ($search) {
    $s = $db->real_escape_string($search);
    $where .= " AND p.name LIKE '%$s%'";
}
$products = $db->query("SELECT p.*, c.name as cat FROM products p LEFT JOIN categories c ON p.category_id = c.id $where ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products - GadgetZone Admin</title>
<link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrapper">
  <aside class="admin-sidebar">
    <div class="sidebar-brand">⚡ GadgetZone Admin</div>
    <nav class="sidebar-nav">
      <a href="admin_dashboard.php" class="sidebar-link">📊 Dashboard</a>
      <a href="admin_products.php" class="sidebar-link active">📦 Products</a>
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
      <h1>Manage Products</h1>
      <a href="admin_add_product.php" class="btn btn-primary">+ Add Product</a>
    </header>
    <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success">Action completed successfully.</div>
    <?php endif; ?>
    <div class="admin-card">
      <form method="GET" class="admin-search-form">
        <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search products..." class="admin-input">
        <button type="submit" class="btn btn-primary">Search</button>
      </form>
      <div class="table-responsive">
        <table class="admin-table">
          <thead>
            <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Sale Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php if ($products && $products->num_rows > 0): ?>
              <?php while ($p = $products->fetch_assoc()): ?>
              <tr>
                <td><?php echo $p['id']; ?></td>
                <td><img src="../assets/images/<?php echo htmlspecialchars($p['image']); ?>" alt="" style="width:50px;height:50px;object-fit:cover;border-radius:4px;" onerror="this.src='../assets/images/placeholder.jpg'"></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['cat'] ?? '-'); ?></td>
                <td>₦<?php echo number_format($p['price'], 2); ?></td>
                <td><?php echo $p['sale_price'] ? '₦' . number_format($p['sale_price'], 2) : '-'; ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td><span class="status-badge status-<?php echo $p['status']; ?>"><?php echo ucfirst($p['status']); ?></span></td>
                <td>
                  <a href="admin_edit_product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm btn-outline">Edit</a>
                  <a href="admin_products.php?delete=<?php echo $p['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this product?')">Del</a>
                </td>
              </tr>
              <?php endwhile; ?>
            <?php else: ?>
              <tr><td colspan="9" class="text-center">No products found</td></tr>
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
