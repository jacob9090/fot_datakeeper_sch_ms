<?php
require_once '../config/db-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $query = "DELETE FROM timetable_table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        echo 'Success';
    } else {
        echo 'Error deleting time table';
    }
    $stmt->close();
}
$conn->close();
?>
