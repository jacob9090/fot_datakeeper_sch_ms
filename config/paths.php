<?php
// Detect protocol
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
// Get server name and subfolder path
$server_name = $_SERVER['HTTP_HOST'];
$subfolder = '/daddy/'; // Change this to match your subfolder

define('BASE_URL', $protocol . $server_name . $subfolder);
define('BASE_PATH', __DIR__ . '/../../'); // Adjust according to your folder structure
?>