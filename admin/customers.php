<?php
define("ADMIN_PAGE",true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();
$customers = $conn->query("SELECT u.*,(SELECT COUNT(*) FROM orders WHERE user_id=u.id) AS order_count,(SELECT COALESCE(SUM(total),0) FROM orders WHERE user_id=u.id) AS total_spent FROM users u WHERE u.role='customer' ORDER BY u.created_at DESC");
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Customers | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/admin.css">
</head><body class="admin-body"><div class="admin-layout">
<?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2>👥 Customers</h2>
    <div style="font-size:.85rem;color:#8a6a5a;"><?= $customers->num_rows ?> registered customers</div>
  </div>
  <div class="admin-card" style="overflow-x:auto;">
    <table class="admin-table">
      <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Orders</th><th>Total Spent</th><th>Member Since</th></tr></thead>
      <tbody>
        <?php while($c=$customers->fetch_assoc()): ?>
        <tr>
          <td><?= $c["id"] ?></td>
          <td style="font-weight:600;">👤 <?= clean($c["full_name"]) ?></td>
          <td><?= clean($c["email"]) ?></td>
          <td><?= clean($c["phone"]) ?></td>
          <td><?= $c["order_count"] ?></td>
          <td><?= price($c["total_spent"]) ?></td>
          <td style="font-size:.82rem;"><?= date("d M Y",strtotime($c["created_at"])) ?></td>
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
