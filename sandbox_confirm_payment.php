<?php
// sandbox_confirm_payment.php

session_start();

require_once __DIR__ . "/includes/db.php";

/*
 * =====================================================
 * SWEET MART - LOCAL SANDBOX PAYMENT CONFIRMATION
 * =====================================================
 *
 * DEVELOPMENT / DEMO USE ONLY.
 *
 * This does NOT verify a real PayHere payment.
 * It is only used to demonstrate the successful
 * payment workflow while the project runs on localhost.
 *
 * REMOVE THIS FILE BEFORE PRODUCTION DEPLOYMENT.
 */


// =====================================================
// 1. CHECK LOGIN
// =====================================================

$userId = $_SESSION["user_id"] ?? null;

if (!$userId) {
    header("Location: login.php");
    exit;
}


// =====================================================
// 2. ONLY ALLOW POST REQUEST
// =====================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: shop.php");
    exit;
}


// =====================================================
// 3. GET ORDER ID
// =====================================================

$orderId = filter_var(
    $_POST["order_id"] ?? 0,
    FILTER_VALIDATE_INT
);

if (!$orderId || $orderId <= 0) {
    die("Invalid order.");
}


// =====================================================
// 4. START DATABASE TRANSACTION
// =====================================================

$conn->begin_transaction();

try {

    // =================================================
    // 5. VERIFY ORDER OWNERSHIP
    // =================================================

    $stmt = $conn->prepare(
        "SELECT id, status
         FROM orders
         WHERE id = ?
           AND user_id = ?
         LIMIT 1"
    );

    $stmt->bind_param(
        "ii",
        $orderId,
        $userId
    );

    $stmt->execute();

    $order = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$order) {
        throw new Exception("Order not found.");
    }


    // =================================================
    // 6. CHECK PAYMENT RECORD EXISTS
    // =================================================

    $stmt = $conn->prepare(
        "SELECT id, status
         FROM payments
         WHERE order_id = ?
         ORDER BY id DESC
         LIMIT 1"
    );

    $stmt->bind_param(
        "i",
        $orderId
    );

    $stmt->execute();

    $payment = $stmt
        ->get_result()
        ->fetch_assoc();

    $stmt->close();


    if (!$payment) {
        throw new Exception("Payment record not found.");
    }


    // =================================================
    // 7. UPDATE PAYMENT STATUS
    // =================================================

    $paymentId = $payment["id"];

    $stmt = $conn->prepare(
        "UPDATE payments
         SET status = 'Paid'
         WHERE id = ?"
    );

    $stmt->bind_param(
        "i",
        $paymentId
    );

    $stmt->execute();
    $stmt->close();


    // =================================================
    // 8. UPDATE ORDER STATUS
    // =================================================

    $stmt = $conn->prepare(
        "UPDATE orders
         SET status = 'Confirmed'
         WHERE id = ?
           AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $orderId,
        $userId
    );

    $stmt->execute();
    $stmt->close();


    // =================================================
    // 9. SAVE CHANGES
    // =================================================

    $conn->commit();


    // Remember latest order
    $_SESSION["last_order_id"] = $orderId;


    // =================================================
    // 10. REDIRECT BACK TO RETURN PAGE
    // =================================================

    header(
        "Location: payhere_return.php?order_id=" .
        urlencode((string)$orderId)
    );

    exit;


} catch (Throwable $e) {

    $conn->rollback();

    // Development message
    die(
        "Unable to confirm sandbox test payment: " .
        htmlspecialchars($e->getMessage())
    );
}