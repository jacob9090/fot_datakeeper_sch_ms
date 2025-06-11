<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get raw POST data
$raw_data = file_get_contents("php://input");

// Validate input
if (empty($raw_data)) {
    http_response_code(400);
    die("Empty request body");
}

// Decode JSON data
$request_data = json_decode($raw_data);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die("Invalid JSON: " . json_last_error_msg());
}

// Validate required structure
if (!isset($request_data->ApiRequestInfo->Operation) || !isset($request_data->ServiceTagId)) {
    http_response_code(400);
    die("Invalid request structure");
}

// Process operation
if ($request_data->ApiRequestInfo->Operation === 'RealTimePunchLog') {
    handle_attendance_log($request_data);
}

// Send response
header("Content-Type: application/json; charset=utf-8");
echo json_encode(["status" => "done"]);
exit;

function handle_attendance_log($request) {
    // Validate required fields
    $required_fields = [
        'ServiceTagId',
        'ApiRequestInfo->UserId',
        'ApiRequestInfo->OperationTime',
        'ApiRequestInfo->OperationData->AttendanceType'
    ];
    
    foreach ($required_fields as $field) {
        if (!isset($request->{$field})) {
            error_log("Missing field: $field");
            return;
        }
    }

    // Create log content
    $content = sprintf(
        "ServiceTagId:%s,\tOperation:%s,\tUserId:%s,\tTime:%s,\tType:%s,\tInput:%s\n",
        $request->ServiceTagId,
        $request->ApiRequestInfo->Operation,
        $request->ApiRequestInfo->UserId,
        date("Y-m-d H:i:s", strtotime($request->ApiRequestInfo->OperationTime)),
        $request->ApiRequestInfo->OperationData->AttendanceType,
        $request->ApiRequestInfo->OperationData->InputType ?? 'Unknown'
    );

    // Write to log file
    $filename = "attendance-record.txt";
    if (!file_put_contents($filename, $content, FILE_APPEND | LOCK_EX)) {
        error_log("Failed to write to log file");
    }

    // Database connection
    $servername = "localhost";
    $username = "u213888571_sch_un";
    $password = "jH3=Z1QEGw";
    $dbname = "u213888571_sch_db";

    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Use prepared statements to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO CamsBiometricAttendance 
            (ServiceTagId, UserId, AttendanceTime, AttendanceType)
            VALUES (?, ?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        // Convert time to MySQL datetime format
        $attendance_time = date("Y-m-d H:i:s", strtotime($request->ApiRequestInfo->OperationTime));
        
        $stmt->bind_param("ssss",
            $request->ServiceTagId,
            $request->ApiRequestInfo->UserId,
            $attendance_time,
            $request->ApiRequestInfo->OperationData->AttendanceType
        );

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
        $conn->close();
        
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
    }
}
?>