<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}
if (empty($_POST['email']) || empty($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$email = mysqli_real_escape_string($connect, $_POST["email"]);
$password = $_POST["password"]; // Don't escape password before verification

// First query to get user details
$sql = "SELECT id, password FROM users WHERE email = ?";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_bind_result($stmt, $user_id, $hashed_password);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (password_verify($password, $hashed_password)) {
        // Generate new JWT token
        $jwt = generate_jwt_token($user_id);
        $expiry = time() + (7 * 86400); // 7 days expiry

        // Check if token exists
        $token_sql = "SELECT id FROM jwt_tokens WHERE user_id = ?";
        $token_stmt = mysqli_prepare($connect, $token_sql);
        mysqli_stmt_bind_param($token_stmt, "i", $user_id);
        mysqli_stmt_execute($token_stmt);
        mysqli_stmt_store_result($token_stmt);

        if (mysqli_stmt_num_rows($token_stmt) > 0) {
            // Update existing token
            $update_sql = "UPDATE jwt_tokens SET jwt = ?, expiry = ? WHERE user_id = ?";
            $update_stmt = mysqli_prepare($connect, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "sii", $jwt, $expiry, $user_id);
            mysqli_stmt_execute($update_stmt);
        } else {
            // Insert new token
            $insert_sql = "INSERT INTO jwt_tokens (user_id, jwt, expiry) VALUES (?, ?, ?)";
            $insert_stmt = mysqli_prepare($connect, $insert_sql);
            mysqli_stmt_bind_param($insert_stmt, "isi", $user_id, $jwt, $expiry);
            mysqli_stmt_execute($insert_stmt);
        }

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "user_id" => $user_id,
            "token" => $jwt
        ]);
    } else {
        http_response_code(401);
        echo json_encode(["status" => "error", "message" => "Invalid password"]);
    }
} else {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "User not found"]);
}

mysqli_close($connect);