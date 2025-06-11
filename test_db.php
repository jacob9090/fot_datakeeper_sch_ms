<?php
$servername = "localhost"; // Replace with your actual values
$username = "u213888571_sch_un";
$password = "jH3=Z1QEGw";
$dbname = "u213888571_sch_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Successfully connected to the database";
$conn->close();
?>