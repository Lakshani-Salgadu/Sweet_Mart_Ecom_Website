<?php
require_once __DIR__ . "/../includes/db.php";
require_once __DIR__ . "/../includes/functions.php";

header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed. Use POST."]);
    exit;
}

// Support both application/json body and standard form POST
$rawInput = file_get_contents("php://input");
$jsonData = json_decode($rawInput, true);
$data = is_array($jsonData) ? $jsonData : $_POST;

$fullName = trim($data["full_name"] ?? "");
$email    = trim($data["email"] ?? "");
$phone    = trim($data["phone"] ?? "");
$password = $data["password"] ?? "";
$confirm  = $data["confirm_password"] ?? "";

$errors = [];

if (!$fullName) {
    $errors[] = "Full name is required.";
}
if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "A valid email address is required.";
}
if (strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
} elseif (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
    $errors[] = "Password must contain at least one letter and one number.";
}
if ($password !== $confirm) {
    $errors[] = "Passwords do not match.";
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(["success" => false, "errors" => $errors]);
    exit;
}

// Check duplicate email
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    http_response_code(409);
    echo json_encode(["success" => false, "message" => "An account with this email address already exists."]);
    exit;
}

// Bcrypt password hashing
$hash = password_hash($password, PASSWORD_BCRYPT);
$ins = $conn->prepare("INSERT INTO users (full_name, email, phone, password, password_hash, role) VALUES (?, ?, ?, ?, ?, 'customer')");
$ins->bind_param("sssss", $fullName, $email, $phone, $hash, $hash);
if ($ins->execute()) {
    $userId = $conn->insert_id;

    // Sync profile
    $insProf = $conn->prepare("INSERT INTO user_profiles (user_id, phone) VALUES (?, ?) ON DUPLICATE KEY UPDATE phone = VALUES(phone)");
    $insProf->bind_param("is", $userId, $phone);
    $insProf->execute();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "message" => "Registration successful. Please log in.",
        "user_id" => $userId
    ]);
} else {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error during registration."]);
}
