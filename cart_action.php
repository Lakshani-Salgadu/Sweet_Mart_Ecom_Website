<?php
require_once __DIR__ . "/includes/functions.php";
header("Content-Type: application/json");
$action = $_POST["action"] ?? "";
if ($action === "add") {
    $pid = (int)($_POST["product_id"] ?? 0);
    $qty = (int)($_POST["qty"] ?? 1);
    if ($pid > 0) addToCart($pid, $qty);
    echo json_encode(["success" => true, "count" => getCartCount()]);
} elseif ($action === "remove") {
    $pid = (int)($_POST["product_id"] ?? 0);
    removeFromCart($pid);
    echo json_encode(["success" => true, "count" => getCartCount()]);
} elseif ($action === "update") {
    $pid = (int)($_POST["product_id"] ?? 0);
    $qty = (int)($_POST["qty"] ?? 1);
    updateCartQty($pid, $qty);
    echo json_encode(["success" => true, "count" => getCartCount()]);
} else {
    echo json_encode(["success" => false]);
}
