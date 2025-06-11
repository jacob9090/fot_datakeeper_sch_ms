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

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function uploadFile($file, $uploadDir, $allowedTypes, $maxSize = 2097152) {
    if (!empty($file['name'])) {
        if ($file['size'] > $maxSize) {
            return ["status" => "error", "message" => "File size exceeds 2MB: " . $file['name']];
        }

        $fileType = mime_content_type($file['tmp_name']);
        if (!in_array($fileType, $allowedTypes)) {
            return ["status" => "error", "message" => "Invalid file type for: " . $file['name']];
        }

        $fileName = uniqid() . "-" . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return ["status" => "success", "path" => $filePath];
        } else {
            return ["status" => "error", "message" => "Failed to upload: " . $file['name']];
        }
    }
    return ["status" => "success", "path" => null]; // Allow empty file uploads
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $first_name = htmlspecialchars(trim($_POST['first_name']));
        $last_name = htmlspecialchars(trim($_POST['last_name']));
        $role = htmlspecialchars(trim($_POST['role']));
        $user_phone = htmlspecialchars(trim($_POST['user_phone']));
        $user_address = htmlspecialchars(trim($_POST['user_address']));
        $user_district = htmlspecialchars(trim($_POST['user_district']));
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'];

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format.");
        }

        $stmt = $conn->prepare("SELECT email FROM fedco_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            throw new Exception("Email already exists.");
        }
        $stmt->close();

        $allowedTypes = ["image/jpeg", "image/png"];
        $photo = uploadFile($_FILES['user_photo'], "../uploads/user_photo/", $allowedTypes);

        if ($photo['status'] === "error") {
            throw new Exception($photo['message']);
        }

        $hashedPassword = hashPassword($password);

        $sql = "INSERT INTO fedco_users (
            first_name, last_name, role, user_phone, user_address, user_district,
            user_photo, email, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "sssssssss",
            $first_name, $last_name, $role, $user_phone, $user_address, $user_district,
            $photo['path'], $email, $hashedPassword
        );

        if (!$stmt->execute()) {
            throw new Exception("Error inserting user data: " . $stmt->error);
        }

        $_SESSION['success'] = "User successfully added!";
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    } finally {
        header("Location: add-user.php");
        exit();
    }
}
?>
