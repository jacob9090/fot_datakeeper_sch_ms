<?php
session_start();
include "../config/db-config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class_name = $_POST['class_name'];
    $academic_year = $_POST['academic_year'];
    $semester = $_POST['semester'];
    $attendance_taken_by = $_POST['attendance_taken_by'];
    $attendance_data = $_POST['attendance'];
    $on_create_date = date("Y-m-d H:i:s");

    $stmt = $conn->prepare("INSERT INTO attendance_table (user_id, class_name, academic_year, semester, mark, attendance_taken_by, on_create_date) VALUES (?, ?, ?, ?, ?, ?, ?)");

    foreach ($attendance_data as $user_id => $mark) {
        $stmt->bind_param("issssis", $user_id, $class_name, $academic_year, $semester, $mark, $attendance_taken_by, $on_create_date);
        $stmt->execute();
    }

    $stmt->close();
    $conn->close();

    $_SESSION['success_alert'] = "Attendance recorded successfully.";
    header("Location: add-attendance.php?class=" . urlencode($class_name));
    exit();
}
?>

