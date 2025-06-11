<?php
// Set secure session cookie parameters FIRST
require_once 'config/paths.php';

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => $subfolder,
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
require_once 'config/paths.php';
require_once 'config/db-config.php';

// Initialize security parameters
$max_attempts = 5;
$lockout_time = 300; // 5 minutes

try {
    // Validate request method
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        throw new Exception("Invalid request method");
    }

    // Initialize CSRF token
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        throw new Exception("Security token validation failed");
    }

    // Validate inputs
    if (empty($_POST['email']) || empty($_POST['password'])) {
        throw new Exception("Please fill in all fields");
    }

    // Sanitize email
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    if (!$email) {
        throw new Exception("Invalid email format");
    }

    // Check rate limits
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 0;
        $_SESSION['first_failed_attempt'] = 0;
    }

    if ($_SESSION['login_attempts'] >= $max_attempts && 
        (time() - $_SESSION['first_failed_attempt']) < $lockout_time) {
        throw new Exception("Too many attempts. Try again later.");
    }

    // Database operations
    $stmt = $conn->prepare("SELECT user_id, first_name, last_name, role, password, is_active 
                          FROM user_table 
                          WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database error. Please try again.");
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // User existence check
    if ($stmt->num_rows === 0) {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] === 1) {
            $_SESSION['first_failed_attempt'] = time();
        }
        throw new Exception("Invalid credentials");
    }

    // Bind results
    $stmt->bind_result($user_id, $first_name, $last_name, $role, $hashedPassword, $is_active);
    $stmt->fetch();

    // Account status check
    if (!$is_active) {
        throw new Exception("Account disabled. Contact administrator.");
    }

    // Password verification
    if (!password_verify($_POST['password'], $hashedPassword)) {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] === 1) {
            $_SESSION['first_failed_attempt'] = time();
        }
        throw new Exception("Invalid credentials");
    }

    // Successful login
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user_id;
    $_SESSION['first_name'] = $first_name;
    $_SESSION['last_name'] = $last_name;
    $_SESSION['email'] = $email;
    $_SESSION['role'] = $role;
    $_SESSION['last_login'] = time();

    // Reset login attempts
    $_SESSION['login_attempts'] = 0;
    $_SESSION['first_failed_attempt'] = 0;

    // Log activity
    $log_stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, action) VALUES (?, 'Login')");
    $log_stmt->bind_param("s", $user_id);
    $log_stmt->execute();
    $log_stmt->close();

    // Set redirect paths
    $redirects = [
        'Admin' => BASE_URL . 'admin/school-overview.php',
        'Accounts' => BASE_URL . 'accounts/school-overview.php',
        'Teacher' => BASE_URL . 'teacher/school-overview.php',
        'Driver' => BASE_URL . 'driver/school-overview.php',
        'Student' => BASE_URL . 'student/student-overview.php'
    ];

    // Validate role
    if (!isset($redirects[$role])) {
        session_destroy();
        throw new Exception("Invalid user role configuration");
    }

    // Cleanup and redirect
    $stmt->close();
    $conn->close();
    
    //header("Location: " . $redirects[$role]);
    header("Location: " . ($redirects[$role] ?? BASE_URL . 'index.php'));
    exit();

} catch (Exception $e) {
    // Error handling
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($log_stmt) && $log_stmt instanceof mysqli_stmt) {
        $log_stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    $_SESSION['danger_alert'] = $e->getMessage();
    header("Location: " . BASE_URL . "index.php");
    exit();
}




// session_start();
// require_once 'config/db-config.php';

// if ($_SERVER["REQUEST_METHOD"] === "POST") {
//     $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
//     $password = $_POST['password'];

//     $stmt = $conn->prepare("SELECT user_id, first_name, last_name, role, password FROM user_table WHERE email = ?");
//     $stmt->bind_param("s", $email);
//     $stmt->execute();
//     $stmt->store_result();

//     if ($stmt->num_rows > 0) {
//         $stmt->bind_result($user_id, $first_name, $last_name, $role, $hashedPassword);
//         $stmt->fetch();

//         if (password_verify($password, $hashedPassword)) {
//             $_SESSION['user_id'] = $user_id;
//             $_SESSION['first_name'] = $first_name;
//             $_SESSION['last_name'] = $last_name;
//             $_SESSION['email'] = $email;
//             $_SESSION['role'] = $role;

//             $_SESSION['success_alert'] = "Welcome, $first_name!";

//             switch ($role) {
//                 case 'Admin':
//                     header("Location: admin/school-overview.php");
//                     break;
//                 case 'Accounts':
//                     header("Location: accounts/school-overview.php");
//                     break;
//                 case 'Teacher':
//                     header("Location: teacher/school-overview.php");
//                     break;
//                 case 'Driver':
//                     header("Location: driver/school-overview.php");
//                     break;
//                 case 'Student':
//                     header("Location: student/student-overview.php");
//                     break;
//                 default:
//                     $_SESSION['danger_alert'] = "Invalid user role.";
//                     header("Location: index.php");
//                     break;
//             }
//             exit();
//         } else {
//             $_SESSION['danger_alert'] = "Incorrect email or password.";
//             header("Location: index.php");
//             exit();
//         }
//     } else {
//         $_SESSION['danger_alert'] = "User does not exist.";
//         header("Location: index.php");
//         exit();
//     }

//     $stmt->close();
//     $conn->close();
// }
?>
