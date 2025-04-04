<?php

/**
 * ========================================================================================
 * Lex Social Media App APIs Version 1.0.0
 * Developed by Ganesh Shelke -  Allied Technologies, 2025.
 * ========================================================================================
 * REST API main endpoint
 * Every API node is connected by the POST request variable @request
 * Based on the POST request parameter, we require the controller code from the controller directory
 * Controller directory is located at ../app/controller/
 * =========================================================================================
 */

 ini_set("display_errors", 1);
 session_start();
 
 // Set common headers
 header('Content-Type: application/json; charset=utf-8');
 header('Access-Control-Allow-Origin: *');
 header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
 header('Access-Control-Allow-Headers: Content-Type, Authorization');
 
 // Handle JSON input
 if (
    isset($_SERVER['CONTENT_TYPE']) &&
    strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false
) {
    $json = file_get_contents('php://input');
    $_POST = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        http_response_code(400);
        echo json_encode(["status" => "error", "message" => "Invalid JSON format"]);
        exit;
    }
}
// Else, $_POST and $_FILES are already set correctly for form-data

 
 // Include required files
 require "app/config/database.php";
 require "app/config/config.php";
 require "app/controllers/functions.php";
 
 // Check database connection
 if (!isset($connect)) {
     http_response_code(500);
     echo json_encode(["status" => "error", "message" => "Database connection not established"]);
     exit;
 }
 
 // Check for required request parameter
 if ((!isset($_POST["request"]) || empty($_POST["request"])) && (!isset($_REQUEST["request"]) || empty($_REQUEST["request"]))) {
     http_response_code(400);
     echo json_encode(["status" => "error", "message" => "Required parameter 'request' is missing"]);
     exit;
 }
 $_POST["request"] = $_POST["request"] ?? $_REQUEST["request"];
 

$response["response"] = array();
$data = array();
$details = array();


/** Api key check */
// if(!isset($_POST["api_key"]) || empty($_POST["api_key"])){
//     $data["status"] = "failed";
//     $data["message"] = "invalid api key";
//     array_push($response["response"], $data);
//     echo json_encode($response,JSON_PRETTY_PRINT);
//     http_response_code(401);
//     exit();
// }else{
//     if(!check_api_key($connect,$_POST["api_key"])){
//         $data["status"] = "failed";
//         $data["message"] = "invalid api key";
//         array_push($response["response"], $data);
//         echo json_encode($response,JSON_PRETTY_PRINT);
//         http_response_code(401);
//         exit();
//     }
// }

/** Check for required global variable @request */
if(!isset($_POST["request"]) || empty($_POST["request"])){
    $data["status"] = "failed";
    $data["message"] = "required params incomplete";
    array_push($response["response"], $data);
    echo json_encode($response,JSON_PRETTY_PRINT);
    http_response_code(401);
    exit();
}



/**
 * 
 * ===========================  API ENDPOINTS START HERE ========================================
 * 
 */

try{
    $request = $_POST["request"];
    $filePath = "app/controllers/" . $request . ".php";

    if (file_exists($filePath)) {
        require $filePath;
    } else {
        $data["status"] = "failed";
        $data["message"] = "Request not found";
        array_push($response["response"], $data);
        echo json_encode($response,JSON_PRETTY_PRINT);
        http_response_code(404);
        exit();
    }
}
catch (Exception $e) {
    $data["status"] = "failed";
    $data["message"] = "Database error: ". $e->getMessage();
    echo json_encode($data, JSON_PRETTY_PRINT);
    http_response_code(500);
    exit();
}

