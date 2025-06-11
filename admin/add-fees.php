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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST["first_name"];
    $other_name = $_POST["other_name"];
    $mobile_number = $_POST["mobile_number"];
    $group_name = $_POST["group_name"];

    $sql = "INSERT INTO teacher_table (first_name, other_name, mobile_number, group_name) 
            VALUES ('$first_name', '$other_name', '$mobile_number', '$group_name')";

    if ($conn->query($sql) === TRUE) {
        // Redirect to a success page or display a success message
        header("Location: contact.php"); 
        exit;
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}


// Validate user authentication
// $accountant_id = $_SESSION['user_id'] ?? null;
// if ($accountant_id === null) {
//     die("Error: User not authenticated. Please log in again.");
// }

// Get Accountant Info
// $stmt = $conn->prepare("SELECT first_name, last_name FROM admin_table WHERE user_id = ?");
// $stmt->bind_param("s", $accountant_id);
// $stmt->execute();
// $accountant_result = $stmt->get_result();
// $accountant_data = $accountant_result->fetch_assoc();
// if (!$accountant_data) {
//     die("Error: Accountant details not found.");
// }
// $accountant_name = $accountant_data['first_name'] . " " . $accountant_data['last_name'];

// Get Current Academic Year & Semester
// $academic_year = "";
// $semester = "";
// $calendar_query = "SELECT academic_year, semester FROM academic_calendar ORDER BY academic_year DESC LIMIT 1";
// $calendar_result = $conn->query($calendar_query);
// if ($calendar_result->num_rows > 0) {
//     $calendar = $calendar_result->fetch_assoc();
//     $academic_year = $calendar['academic_year'];
//     $semester = $calendar['semester'];
// }

// Handle GET Request: Search Student
$searchPerformed = false;
$student_data = null;
$fees_owned = 0.00;
$total_paid = 0.00;
$balance = 0.00;
$payment_date = "N/A";

