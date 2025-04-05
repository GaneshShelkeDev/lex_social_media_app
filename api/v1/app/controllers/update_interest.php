<?php 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}

if (empty($_POST['user_id']) || empty($_POST['interest_id'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}

$user_id = (int)$_POST['user_id'];
$interest_ids = $_POST['interest_id']; // Accepts array or comma-separated string

// Normalize to array
if (!is_array($interest_ids)) {
    $interest_ids = explode(',', $interest_ids);
}

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

$inserted = [];
$skipped = [];

foreach ($interest_ids as $interest_id) {
    $interest_id = (int)trim($interest_id);

    // Check if interest exists
    $interest_check = mysqli_prepare($connect, "SELECT id FROM interests WHERE id = ?");
    mysqli_stmt_bind_param($interest_check, "i", $interest_id);
    mysqli_stmt_execute($interest_check);
    mysqli_stmt_store_result($interest_check);

    if (mysqli_stmt_num_rows($interest_check) === 0) {
        $skipped[] = $interest_id . ' (not found)';
        mysqli_stmt_close($interest_check);
        continue;
    }
    mysqli_stmt_close($interest_check);

    // Check if already exists
    $check_exists = mysqli_prepare($connect, "SELECT id FROM user_interests WHERE user_id = ? AND interest_id = ?");
    mysqli_stmt_bind_param($check_exists, "ii", $user_id, $interest_id);
    mysqli_stmt_execute($check_exists);
    mysqli_stmt_store_result($check_exists);

    if (mysqli_stmt_num_rows($check_exists) > 0) {
        $skipped[] = $interest_id . ' (already added)';
        mysqli_stmt_close($check_exists);
        continue;
    }
    mysqli_stmt_close($check_exists);

    // Insert
    $insert_stmt = mysqli_prepare($connect, "INSERT INTO user_interests (user_id, interest_id) VALUES (?, ?)");
    mysqli_stmt_bind_param($insert_stmt, "ii", $user_id, $interest_id);

    if (mysqli_stmt_execute($insert_stmt)) {
        $inserted[] = $interest_id;
    } else {
        $skipped[] = $interest_id . ' (insert error)';
    }
    mysqli_stmt_close($insert_stmt);
}

mysqli_close($connect);

// Final response
echo json_encode([
    "status" => "success",
    "inserted" => $inserted,
    "skipped" => $skipped,
    "message" => "Interest processing completed"
]);
?>