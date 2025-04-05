<?php 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

if (empty($_POST['user_id']) || empty($_POST['hobbie_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$user_id = (int)$_POST['user_id'];
$interest_id = (int)$_POST['interest_id'];

// Check if user exists
$user_check = mysqli_prepare($connect, "SELECT id FROM users WHERE id = ?");
mysqli_stmt_bind_param($user_check, "i", $user_id);
mysqli_stmt_execute($user_check);
mysqli_stmt_store_result($user_check);

if (mysqli_stmt_num_rows($user_check) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "User not found"]);
    exit;
}
mysqli_stmt_close($user_check);

// Check if interest exists
$interest_check = mysqli_prepare($connect, "SELECT id FROM interests WHERE id = ?");
mysqli_stmt_bind_param($interest_check, "i", $interest_id);
mysqli_stmt_execute($interest_check);
mysqli_stmt_store_result($interest_check);

if (mysqli_stmt_num_rows($interest_check) === 0) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Interest not found"]);
    exit;
}
mysqli_stmt_close($interest_check);

$check_is_exists = mysqli_prepare($connect, "SELECT * FROM user_interests WHERE user_id = ? AND interest_id = ?");
mysqli_stmt_bind_param($check_is_exists, "ii", $user_id, $interest_id);
mysqli_stmt_execute($check_is_exists);
mysqli_stmt_store_result($check_is_exists);
if (mysqli_stmt_num_rows($check_is_exists) > 0) {
    http_response_code(409);
    echo json_encode(["status" => "error", "message" => "Interest already exists"]);
    exit;
}
mysqli_stmt_close($check_is_exists);

// Insert into user_interests
$sql = "INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)";
$stmt = mysqli_prepare($connect, $sql);
if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $interest_id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(["status" => "success", "message" => "Interest updated successfully"]);
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to update interest"]);
    }

    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database query preparation failed"]);
}

mysqli_close($connect);
?>