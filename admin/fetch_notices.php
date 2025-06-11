<?php
// Include database connection
require_once '../config/db-config.php'; // Ensure this file includes the connection logic in the prompt

// Prepare the SQL query to fetch all notices
$query = "SELECT * FROM notice_table ORDER BY on_create DESC";
$result = $conn->query($query);

// Initialize an array to store notices
$notices = [];

if ($result && $result->num_rows > 0) {
    // Fetch each row as an associative array
    while ($row = $result->fetch_assoc()) {
        $notices[] = [
            'id' => $row['id'],
            'notice_group' => $row['notice_group'],
            'msg_subject' => $row['msg_subject'],
            'msg_content' => $row['msg_content'],
            'is_read' => (int)$row['is_read'],
            'on_create' => $row['on_create'],
            'on_update' => $row['on_update']
        ];
    }
}

// Return the notices as a JSON response
header('Content-Type: application/json');
echo json_encode($notices);

// Close the database connection
$conn->close();
?>