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
if (empty($data['email']) || empty($data['password'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}
$email = mysqli_real_escape_string($connect, $data["email"]);
$password = mysqli_real_escape_string($connect, $data["password"]);
$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);        
if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_bind_result($stmt, $user_id, $hashed_password);
    mysqli_stmt_fetch($stmt);
    
    if (password_verify($password, $hashed_password)) {
        http_response_code(200);
        echo json_encode([
            "status" => "success", 
            "message" => "Login successful",
            "user_id" => $user_id
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "User not found"]);
}
    echo json_encode($response,JSON_PRETTY_PRINT);
    http_response_code(404);
    exit();
    