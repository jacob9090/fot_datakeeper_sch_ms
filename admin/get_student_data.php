<?php
session_start();

// included $conn variable
include "../config/db-config.php";

// Redirect to login page if the user is not logged in
if (!isset($_SESSION['first_name'])) {
    header("Location: ../index.php");
    exit();
}

$sql = "SELECT current_class, gender FROM student_table";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  $data = array();
  while($row = $result->fetch_assoc()) {
    $data[] = $row;
  }
} else {
  $data = array(); 
}

$conn->close();

header('Content-Type: application/json');
echo json_encode($data);
?>