if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
    $searchPerformed = true;
    $user_id = trim($_GET['user_id']);
    
    

    try {
        // Fetch student details
        $stmt = $conn->prepare("SELECT user_id, first_name, last_name, gender 
                               FROM students 
                               WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        
        if (!$stmt->execute()) {
            throw new Exception("Database query failed: " . $stmt->error);
        }
        
        $student_result = $stmt->get_result();
        
        if ($student_result->num_rows > 0) {
            $student_data = $student_result->fetch_assoc();

            // Fetch Financial Information in a transaction
            $conn->begin_transaction();
            
            try {
                // Get Total Fees Owned
                $stmt = $conn->prepare("SELECT COALESCE(SUM(fees_owned), 0.00) AS total_fees_owned 
                                        FROM student_fees 
                                        WHERE user_id = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $fees_data = $stmt->get_result()->fetch_assoc();
                $fees_owned = (float)$fees_data['total_fees_owned'];

                // Get Total Payments
                $stmt = $conn->prepare("SELECT COALESCE(SUM(amount_paid), 0.00) AS total_paid,
                                               MAX(payment_date) AS last_payment_date 
                                        FROM fee_payments 
                                        WHERE user_id = ?");
                $stmt->bind_param("s", $user_id);
                $stmt->execute();
                $payment_data = $stmt->get_result()->fetch_assoc();
                $total_paid = (float)$payment_data['total_paid'];
                $payment_date = $payment_data['last_payment_date'] ?? "N/A";

                // Calculate balance
                $balance = $fees_owned - $total_paid;
                
                $conn->commit();
            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception("Financial data retrieval failed: " . $e->getMessage());
            }
        } else {
            $student_data = null;  // Explicitly set to null for clarity
        }
    } catch (Exception $e) {
        error_log("Student search error: " . $e->getMessage());
        die("An error occurred during the student search. Please try again.");
    }
}

// Handle Payment Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST["user_id"];
    $amount_paid = $_POST["amount_paid"];
    $payment_date = date("Y-m-d H:i:s");

    // Insert Payment Record
    $stmt = $conn->prepare("INSERT INTO fee_payments (user_id, academic_year, semester, amount_paid, balance, payment_date) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdds", $user_id, $academic_year, $semester, $amount_paid, $balance, $payment_date);
    $stmt->execute();

    // Insert into user_activity_log
    $action = "Added payment of $" . number_format($amount_paid, 2) . " for student ID " . $user_id;
    $stmt = $conn->prepare("INSERT INTO user_activity_log (user_id, first_name, last_name, action) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $accountant_id, $accountant_data['first_name'], $accountant_data['last_name'], $action);
    $stmt->execute();

    // Check Fees Verification
    $fees_percentage_query = "SELECT fees_percentage FROM fees_pass_percentage LIMIT 1";
    $result = $conn->query($fees_percentage_query);
    if ($result->num_rows > 0) {
        $fees_data = $result->fetch_assoc();
        $fees_percentage = $fees_data['fees_percentage'];

        // Re-fetch the updated total fees_owned and total_paid after payment insertion
        $stmt = $conn->prepare("SELECT COALESCE(SUM(fees_owned), 0) AS total_fees_owned FROM student_fees WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $fees_result = $stmt->get_result();
        $fees_data = $fees_result->fetch_assoc();
        $updated_fees_owned = (float)$fees_data['total_fees_owned'];

        $stmt = $conn->prepare("SELECT COALESCE(SUM(amount_paid), 0) AS total_paid FROM fee_payments WHERE user_id = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $payment_result = $stmt->get_result();
        $payment_data = $payment_result->fetch_assoc();
        $updated_total_paid = (float)$payment_data['total_paid'];

        // Calculate is_verify with updated totals
        $is_verify = 0;
        if ($updated_fees_owned > 0) { // Avoid division by zero
            $percentage = ($updated_total_paid / $updated_fees_owned) * 100;
            $is_verify = ($percentage >= $fees_percentage) ? 1 : 0;
        }

        $verification_code = time();

        // Insert or Update accounts_verification
        $stmt = $conn->prepare("
            INSERT INTO accounts_verification (user_id, is_verify, verification_code, verify_by)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE is_verify = VALUES(is_verify), verification_code = VALUES(verification_code), verify_by = VALUES(verify_by)
        ");
        $stmt->bind_param("siss", $user_id, $is_verify, $verification_code, $accountant_name);
        $stmt->execute();
    }

    $notification = "Payment recorded successfully!";
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Daddy - Add Fees</title>

  <meta name="description"
    content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave and published on Themeforest">
  <meta name="author" content="pixelcave">
  <meta name="robots" content="noindex, nofollow">

  <!-- Open Graph Meta -->
  <meta property="og:title" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework">
  <meta property="og:site_name" content="Dashmix">
  <meta property="og:description"
    content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave and published on Themeforest">
  <meta property="og:type" content="website">
  <meta property="og:url" content="">
  <meta property="og:image" content="">

  <!-- Icons -->
  <!-- The following icons can be replaced with your own, they are used by desktop and mobile browsers -->
  <link rel="shortcut icon" href="../assets/media/favicons/favicon.png">
  <link rel="icon" type="image/png" sizes="192x192" href="../assets/media/favicons/favicon-192x192.png">
  <link rel="apple-touch-icon" sizes="180x180" href="../assets/media/favicons/apple-touch-icon-180x180.png">
  <!-- END Icons -->

  <!-- Stylesheets -->
  <!-- Page JS Plugins CSS -->
  <link rel="stylesheet" href="../assets/js/plugins/select2/css/select2.min.css">

  <!-- Dashmix framework -->
  <link rel="stylesheet" id="css-main" href="../assets/css/dashmix.min.css">

  <style>
      .row-area-custom .row {
        margin-left: 1px;
        margin-right: 1px;
        padding-top: 0.90rem;
        padding-bottom: 0.90rem;
        background-color: rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.125);
      }

      table {
        display: block; /* Ensure table behavior */
        width: 100% !important; /* Make the table responsive */
        border-collapse: collapse;
        border-spacing: 0;
        overflow-x: auto;
        white-space: nowrap;
        font-size: small;
      }

      table th, table td {
        border: 1px solid #ddd;
        padding: 8px;
        text-align: left;
      }

      table th {
        background-color: #c5c4c3;
      }

      /* Responsive design (optional) */
      @media (max-width: 768px) {
        table {
          font-size: 0.8em;
        }
      }

      /* Customize scrollbar appearance (optional) */
      ::-webkit-scrollbar {
        width: 10px;
      }

      ::-webkit-scrollbar-track {
        background-color: #f1f1f1;
      }

      ::-webkit-scrollbar-thumb {
        background-color: #888;
        border-radius: 10px;
      }
    </style>

</head>

<body>
  
    <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-fixed">
      
    <!-- Sidebar -->
    <nav id="sidebar" class="with-mini-nav" aria-label="Main Navigation">
      <!-- Sidebar Mini Nav -->
      <div class="sidebar-mini-nav">
        <!-- Logo -->
        <a href="index.html" class="mini-nav-item h-header">
          <i class="fa fa-times text-primary-light fs-lg"></i>
        </a>
        <!-- END Logo -->

        <!-- Mini Main Nav -->
        <nav class="flex-grow-1 space-y-1">
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-briefcase fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-chart-pie fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-users fs-sm"></i>
          </a>
        </nav>
        <!-- END Mini Main Nav -->

        <!-- Mini User Nav -->
        <nav class="space-y-1 pb-2">
          <a class="mini-nav-item" href="../be_pages_generic_profile.html">
            <i class="fa fa-cog fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="op_auth_signin.html">
            <i class="fa fa-lock fs-sm"></i>
          </a>
        </nav>
        <!-- END Mini User Nav -->
      </div>
      <!-- END Sidebar Mini Nav -->

      <!-- Sidebar Scrolling -->
      <div class="js-sidebar-scroll full-height">
        <!-- Side Header -->
        <div class="bg-header-dark">
          <div class="content-header bg-white-5">
            <!-- Logo -->
            <a class="fw-semibold text-white tracking-wide" href="index.html">
              <span class="smini-visible">Daddy</span>
              <span class="smini-hidden">Daddy</span>
            </a>
            <!-- END Logo -->

            <!-- Options -->
            <div>
              <!-- Toggle Sidebar Style -->
              <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
              <!-- Class Toggle, functionality initialized in Helpers.dmToggleClass() -->
              <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="class-toggle"
                data-target="#sidebar-style-toggler" data-class="fa-toggle-off fa-toggle-on"
                onclick="Dashmix.layout('sidebar_style_toggle');Dashmix.layout('header_style_toggle');">
                <i class="fa fa-toggle-off" id="sidebar-style-toggler"></i>
              </button>
              <!-- END Toggle Sidebar Style -->

              <!-- Dark Mode -->
              <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
              <button type="button" class="btn btn-sm btn-alt-secondary" data-toggle="class-toggle"
                data-target="#dark-mode-toggler" data-class="far fa" onclick="Dashmix.layout('dark_mode_toggle');">
                <i class="far fa-moon" id="dark-mode-toggler"></i>
              </button>
              <!-- END Dark Mode -->

              <!-- Close Sidebar, Visible only on mobile screens -->
              <!-- Layout API, functionality initialized in Template._uiApiLayout() -->
              <button type="button" class="btn btn-sm btn-alt-secondary d-lg-none" data-toggle="layout"
                data-action="sidebar_close">
                <i class="fa fa-times-circle"></i>
              </button>
              <!-- END Close Sidebar -->
            </div>
            <!-- END Options -->
          </div>
        </div>
        <!-- END Side Header -->

        <!-- Side Navigation -->
        <div class="content-side">
          <ul class="nav-main">
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-dashboard"></i>
                <span class="nav-main-link-name">Dashboard</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="school-overview.php">
                    <span class="nav-main-link-name">School Overview</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="financial-overview.php">
                    <span class="nav-main-link-name">Financials</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="performance-overview.php">
                    <span class="nav-main-link-name">Performance</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="attendance-overview.php">
                    <span class="nav-main-link-name">Attendance</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-heading">Menu</li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-user"></i>
                <span class="nav-main-link-name">Teacher</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-teacher.php">
                    <span class="nav-main-link-name">Add Teacher</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-teacher.php">
                    <span class="nav-main-link-name">Manage Teacher</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-users"></i>
                <span class="nav-main-link-name">Student</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-student.php">
                    <span class="nav-main-link-name">Add Student</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-student.php">
                    <span class="nav-main-link-name">Manage Student</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-calendar-check"></i>
                <span class="nav-main-link-name">Attendance</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-attendance.php">
                    <span class="nav-main-link-name">Add Attendance</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-attendance.php">
                    <span class="nav-main-link-name">Manage Attendance</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-bell"></i>
                <span class="nav-main-link-name">Notice</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-notice.php">
                    <span class="nav-main-link-name">Add Notice</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-table"></i>
                <span class="nav-main-link-name">Time Table</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-timetable.php">
                    <span class="nav-main-link-name">Add Time Table</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-timetable.php">
                    <span class="nav-main-link-name">Manage Time Table</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-file"></i>
                <span class="nav-main-link-name">Syllabus</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-syllabus.php">
                    <span class="nav-main-link-name">Add Syllabus</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-syllabus.php">
                    <span class="nav-main-link-name">Manage Syllabus</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-sticky-note"></i>
                <span class="nav-main-link-name">Assignment</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-assignment.php">
                    <span class="nav-main-link-name">Add Assignment</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-assignment.php">
                    <span class="nav-main-link-name">Manage Assignment</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-paste"></i>
                <span class="nav-main-link-name">Marks</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-marks.php">
                    <span class="nav-main-link-name">Add Marks</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-marks.php">
                    <span class="nav-main-link-name">Manage Marks</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-bus"></i>
                <span class="nav-main-link-name">Bus Services</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-bus.php">
                    <span class="nav-main-link-name">Add Bus</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-bus.php">
                    <span class="nav-main-link-name">Manage Bus</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-commenting"></i>
                <span class="nav-main-link-name">Message</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="send-text.php">
                    <span class="nav-main-link-name">Send Text</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="send-email.php">
                    <span class="nav-main-link-name">Send Email</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-print"></i>
                <span class="nav-main-link-name">Report Card</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="view-report.php">
                    <span class="nav-main-link-name">View Report</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="print-report.php">
                    <span class="nav-main-link-name">Print Report</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
                <i class="nav-main-link-icon fa fa-print"></i>
                <span class="nav-main-link-name">Fees</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link active" href="add-fees.php">
                    <span class="nav-main-link-name">Add Fees</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-fees.php">
                    <span class="nav-main-link-name">Manage Fees</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="print-fees-receipt.php">
                    <span class="nav-main-link-name">Print Receipt</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-heading">Settings</li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-cog"></i>
                <span class="nav-main-link-name">App Settings</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="school-setup.php">
                    <span class="nav-main-link-name">School Setup</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-class.php">
                    <span class="nav-main-link-name">Add Class</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-subject.php">
                    <span class="nav-main-link-name">Add Subject</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="message-setup.php">
                    <span class="nav-main-link-name">Message Setup</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-heading">Past Records</li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-archive"></i>
                <span class="nav-main-link-name">Archives</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="teachers-archive.php">
                    <span class="nav-main-link-name">Teachers</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="students-archive.php">
                    <span class="nav-main-link-name">Students</span>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
        <!-- END Side Navigation -->
      </div>
      <!-- END Sidebar Scrolling -->
    </nav>
    <!-- END Sidebar -->

    <!-- Header -->
    <?php include('../include/header.php'); ?>
    <!-- END Header -->

    <!-- Main Container -->
    <main id="main-container">
      <!-- Hero -->
      <div class="content">
        
        <!-- Bootstrap Notification -->
        <?php if (!empty($notification)) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= htmlspecialchars($notification) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php } ?>
        
         <div class="p-3 bg-body-extra-light rounded push">
         <div class="block-header block-header-default">
                  <h3 class="block-title">Add <small>Payments</small></h3>
                  <div class="block-options"></div>
                </div><hr/>
        <!-- Student Search Form -->
       
        <form method="GET" class="row">
            <div class="col-md-8">
                <input type="text" name="user_id" class="form-control" placeholder="Enter Student ID">
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        <hr/>
        

        <?php if (isset($student_data) && $student_data !== null) { ?>
        <!-- Display student details -->
        
    <h5><i class="fas fa-user-check text-primary"></i>&nbsp;Student Details</h5>
    <table class="table table-bordered">
        <tr><th>User ID</th><td><?= htmlspecialchars($student_data['user_id']) ?></td></tr>
        <tr><th>Name</th><td><?= htmlspecialchars($student_data['first_name'] . " " . $student_data['last_name']) ?></td></tr>
        <tr><th>Gender</th><td><?= htmlspecialchars($student_data['gender']) ?></td></tr>
       
    </table><hr/>

    <h5><i class="fas fa-money-bill text-primary"></i>&nbsp;Financial Details (Academic Year: <?= $academic_year ?>, Semester: <?= $semester ?>)</h5>
    <table class="table table-striped">
        <tr><th>Total Fees Owned</th><td>&#8373;&nbsp;<?= number_format($fees_owned, 2) ?></td></tr>
        <tr><th>Total Paid</th><td>&#8373;&nbsp;<?= number_format($total_paid, 2) ?></td></tr>
        <tr><th>Balance</th><td><strong class="text-danger">&#8373;&nbsp;<?= number_format($balance, 2) ?></strong></td></tr>
        <tr><th>Last Payment Date</th><td><?= htmlspecialchars($payment_date) ?></td></tr>
    </table>

    <!-- Payment Form -->
    <h5><i class="fas fa-money-bill-alt text-success"></i>&nbsp; Enter Payment</h5>
    <form method="POST" class="row g-3">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($student_data['user_id']) ?>">
        <div class="col-md-4">
            <input type="number" name="amount_paid" class="form-control" placeholder="Enter Amount" required>
        </div><hr/>
        <div class="col-md-4">
            <button type="submit" class="btn btn-success">Make Payment</button>
        </div>
    </form>
<?php } else { ?>
            <?php if ($searchPerformed) { ?>
                <div class="alert alert-warning">No student found. Please enter a valid Student ID.</div>
            <?php } ?>
        <?php } ?>
        
        
      </div>
</div>
      </div>
      <!-- END Hero -->
      
      </div>
      <!-- END Page Content -->
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    <?php include('../include/footer.php'); ?>
    <!-- END Footer -->
  </div>
  <!-- END Page Container -->

  <!--
      Core libraries and functionality
      webpack is putting everything together at assets/_js/main/app.js
    -->
  <script src="../assets/js/dashmix.app.min.js"></script>

  <!-- jQuery (required for Select2 + jQuery Validation plugins) -->
  <script src="../assets/js/lib/jquery.min.js"></script>

  <!-- Page JS Plugins -->
  <script src="../assets/js/plugins/select2/js/select2.full.min.js"></script>
  <script src="../assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>
  <script src="../assets/js/plugins/jquery-validation/additional-methods.js"></script>

  <!-- Page JS Helpers (Select2 plugin) -->
  <script>Dashmix.helpersOnLoad(['jq-select2']);</script>

  <!-- Page JS Code -->
  <script src="../assets/js/pages/be_forms_validation.min.js"></script>
  
  <script type="text/javascript">
    $(".alert").delay(4000).slideUp(500, function() {
        $(this).alert('close');
    });
  </script>

  <script>
      
    const amountToPaidInput = document.getElementById('amount_to_paid');
    const amountPaidInput = document.getElementById('amount_paid');
    const amountBalanceInput = document.getElementById('amount_balance');

    amountPaidInput.addEventListener('input', () => {
      const amountToPaid = parseFloat(amountToPaidInput.value);
      const amountPaid = parseFloat(amountPaidInput.value);

      if (!isNaN(amountToPaid) && !isNaN(amountPaid)) {
        const amountBalance = amountToPaid - amountPaid;
        amountBalanceInput.value = amountBalance.toFixed(2); 
      } else {
        amountBalanceInput.value = ''; 
      }
    });
    
  </script>
</body>

</html>