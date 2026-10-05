<?php
// payhere_notify.php
require_once __DIR__ . "/includes/db.php";
require_once __DIR__ . "/includes/payhere_config.php";

$merchant_id         = $_POST['merchant_id'] ?? '';
$order_id            = $_POST['order_id'] ?? '';
$payhere_amount      = $_POST['payhere_amount'] ?? '';
$payhere_currency    = $_POST['payhere_currency'] ?? '';
$status_code         = $_POST['status_code'] ?? '';
$md5sig              = $_POST['md5sig'] ?? '';

// Prevent multiple notifications from doing duplicate work
// The database updates should be idempotent

$merchant_secret = PAYHERE_MERCHANT_SECRET;
$local_md5sig = strtoupper(
    md5(
        $merchant_id . 
        $order_id . 
        $payhere_amount . 
        $payhere_currency . 
        $status_code . 
        strtoupper(md5($merchant_secret)) 
    ) 
);

if (($local_md5sig === $md5sig) && ($status_code == 2) && ($merchant_id === PAYHERE_MERCHANT_ID)) {
    // Check if the order amount matches the notification
    $stmt = $conn->prepare("SELECT total FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if ($order && number_format($order['total'], 2, '.', '') === $payhere_amount && $payhere_currency === PAYHERE_CURRENCY) {
        
        // Update payments status to Paid if it's currently Pending
        $ps = $conn->prepare("UPDATE payments SET status = 'Paid', paid_at = CURRENT_TIMESTAMP WHERE order_id = ? AND method = 'card' AND status = 'Pending'");
        $ps->bind_param("i", $order_id);
        $ps->execute();
        
        if ($ps->affected_rows > 0) {
            // Update order status to Confirmed
            $os = $conn->prepare("UPDATE orders SET status = 'Confirmed' WHERE id = ?");
            $os->bind_param("i", $order_id);
            $os->execute();
        }
    }
}
