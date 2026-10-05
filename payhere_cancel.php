<?php
// payhere_cancel.php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";

$pageTitle = "Payment Cancelled";
require __DIR__ . "/includes/header.php";
?>
<section class="section-pad" style="min-height:70vh;display:flex;align-items:center;">
  <div class="container text-center">
    <div style="font-size:5rem;margin-bottom:1rem;">&#10060;</div>
    <h1 class="section-title" style="color:var(--pink);">Payment Cancelled</h1>
    <p style="color:#7a5a4a;font-size:1.1rem;max-width:500px;margin:.5rem auto 2rem;">
      You cancelled the payment process. Your order has been saved as <b>Pending</b>. 
      You can try paying again later from your orders page or contact support if you need assistance.
    </p>
    
    <div class="mt-4 d-flex gap-3 justify-content-center">
      <a href="my_orders.php" class="btn-primary-sm">View My Orders</a>
      <a href="shop.php" class="btn-outline-brown">Continue Shopping</a>
    </div>
  </div>
</section>
<?php require __DIR__ . "/includes/footer.php"; ?>
