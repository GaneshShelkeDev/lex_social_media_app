<?php
require_once dirname(__FILE__) . "/../config/database.php";
// ...existing code...

if (!isset($connect)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection not established"]);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
    exit;
}

if (empty($data['fullname']) || empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$check_email = "SELECT id FROM users WHERE email = ?";
$stmt = mysqli_prepare($connect, $check_email);
mysqli_stmt_bind_param($stmt, "s", $data['email']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Email already registered"]);
    exit;
}

$fullname = mysqli_real_escape_string($connect, $data["fullname"]);
$email = mysqli_real_escape_string($connect, $data["email"]);
$password = password_hash($data["password"], PASSWORD_DEFAULT);

$sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $password);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($connect);
    http_response_code(201);
    echo json_encode([
        "status" => "success", 
        "message" => "Registration pending OTP verification",
        "user_id" => $user_id
    ]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to insert data"]);
}
