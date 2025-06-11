<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$rawdata = file_get_contents("php://input");

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rawdata = json_encode([
        "RealTime" => [
            "PunchLog" => [
                "UserId" => "TEST_USER",
                "LogTime" => date('Y-m-d H:i:s'),
                "Type" => "TestCheck"
            ]
        ]
    ]);
    $_GET['stgid'] = $_GET['stgid'] ?? 'TEST_STGID';
}

// Verify required stgid parameter
// if (!isset($_GET["stgid"])) {
//     http_response_code(400);
//     die("Missing stgid parameter");
// }
if (!isset($_GET["stgid"])) {
    error_log("Warning: Missing stgid parameter");
    $stgid = "UNKNOWN_DEVICE";
}
$stgid = $_GET["stgid"];

// Validate and decode JSON input
if (empty($rawdata)) {
    http_response_code(400);
    die("Empty request body");
}

$data = json_decode($rawdata, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die("Invalid JSON: " . json_last_error_msg());
}

// Process RealTime data
$response = new StdClass();
if (isset($data["RealTime"])) {
    $ret = handle_attendance_log($stgid, $rawdata);
    $response->status = $ret;
} else {
    http_response_code(400);
    die("Missing RealTime data");
}

// Send proper JSON response
header("Content-Type: application/json; charset=utf-8");
echo json_encode($response);
exit;

function handle_attendance_log($stgid, $rawdata) {
    // Decode JSON data
    $request = json_decode($rawdata);
    if (!$request || !isset($request->RealTime->PunchLog)) {
        error_log("Invalid RealTime data structure");
        return "error";
    }

    // Create log content
    $punch = $request->RealTime->PunchLog;
    $content = sprintf(
        "ServiceTagId:%s,\tUserId:%s,\tAttendanceTime:%s,\tAttendanceType:%s,\tInputType:%s,\tOperation:RealTime->PunchLog,\tAuthToken:%s\n",
        $stgid,
        $punch->UserId ?? 'null',
        $punch->LogTime ?? 'null',
        $punch->Type ?? 'null',
        $punch->InputType ?? 'null',
        $request->RealTime->AuthToken ?? 'null'
    );

    // Write to log file with error handling
    $filename = "cams-attendance-record.txt";
    $file = fopen($filename, "a");
    if (!$file) {
        error_log("Failed to open log file: " . $filename);
        return "file_error";
    }
    fwrite($file, $content);
    
    // Database connection parameters (UPDATE THESE WITH YOUR CREDENTIALS)
    $servername = "localhost";
    $username = "u213888571_sch_un";
    $password = "jH3=Z1QEGw";
    $dbname = "u213888571_sch_db";

    try {
        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind parameters to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO CamsBiometricAttendance 
            (ServiceTagId, UserId, AttendanceTime, AttendanceType) 
            VALUES (?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Convert log time to valid MySQL datetime format
        $logTime = date("Y-m-d H:i:s", strtotime($punch->LogTime));
        
        $stmt->bind_param("ssss", 
            $stgid,
            $punch->UserId,
            $logTime,
            $punch->Type
        );

        // Execute and check result
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        
        fwrite($file, " -- inserted in db");
    } catch (Exception $e) {
        fwrite($file, " -- DB Error: " . $e->getMessage());
        error_log("Database error: " . $e->getMessage());
    }
    
    fclose($file);
    return "done";
}

?>