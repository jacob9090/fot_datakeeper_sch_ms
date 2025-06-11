<?php
require_once '../config/db-config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'], $_POST['subject'], $_POST['message'])) {
    $id = intval($_POST['id']);
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    $query = "UPDATE notice_table SET msg_subject = ?, msg_content = ?, on_update = NOW() WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('ssi', $subject, $message, $id);
    
    if ($stmt->execute()) {
        echo 'Success';
    } else {
        echo 'Error updating notice';
    }
    $stmt->close();
}
$conn->close();
?>
