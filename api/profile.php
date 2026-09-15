<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json; charset=UTF-8");

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Unauthorized. Please log in."]);
    exit;
}

$userId = (int)$_SESSION["user_id"];
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "GET") {
    $profile = getUserProfile($userId);
    if ($profile) {
        echo json_encode([
            "success" => true,
            "profile" => [
                "user_id"          => $profile["user_id"],
                "full_name"        => $profile["full_name"],
                "email"            => $profile["email"],
                "phone"            => $profile["phone"] ?? "",
                "shipping_address" => $profile["shipping_address"] ?? "",
                "billing_address"  => $profile["billing_address"] ?? "",
                "role"             => $profile["role"],
                "created_at"       => $profile["created_at"]
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "User not found."]);
    }
    exit;
}

if ($method === "POST" || $method === "PUT") {
    $rawInput = file_get_contents("php://input");
    $jsonData = json_decode($rawInput, true);
    $data = is_array($jsonData) ? $jsonData : $_POST;

    $fullName = trim($data["full_name"] ?? "");
    $phone    = trim($data["phone"] ?? "");
    $shipping = trim($data["shipping_address"] ?? "");
    $billing  = trim($data["billing_address"] ?? "");

    if (!$fullName) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Full name is required."]);
        exit;
    }

    $updateData = [
        "full_name"        => $fullName,
        "phone"            => $phone,
        "shipping_address" => $shipping,
        "billing_address"  => $billing
    ];

    if (updateUserProfile($userId, $updateData)) {
        $updatedProfile = getUserProfile($userId);
        echo json_encode([
            "success" => true,
            "message" => "Profile updated successfully.",
            "profile" => [
                "user_id"          => $updatedProfile["user_id"],
                "full_name"        => $updatedProfile["full_name"],
                "email"            => $updatedProfile["email"],
                "phone"            => $updatedProfile["phone"] ?? "",
                "shipping_address" => $updatedProfile["shipping_address"] ?? "",
                "billing_address"  => $updatedProfile["billing_address"] ?? "",
                "role"             => $updatedProfile["role"]
            ]
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Failed to update profile."]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["success" => false, "message" => "Method not allowed. Use GET or POST."]);
