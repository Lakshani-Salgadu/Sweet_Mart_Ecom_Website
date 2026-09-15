<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
requireLogin();
$pageTitle = "My Orders";
$uid  = (int)$_SESSION["user_id"];
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$orders = $stmt->get_result();

$statuses = ["Pending","Confirmed","Preparing","Out for Delivery","Delivered"];
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero"><div class="container"><h1 class="section-title">📦 My Orders</h1><p class="section-sub">Track your Sweet Mart orders</p></div></div>
<section class="section-pad">
  <div class="container">
    <?php if($orders->num_rows===0): ?>
    <div class="text-center py-5"><div style="font-size:4rem">📦</div><h3>No orders yet</h3><p style="color:#8a6a5a">You have not placed any orders. Start shopping!</p><a href="shop.php" class="btn-primary-sm mt-3">Shop Now</a></div>
    <?php else: 
      $itemStmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
      while($o=$orders->fetch_assoc()):
        $orderId = (int)$o['id'];
        $itemStmt->bind_param("i", $orderId);
        $itemStmt->execute();
        $items = $itemStmt->get_result();
        $si = array_search($o['status'],$statuses);
    ?>
    <div class="cart-summary-box mb-4">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
          <div style="font-size:.8rem;color:#8a6a5a;">ORDER #<?= str_pad($o['id'],5,'0',STR_PAD_LEFT) ?></div>
          <div style="font-size:.85rem;color:#8a6a5a;"><?= date('d M Y, H:i',strtotime($o['created_at'])) ?></div>
        </div>
        <div class="text-end">
          <span class="status-badge status-<?= str_replace(' ','',$o['status']) ?>"><?= $o['status'] ?></span>
          <div style="font-weight:700;font-size:1.1rem;margin-top:.3rem;"><?= price($o['total']) ?></div>
        </div>
      </div>
      <!-- Status Track -->
      <div class="order-status-track mb-4">
        <?php foreach($statuses as $i=>$s): ?>
        <div class="status-step <?= $i<$si?'done':($i===$si?'active':'') ?>">
          <div class="status-dot"><?= ["📋","✅","👩‍🍳","🚚","🎉"][$i] ?></div>
          <p><?= $s ?></p>
          <?php if($i<count($statuses)-1): ?><div class="step-line"></div><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <!-- Items -->
      <div style="border-top:1px solid #f0e8e0;padding-top:1rem;">
        <?php while($it=$items->fetch_assoc()): ?>
        <div class="d-flex justify-content-between py-1" style="font-size:.88rem;">
          <span><?= clean($it['product_name']) ?> × <?= $it['quantity'] ?></span>
          <span><?= price($it['unit_price']*$it['quantity']) ?></span>
        </div>
        <?php endwhile; ?>
      </div>
      <?php if($o['delivery_date']): ?>
      <div style="margin-top:.8rem;font-size:.82rem;color:#8a6a5a;">🗓 Estimated Delivery: <?= date('d M Y',strtotime($o['delivery_date'])) ?></div>
      <?php endif; ?>
    </div>
    <?php endwhile; endif; ?>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
