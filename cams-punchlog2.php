<?php
// Enable error reporting for now
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

file_put_contents("debug.txt", "Script started.\n", FILE_APPEND);

$rawdata = file_get_contents("php://input");
file_put_contents("debug.txt", "Raw data received: " . $rawdata . "\n", FILE_APPEND);

	$rawdata = file_get_contents("php://input");

	// We capture all the incoming data into file: cams-attendance-record.txt.
	// If you need to maintain it in your own database, you need to implement the same here.
	$raw = json_decode($rawdata);

	$ret = "done";
	$data = json_decode($rawdata, true);

	if( isset( $data["RealTime"] ))
	{
		$ret = handle_attendance_log($rawdata);
	}
	else
		$ret = "Else";

	$response = new StdClass();
	$response->status="done";

	header("Content-Type: application/text;charset=utf-8");
	http_response_code(200);
	echo json_encode($response);


// You can test url of this file through postman with POST->body->raw (JSON) parameter
// the results will be available in http://<domain:port>/cams-attendance-record.txt
// ------------------------------------------------------------------------------------------------------------


function handle_attendance_log($rawdata)
{

	$ret = "done";
		// Sample rawdata
		// {
		//      "RealTime": {
		//           "OperationID": "123123123",
		//           "PunchLog": {
		//                "Type": "CheckOut",
		//                "Temperature": "36.8",
		//                "FaceMask": false,
		//                "InputType": "Fingerprint",
		//                "UserId": "2",
		//                "LogTime": "2020-09-17 07:48:22 GMT +0530"
		//           },
		//           "AuthToken": "COJJ7eiiPBGUfmIQPvh2PJWWDLX7OuKs",
		//           "Time": "2020-09-17 04:19:03 GMT +0000"
		//      }
		// }

	$request = new StdClass();
	$request->RealTime = new StdClass();
	$request->RealTime->AuthToken="";
	$request->RealTime->Time="";
	$request->RealTime->OperationID="";
	$request->RealTime->PunchLog = new StdClass();
	$request->RealTime->PunchLog->UserId="";
	$request->RealTime->PunchLog->LogTime="";
	$request->RealTime->PunchLog->Temperature="";
	$request->RealTime->PunchLog->FaceMask="";
	$request->RealTime->PunchLog->InputType="";
	$request->RealTime->PunchLog->Type= "";

	$request = json_decode($rawdata);

	$content = 'UserId:' . $request->RealTime->PunchLog->UserId . ",\t";
	$content = $content . 'AttendanceTime:' . $request->RealTime->PunchLog->LogTime . ",\t";
	$content = $content . 'AttendanceType:' . $request->RealTime->PunchLog->Type . ",\t";
	$content = $content . 'InputType:' . $request->RealTime->PunchLog->InputType . ",\t";
	$content = $content . 'Operation: RealTime->PunchLog' . ",\t";
	$content = $content . 'AuthToken:' . $request->RealTime->AuthToken . "\n";

	$file = fopen("cams-attendance-record.txt","a");
	fwrite($file, $content);

	// Uncomment this block to write the values in your DB. Make sure that following table is created and $servername, $username , $password and $dbname are filled with its actual values
	//DB Script for table name CamsBiometricAttendance
	// Create Table CamsBiometricAttendance ( ID int NOT NULL AUTO_INCREMENT,    UserId varchar(9), AttendanceTime datetime, AttendanceType varchar(16), PRIMARY KEY (ID));
	// After success execution of the script run the following query at your sql server
	//select UserId, AttendanceTime, AttendanceType  from CamsBiometricAttendance order by id desc


	$UserId = $request->RealTime->PunchLog->UserId;
	$AttendanceTime = $request->RealTime->PunchLog->LogTime;
	$AttendanceType = $request->RealTime->PunchLog->Type;

	$servername = "localhost";
	$username = "u213888571_sch_un";
	$password = "jH3=Z1QEGw";
	$dbname = "u213888571_sch_db";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);

	// Check connection
	if ($conn->connect_error) {
		fwrite($file,"Connection failed: " . $conn->connect_error);
		die("Connection failed: " . $conn->connect_error);
	}

	$sql = "INSERT INTO CamsBiometricAttendance (UserId, AttendanceTime, AttendanceType)
				VALUES ('". $UserId ."', '" . $AttendanceTime . "', '" . $AttendanceType . "')";

	fwrite($file,$sql);
	if ($conn->query($sql) === TRUE) {
		fwrite($file, " -- inserted in db");
	} else {
		fwrite($file, " -- DB Error: " . $sql . "<br>" . $conn->error);
	}

	$conn->close();


	fclose($file);

	return $ret;

}
?>