<?php
// payhere_process.php
session_start();
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/functions.php";
require_once __DIR__ . "/includes/payhere_config.php";

$userId = $_SESSION["user_id"] ?? null;
if (!$userId) {
    header("Location: login.php");
    exit;
}

$orderId = $_GET["order_id"] ?? null;
if (!$orderId) {
    header("Location: index.php");
    exit;
}

// Fetch order
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or access denied.");
}

if ($order["status"] !== "Pending") {
    die("This order cannot be paid for now.");
}

// Fetch order items for description
$is = $conn->prepare("SELECT product_name FROM order_items WHERE order_id = ?");
$is->bind_param("i", $orderId);
$is->execute();
$res = $is->get_result();
$items = [];
while ($row = $res->fetch_assoc()) {
    $items[] = $row["product_name"];
}
$orderDescription = implode(", ", $items);
if (empty($orderDescription)) $orderDescription = "Sweet Mart Order #" . $orderId;

$merchantId = PAYHERE_MERCHANT_ID;
$merchantSecret = PAYHERE_MERCHANT_SECRET;
$currency = PAYHERE_CURRENCY;
$amount = number_format($order["total"], 2, '.', ''); // Ensure 2 decimal places
$orderIdSafe = $order["id"]; // Use DB value safely

$hash = strtoupper(
    md5(
        $merchantId . 
        $orderIdSafe . 
        $amount . 
        $currency .  
        strtoupper(md5($merchantSecret)) 
    ) 
);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="referrer" content="origin">
    <title>Redirecting to PayHere...</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; background: #fdfaf6; }
        .loader { border: 5px solid #f3f3f3; border-top: 5px solid #b87a64; border-radius: 50%; width: 40px; height: 40px; animation: spin 1s linear infinite; margin-bottom: 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
    <div class="loader"></div>
    <h3>Redirecting to Secure Payment Gateway...</h3>
    <p>Please wait, do not close this window.</p>

    <form method="post" action="https://sandbox.payhere.lk/pay/checkout" id="payhere-form">   
        <input type="hidden" name="merchant_id" value="<?= htmlspecialchars($merchantId) ?>">    <!-- Replace your Merchant ID -->
        <input type="hidden" name="return_url" value="<?= htmlspecialchars(PAYHERE_RETURN_URL) ?>">
        <input type="hidden" name="cancel_url" value="<?= htmlspecialchars(PAYHERE_CANCEL_URL) ?>">
        <input type="hidden" name="notify_url" value="<?= htmlspecialchars(PAYHERE_NOTIFY_URL) ?>">  
        <?php 
            $nameParts = explode(" ", $order["full_name"], 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '.';
        ?>
        <input type="hidden" name="first_name" value="<?= htmlspecialchars($firstName) ?>">
        <input type="hidden" name="last_name" value="<?= htmlspecialchars($lastName) ?>">
        <input type="hidden" name="email" value="<?= htmlspecialchars($order["email"]) ?>">
        <input type="hidden" name="phone" value="<?= htmlspecialchars($order["phone"]) ?>">
        <input type="hidden" name="address" value="<?= htmlspecialchars($order["address"]) ?>">
        <input type="hidden" name="city" value="<?= htmlspecialchars($order["city"]) ?>">
        <input type="hidden" name="country" value="Sri Lanka">
        <input type="hidden" name="order_id" value="<?= htmlspecialchars($orderIdSafe) ?>">
        <input type="hidden" name="items" value="<?= htmlspecialchars($orderDescription) ?>">
        <input type="hidden" name="currency" value="<?= htmlspecialchars($currency) ?>">
        <input type="hidden" name="amount" value="<?= htmlspecialchars($amount) ?>">  
        <input type="hidden" name="hash" value="<?= htmlspecialchars($hash) ?>">    
    </form>
    <script>
        document.getElementById('payhere-form').submit();
    </script>
</body>
</html>
