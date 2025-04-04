<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

if (empty($_POST['fullname']) || empty($_POST['email']) || empty($_POST['password'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}


$sql = "SELECT 1 FROM users WHERE email = ? LIMIT 1";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "s", $data['email']);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    http_response_code(409);
    echo json_encode([
        "status" => "error",
        "message" => "Email already exists. Please try again."
    ]);
    exit();
}

$fullname = mysqli_real_escape_string($connect, $_POST["fullname"]);
$email = mysqli_real_escape_string($connect, $_POST["email"]);
$passw = mysqli_real_escape_string($connect, $_POST["password"]);
$passwordPattern = "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%#?&])[A-Za-z\d@$!%*#?&]{8,}$/";

    if (preg_match($passwordPattern, $passw)) {
        $password = password_hash($passw, PASSWORD_BCRYPT);

    } else {
        $data["status"] = "error";
        $data["message"] = "Password is invalid. It must contain at least 8 characters, one letter, one number, and one special character (@$!%#?&).";
        echo json_encode($data,JSON_PRETTY_PRINT);
        http_response_code(400);

        exit();
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $data["status"] = "error";
        $data["message"] = "Invalid email address. Please try again.";
        echo json_encode($data,JSON_PRETTY_PRINT);
        http_response_code(200);
        exit();
    }

    

$sql = "INSERT INTO users (fullname, email, password) VALUES (?, ?, ?)";
$stmt = mysqli_prepare($connect, $sql);
mysqli_stmt_bind_param($stmt, "sss", $fullname, $email, $password);

if (mysqli_stmt_execute($stmt)) {
    $user_id = mysqli_insert_id($connect);
    
    // Generate JWT token
    $jwt = generate_jwt_token($user_id);
    
    // Save the JWT token into the jwt_tokens table with expiry
    $expiry = time() + (7 * 86400); // 7 days expiry
    $token_sql = "INSERT INTO jwt_tokens (user_id, jwt, expiry) VALUES (?, ?, ?)";
    $token_stmt = mysqli_prepare($connect, $token_sql);
    mysqli_stmt_bind_param($token_stmt, "isi", $user_id, $jwt, $expiry);
    
    if (mysqli_stmt_execute($token_stmt)) {
        http_response_code(201);
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful",
            "user_id" => $user_id,
            "jwt" => $jwt
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to create authentication token"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Failed to insert data"]);
}
