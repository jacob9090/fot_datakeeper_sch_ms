<?php
// secure session parameters
require_once '../config/paths.php';
require_once '../config/db-config.php'; // database connection

// Verify subfolder is defined
if (!isset($subfolder) || empty($subfolder)) {
    die('Subfolder path not configured');
}

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => $subfolder,
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();

// Authorization check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['danger_alert'] = "Unauthorized access!";
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Current Date
$today = date("Y-m-d");

// Get selected class
$selected_class = $_GET['class'] ?? null;

// Get class list for dropdown
$classResult = $conn->query("SELECT DISTINCT current_class FROM student_table ORDER BY current_class ASC");
$classes = [];
while ($row = $classResult->fetch_assoc()) {
    $classes[] = $row['current_class'];
}

// Fetch students only if class selected
$students = [];
if ($selected_class) {
    $stmt = $conn->prepare("SELECT user_id, first_name, other_names, current_class FROM student_table WHERE current_class = ?");
    $stmt->bind_param("s", $selected_class);
    $stmt->execute();
    $students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Check if attendance was already taken today for selected class
$attendance_taken = false;
if ($selected_class) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM attendance_table WHERE class_name = ? AND DATE(on_create_date) = ?");
    $stmt->bind_param("ss", $selected_class, $today);
    $stmt->execute();
    $stmt->bind_result($attendance_count);
    $stmt->fetch();
    $attendance_taken = $attendance_count > 0;
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">

    <h2 class="text-center mb-4">Student Attendance Register</h2>

    <!-- Class filter -->
    <form method="GET" class="mb-4">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <label for="class" class="form-label">Select Class:</label>
                <select name="class" id="class" class="form-select" required onchange="this.form.submit()">
                    <option value="">-- Choose Class --</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?= htmlspecialchars($class) ?>" <?= $class == $selected_class ? 'selected' : '' ?>>
                            <?= htmlspecialchars($class) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if ($selected_class): ?>

        <?php if ($attendance_taken): ?>
            <div class="alert alert-warning">
                Attendance has already been taken for <strong><?= htmlspecialchars($selected_class) ?></strong> today.
                Please come back tomorrow.
            </div>
        <?php elseif (empty($students)): ?>
            <div class="alert alert-info">No students found in this class.</div>
        <?php else: ?>

            <form action="process-attendance.php" method="POST">
                <input type="hidden" name="class_name" value="<?= htmlspecialchars($selected_class) ?>">
                <input type="hidden" name="academic_year" value="<?= date("Y") ?>">
                <input type="hidden" name="semester" value="<?= date('n') <= 6 ? 'Semester 1' : 'Semester 2' ?>">
                <input type="hidden" name="attendance_taken_by" value="<?= $_SESSION['user_id'] ?>">

                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Mark Present</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?= htmlspecialchars($student['first_name'] . ' ' . $student['other_names']) ?></td>
                                <td>
                                    <input type="hidden" name="attendance[<?= $student['user_id'] ?>]" value="0">
                                    <input type="checkbox" name="attendance[<?= $student['user_id'] ?>]" value="1">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Submit Attendance</button>
                </div>
            </form>

        <?php endif; ?>
    <?php endif; ?>

</body>
</html>
