<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Order Confirmed";
$oid = (int)($_SESSION["last_order_id"] ?? 0);
if (!$oid) { header("Location: index.php"); exit; }
$stmt = $conn->prepare("SELECT * FROM orders WHERE id=?");
$stmt->bind_param("i",$oid); $stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$items = $conn->query("SELECT * FROM order_items WHERE order_id=$oid");
unset($_SESSION["last_order_id"]);
require __DIR__ . "/includes/header.php";
?>
<section class="section-pad" style="min-height:70vh;display:flex;align-items:center;">
  <div class="container text-center">
    <div style="font-size:5rem;margin-bottom:1rem;">🎉</div>
    <h1 class="section-title" style="color:var(--pink);">Order Placed Successfully!</h1>
    <p style="color:#7a5a4a;font-size:1.1rem;max-width:500px;margin:.5rem auto 2rem;">Thank you <?= clean($order["full_name"]) ?>! Your sweet treats are being prepared with love and care.</p>
    <div class="cart-summary-box text-start d-inline-block" style="min-width:380px;max-width:560px;text-align:left;">
      <div class="summary-row"><b>Order ID:</b><span>#<?= str_pad($oid,5,'0',STR_PAD_LEFT) ?></span></div>
      <div class="summary-row"><b>Status:</b><span class="status-badge status-<?= $order['status'] ?>"><?= $order['status'] ?></span></div>
      <div class="summary-row"><b>Payment:</b><span><?= strtoupper($order['payment_method']) ?></span></div>
      <div class="summary-row"><b>Delivery to:</b><span><?= clean($order['city']) ?></span></div>
      <?php if($order['delivery_date']): ?><div class="summary-row"><b>Delivery Date:</b><span><?= date('d M Y',strtotime($order['delivery_date'])) ?></span></div><?php endif; ?>
      <div class="summary-row total"><b>Total:</b><span><?= price($order['total']) ?></span></div>
    </div>
    <div class="mt-4 d-flex gap-3 justify-content-center">
      <a href="my_orders.php" class="btn-primary-sm" id="track-btn">Track My Order</a>
      <a href="shop.php" class="btn-outline-brown" id="shop-more-btn">Continue Shopping</a>
    </div>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
