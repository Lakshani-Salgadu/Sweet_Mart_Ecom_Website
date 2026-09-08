<?php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
$pageTitle = "Shopping Cart";
// Handle form updates
if ($_SERVER["REQUEST_METHOD"]==="POST") {
  if (isset($_POST["remove"])) {
    removeFromCart((int)$_POST["remove"]);
    setFlash("success","Item removed.");
  } elseif (isset($_POST["update"])) {
    foreach ($_POST["qty"] as $pid => $qty) {
      updateCartQty((int)$pid, (int)$qty);
    }
    setFlash("success","Cart updated.");
  }
  header("Location: cart.php"); exit;
}
// Load cart items from session
$cartItems = [];
$subtotal  = 0;
if (!empty($_SESSION["cart"])) {
  $ids = implode(",", array_map("intval", array_keys($_SESSION["cart"])));
  $rows = $conn->query("SELECT * FROM products WHERE id IN ($ids)");
  while ($r = $rows->fetch_assoc()) {
    $qty = $_SESSION["cart"][$r["id"]] ?? 1;
    $r["qty"] = $qty;
    $r["line_total"] = $r["price"] * $qty;
    $subtotal += $r["line_total"];
    $cartItems[] = $r;
  }
}
$delivery = getDeliveryFee($subtotal);
$total    = $subtotal + $delivery;
// Custom box in cart
$customBox = $_SESSION["custom_box"] ?? null;
if ($customBox) {
  $subtotal  += $customBox["total_price"];
  $delivery   = getDeliveryFee($subtotal);
  $total      = $subtotal + $delivery;
}
require __DIR__ . "/includes/header.php";
?>
<div class="page-hero"><div class="container"><h1 class="section-title">🛒 Your Cart</h1></div></div>
<section class="section-pad">
  <div class="container">
    <?php if (empty($cartItems) && !$customBox): ?>
      <div class="text-center py-5">
        <div style="font-size:5rem;">🛒</div>
        <h3 style="margin-top:1rem;">Your cart is empty</h3>
        <p style="color:#8a6a5a;margin:.5rem 0 2rem;">Looks like you haven't added anything yet!</p>
        <a href="shop.php" class="btn-primary-sm">Start Shopping</a>
      </div>
    <?php else: ?>
    <div class="row g-4">
      <div class="col-lg-8">
        <form method="POST">
          <div class="cart-summary-box mb-3" style="overflow-x:auto;">
            <table class="cart-table">
              <thead>
                <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th><th></th></tr>
              </thead>
              <tbody>
                <?php foreach ($cartItems as $item): ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div style="width:60px;height:60px;border-radius:8px;background:var(--cream);display:flex;align-items:center;justify-content:center;font-size:2rem;overflow:hidden;">
                        <?= productImage($item["image"],$item["name"]) ?>
                      </div>
                      <div>
                        <div style="font-weight:600;font-size:.92rem;"><?= clean($item["name"]) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= price($item["price"]) ?></td>
                  <td>
                    <div class="qty-control">
                      <button type="button" class="qty-btn" data-dir="down">−</button>
                      <input type="number" name="qty[<?= $item['id'] ?>]" class="qty-input" value="<?= $item['qty'] ?>" min="1" max="99">
                      <button type="button" class="qty-btn" data-dir="up">+</button>
                    </div>
                  </td>
                  <td style="font-weight:600;"><?= price($item["line_total"]) ?></td>
                  <td>
                    <button type="submit" name="remove" value="<?= $item['id'] ?>" class="btn-view" id="rm-<?= $item['id'] ?>" style="background:none;border:none;color:#c0392b;font-size:1.2rem;cursor:pointer;">🗑</button>
                  </td>
                </tr>
                <?php endforeach; ?>
                <?php if ($customBox): ?>
                <tr>
                  <td><div style="font-weight:600;">🎁 Custom Sweet Box<br><small style="color:#8a6a5a;font-size:.78rem;"><?= clean($customBox["occasion"] ?? "") ?> — <?= clean($customBox["size"] ?? "") ?></small></div></td>
                  <td><?= price($customBox["total_price"]) ?></td>
                  <td>1</td>
                  <td style="font-weight:600;"><?= price($customBox["total_price"]) ?></td>
                  <td><a href="cart.php?remove_box=1" style="color:#c0392b;font-size:1.2rem;">🗑</a></td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="d-flex gap-3 flex-wrap">
            <a href="shop.php" class="btn-outline-brown" id="continue-btn">← Continue Shopping</a>
            <button type="submit" name="update" class="btn-primary-sm" id="update-cart-btn">Update Cart</button>
          </div>
        </form>
      </div>
      <!-- Summary -->
      <div class="col-lg-4">
        <div class="cart-summary-box">
          <h5 style="margin-bottom:1.5rem;">Order Summary</h5>
          <div class="summary-row"><span>Subtotal</span><span><?= price($subtotal) ?></span></div>
          <div class="summary-row"><span>Delivery Fee</span><span><?= $delivery==0?'<span style="color:green">Free</span>':price($delivery) ?></span></div>
          <?php if ($delivery > 0): ?>
          <div style="font-size:.78rem;color:#8a6a5a;padding:6px 0;">Free delivery on orders over LKR 5,000</div>
          <?php endif; ?>
          <div class="summary-row total"><span>Total</span><span><?= price($total) ?></span></div>
          <a href="checkout.php" class="btn-primary-sm w-100 text-center mt-4 d-block" id="checkout-btn" style="text-align:center;">Proceed to Checkout →</a>
        </div>
        <div class="cart-summary-box mt-3" style="font-size:.85rem;color:#8a6a5a;">
          <div>🔒 Secure checkout</div>
          <div class="mt-1">🚚 Delivery within 1–3 days</div>
          <div class="mt-1">🎁 Gift wrapping available</div>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php
// Remove custom box
if (isset($_GET["remove_box"])) { unset($_SESSION["custom_box"]); header("Location: cart.php"); exit; }
require __DIR__ . "/includes/footer.php";
?>
