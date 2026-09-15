<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
    exit;
}

$rawInput = file_get_contents("php://input");
$jsonData = json_decode($rawInput, true);
$data = is_array($jsonData) ? $jsonData : $_POST;

$email    = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email and password are required."]);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$storedHash = $user["password_hash"] ?? $user["password"] ?? "";
if ($user && $storedHash && password_verify($password, $storedHash)) {
    // Regenerate session ID for security
    session_regenerate_id(true);

    $_SESSION["user_id"]   = (int)$user["id"];
    $_SESSION["full_name"] = $user["full_name"];
    $_SESSION["email"]     = $user["email"];
    $_SESSION["role"]      = $user["role"] ?? "customer";

    echo json_encode([
        "success" => true,
        "message" => "Login successful",
        "user" => [
            "user_id"   => (int)$user["id"],
            "full_name" => $user["full_name"],
            "email"     => $user["email"],
            "role"      => $user["role"] ?? "customer"
        ]
    ]);
} else {
    // Generic error to prevent enumeration
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid email or password."]);
}
