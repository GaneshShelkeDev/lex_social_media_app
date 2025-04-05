<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}
if (empty($_POST['id']) || empty($_POST['username']) || empty($_POST['dob']) || empty($_POST['bio'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit;
}
$id = mysqli_real_escape_string($connect, $_POST["id"]);
$username = mysqli_real_escape_string($connect, $_POST["username"]);
$date = DateTime::createFromFormat('d-m-Y', $_POST["dob"]);
$dob = $date ? $date->format('Y-m-d') : null;
if (!$dob) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid date format"]);
    exit;
}
$bio = mysqli_real_escape_string($connect, $_POST["bio"]);
$profile = $_FILES['profile'] ?? null;

// Handle file upload
if ($profile) {
    $target_dir = "app/uploads/profile_images/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $filename = uniqid() . '_' . basename($profile["name"]);
    $target_file = $target_dir . $filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Allow certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Sorry, only JPG, JPEG & PNG files are allowed"]);
        exit;
    }
    
    if (move_uploaded_file($profile["tmp_name"], $target_file)) {
        $profile_path = $filename;  // Store only filename in database
    } else {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to upload file"]);
        exit;
    }
} else {
    $profile_path = null;
}

// Update user data
$sql = "UPDATE users SET username = ?, dob = ?, bio = ?, profile = ? WHERE id = ?";
$stmt = mysqli_prepare($connect, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssssi", $username, $dob, $bio, $profile_path, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode([
            "status" => "success",
            "message" => "Profile updated successfully"
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            "status" => "error",
            "message" => "Failed to update profile: " . mysqli_error($connect)
        ]);
    }
    
    mysqli_stmt_close($stmt);
} else {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Failed to prepare statement: " . mysqli_error($connect)
    ]);
}

mysqli_close($connect);

