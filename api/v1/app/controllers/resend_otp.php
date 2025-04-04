<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

if (empty($_POST['email'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

try {
    $email = filter_var($_POST["email"], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    $otp = sprintf("%04d", random_int(0, 9999)); // Generate 4-digit OTP

    $token_sql = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($connect, $token_sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) > 0) {
        // User exists, proceed to send OTP
        $update_sql = "UPDATE users SET otp = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($connect, $update_sql);
        mysqli_stmt_bind_param($update_stmt, "ss", $otp, $email);
        
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("Failed to update OTP");
        }
        // Send OTP via email (assuming send_email is a function that sends an email)

        http_response_code(200);
        echo json_encode([
            "status" => "success",
            "message" => "OTP sent to your email address"
        ]);
    } else {
        http_response_code(404);
        echo json_encode([
            "status" => "error",
            "message" => "Email not found or account inactive"
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
}