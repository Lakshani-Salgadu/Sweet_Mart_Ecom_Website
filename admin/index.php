<?php
define("ADMIN_PAGE",true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();
$pageTitle = "Admin Dashboard";
$totalProducts  = $conn->query("SELECT COUNT(*) FROM products")->fetch_row()[0];
$totalOrders    = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$totalCustomers = $conn->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetch_row()[0];
$totalSales     = $conn->query("SELECT COALESCE(SUM(total),0) FROM orders WHERE status='Delivered'")->fetch_row()[0];
$recentOrders   = $conn->query("SELECT o.*,u.full_name AS customer FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC LIMIT 8");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-layout">
<?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2>🏠 Dashboard</h2>
    <div style="font-size:.85rem;color:#8a6a5a;"><?= date('l, d F Y') ?></div>
  </div>
  <!-- Stats -->
  <div class="row g-4 mb-4">
    <?php
    $stats=[
      ["📦","Total Products",$totalProducts,"#d4e8d4","products.php"],
      ["🛒","Total Orders",$totalOrders,"#fce8d4","orders.php"],
      ["👥","Total Customers",$totalCustomers,"#d4e0f0","customers.php"],
      ["💰","Total Sales (LKR)",number_format($totalSales,2),"#fce8e8","orders.php"],
    ];
    foreach($stats as [$icon,$label,$val,$bg,$link]):
    ?>
    <div class="col-xl-3 col-md-6">
      <div class="stat-card" style="border-left-color:transparent;background:<?= $bg ?>;">
        <div class="stat-icon"><?= $icon ?></div>
        <div class="stat-value" data-count="<?= is_numeric($val)?$val:'' ?>"><?= $val ?></div>
        <div class="stat-label"><?= $label ?> &nbsp;<a href="<?= $link ?>" style="font-size:.75rem;color:var(--brown);">View →</a></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <!-- Recent Orders -->
  <div class="admin-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h5 style="margin:0;">Recent Orders</h5>
      <a href="orders.php" class="btn-admin-primary">View All</a>
    </div>
    <div style="overflow-x:auto;">
      <table class="admin-table">
        <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
          <?php while($o=$recentOrders->fetch_assoc()): ?>
          <tr>
            <td>#<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></td>
            <td><?= clean($o['customer']??$o['full_name']) ?></td>
            <td style="font-size:.82rem;"><?= date('d M Y',strtotime($o['created_at'])) ?></td>
            <td><?= price($o['total']) ?></td>
            <td><?= strtoupper($o['payment_method']) ?></td>
            <td><span class="status-badge status-<?= str_replace(' ','',$o['status']) ?>"><?= $o['status'] ?></span></td>
            <td><a href="order_detail.php?id=<?= $o['id'] ?>" class="btn-admin-info" style="border-radius:6px;padding:5px 12px;font-size:.78rem;text-decoration:none;color:#fff;">View</a></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body></html>
