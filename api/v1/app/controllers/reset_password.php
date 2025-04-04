<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}
if (empty($_POST['email']) || empty($_POST['password']) || empty($_POST['confirm_password'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}
$email = mysqli_real_escape_string($connect, $_POST["email"]);
$password = mysqli_real_escape_string($connect, $_POST["password"]);
$confirm_password = mysqli_real_escape_string($connect, $_POST["confirm_password"]);

if ($password !== $confirm_password) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Passwords do not match"]);
    exit;
}
$passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%#?&])[A-Za-z\d@$!%*#?&]{8,}$/";
if (preg_match($passwordPattern, $password)) {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Password is invalid. It must contain at least 8 characters, one letter, one number, and one special character (@$!%#?&)."]);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid email address. Please try again."]);
    exit;
}
$sql = "SELECT 1 FROM users WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Email not found"]);
    exit;
}
$sql = "UPDATE users SET password = ? WHERE email = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "ss", $hashed_password, $email);
if (mysqli_stmt_execute($stmt)) {
    http_response_code(200);
    echo json_encode(["status" => "success", "message" => "Password reset successfully"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to reset password"]);
}
mysqli_stmt_close($stmt);
mysqli_close($connect);
