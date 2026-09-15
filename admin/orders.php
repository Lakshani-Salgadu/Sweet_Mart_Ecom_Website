<?php
define("ADMIN_PAGE",true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();
$orders = $conn->query("SELECT o.*,u.full_name AS customer FROM orders o LEFT JOIN users u ON o.user_id=u.id ORDER BY o.created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Orders | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/admin.css">
</head><body class="admin-body"><div class="admin-layout">
<?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2>🛒 All Orders</h2>
  </div>
  <?php showFlash(); ?>
  <div class="admin-card" style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
        <?php while($o=$orders->fetch_assoc()):
          $cnt = $conn->query("SELECT SUM(quantity) FROM order_items WHERE order_id=".$o["id"])->fetch_row()[0];
        ?>
        <tr>
          <td style="font-weight:600;">#<?= str_pad($o["id"],5,"0",STR_PAD_LEFT) ?></td>
          <td><?= clean($o["customer"]??$o["full_name"]) ?><br><span style="font-size:.75rem;color:#8a6a5a;"><?= clean($o["city"]) ?></span></td>
          <td style="font-size:.82rem;"><?= date("d M Y",strtotime($o["created_at"])) ?></td>
          <td><?= $cnt ?> items</td>
          <td style="font-weight:600;"><?= price($o["total"]) ?></td>
          <td><?= strtoupper($o["payment_method"]) ?></td>
          <td><span class="status-badge status-<?= str_replace(" ","",$o["status"]) ?>"><?= $o["status"] ?></span></td>
          <td><a href="order_detail.php?id=<?= $o["id"] ?>" class="btn-admin-info" style="text-decoration:none;color:#fff;border-radius:6px;padding:5px 12px;font-size:.78rem;">Manage</a></td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body></html>
