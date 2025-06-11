<?php
// Enable error reporting for debugging during development
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Database connection constants
define('DB_HOST', 'localhost');
define('DB_USER', 'u213888571_sch_un');
define('DB_PASS', 'jH3=Z1QEGw');
define('DB_NAME', 'u213888571_sch_db');

// Establish a database connection using mysqli
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check for connection errors
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Set the character set to UTF-8
$conn->set_charset('utf8mb4');
?>
