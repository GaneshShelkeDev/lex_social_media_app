<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}
if (empty($_POST['email']) || empty($_POST['otp'])) {
  
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}
$email = mysqli_real_escape_string($connect, $_POST["email"]);
$otp = mysqli_real_escape_string($connect, $_POST["otp"]);
$query = "SELECT * FROM users WHERE email = '$email' AND otp = '$otp'";
$result = mysqli_query($connect, $query);

if (mysqli_num_rows($result) > 0) {
    // OTP is valid, proceed to update the user's status 
    echo json_encode(["status" => "success", "message" => "OTP verified successfully"]);
} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid OTP or OTP expired"]);
}