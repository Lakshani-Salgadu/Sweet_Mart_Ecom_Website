<?php
define("ADMIN_PAGE",true);
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";
requireAdmin();
$oid = (int)($_GET["id"] ?? 0);
if (!$oid) { header("Location: orders.php"); exit; }
$order = $conn->query("SELECT o.*,u.full_name AS customer FROM orders o LEFT JOIN users u ON o.user_id=u.id WHERE o.id=$oid")->fetch_assoc();
if (!$order) { header("Location: orders.php"); exit; }
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$oid");
$statuses = ["Pending","Confirmed","Preparing","Out for Delivery","Delivered"];
if ($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST["status"])) {
  $ns = $_POST["status"];
  $stmt = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
  $stmt->bind_param("si",$ns,$oid); $stmt->execute();
  setFlash("success","Order status updated to: ".$ns);
  header("Location: order_detail.php?id=$oid"); exit;
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Order #<?= $oid ?> | Sweet Mart Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/admin.css">
</head><body class="admin-body"><div class="admin-layout">
<?php require __DIR__ . "/../includes/admin_sidebar.php"; ?>
<main class="admin-main">
  <div class="admin-topbar">
    <button id="sidebarOpen" class="d-lg-none btn btn-sm" style="background:var(--pink-light)"><i class="bi bi-list"></i></button>
    <h2>Order #<?= str_pad($oid,5,"0",STR_PAD_LEFT) ?></h2>
    <a href="orders.php" class="btn-admin-primary">← All Orders</a>
  </div>
  <?php showFlash(); ?>
  <div class="row g-4">
    <div class="col-md-7">
      <div class="admin-card mb-4">
        <h5 class="mb-3">Customer &amp; Delivery</h5>
        <div class="row g-2" style="font-size:.9rem;">
          <div class="col-6"><b>Name:</b> <?= clean($order["full_name"]) ?></div>
          <div class="col-6"><b>Email:</b> <?= clean($order["email"]) ?></div>
          <div class="col-6"><b>Phone:</b> <?= clean($order["phone"]) ?></div>
          <div class="col-6"><b>City:</b> <?= clean($order["city"]) ?></div>
          <div class="col-12"><b>Address:</b> <?= clean($order["address"]) ?></div>
          <?php if($order["delivery_date"]): ?>
          <div class="col-6"><b>Delivery Date:</b> <?= date("d M Y",strtotime($order["delivery_date"])) ?></div>
          <?php endif; ?>
          <div class="col-6"><b>Payment:</b> <?= strtoupper($order["payment_method"]) ?></div>
        </div>
      </div>
      <div class="admin-card" style="overflow-x:auto;">
        <h5 class="mb-3">Order Items</h5>
        <table class="admin-table">
          <thead><tr><th>Product</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
          <tbody>
            <?php while($it=$items->fetch_assoc()): ?>
            <tr>
              <td><?= clean($it["product_name"]) ?></td>
              <td><?= $it["quantity"] ?></td>
              <td><?= price($it["unit_price"]) ?></td>
              <td><?= price($it["unit_price"]*$it["quantity"]) ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <div class="d-flex justify-content-end mt-3" style="gap:24px;font-size:.9rem;">
          <span>Subtotal: <b><?= price($order["subtotal"]) ?></b></span>
          <span>Delivery: <b><?= price($order["delivery_fee"]) ?></b></span>
          <span style="font-size:1.05rem;">Total: <b><?= price($order["total"]) ?></b></span>
        </div>
      </div>
    </div>
    <div class="col-md-5">
      <div class="admin-card">
        <h5 class="mb-3">Update Status</h5>
        <p>Current: <span class="status-badge status-<?= str_replace(" ","",$order["status"]) ?>"><?= $order["status"] ?></span></p>
        <form method="POST">
          <select name="status" class="form-control-admin mb-3" id="status-select">
            <?php foreach($statuses as $s): ?>
            <option <?= $order["status"]===$s?"selected":"" ?>><?= $s ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn-admin-primary w-100" id="update-status-btn">Update Status</button>
        </form>
      </div>
    </div>
  </div>
</main>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/main.js"></script>
</body></html>
