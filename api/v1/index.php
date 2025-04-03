<?php

/**
 * ========================================================================================
 * Bible App APIs Version 1.0.0
 * Developed by Ganesh Shelke -  Allied Technologies, 2025.
 * ========================================================================================
 * REST API main endpoint
 * Every API node is connected by the POST request variable @request
 * Based on the POST request parameter, we require the controller code from the controller directory
 * Controller directory is located at ../app/controller/
 * =========================================================================================
 */

ini_set("display_errors",1);

 /**adding prerequisite files */
session_start();
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? $_SERVER["CONTENT_TYPE"] : '';
if (strpos($contentType, 'application/json') !== false) {
    $_POST = json_decode(file_get_contents('php://input'), true);
}
require "app/config/database.php";
require "app/config/config.php";
// require "../app/controllers/functions.php";

header('Content-Type: application/json; charset=utf-8');

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

