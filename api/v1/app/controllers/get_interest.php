<?php 
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method not allowed"]);
    exit;
}
$sql = "SELECT * FROM interests";
$stmt = mysqli_prepare($connect, $sql);
if ($stmt) {
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_num_rows($result) > 0) {
        $interests = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $interests[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $interests]);
    } else {
        echo json_encode(["status" => "error", "message" => "No interests found"]);
    }
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database query failed"]);
}
