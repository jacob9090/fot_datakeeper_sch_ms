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

// Function to hash passwords securely
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// Function to handle file uploads
function uploadFile($file, $uploadDir) {
    if (!empty($file['name'])) {
        $fileName = uniqid() . "-" . basename($file['name']);
        $filePath = $uploadDir . $fileName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            return $filePath;
        }
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_POST['user_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $gender = $_POST['gender'];
    $mobile_number = $_POST['mobile_number'];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $photo_path = uploadFile($_FILES['teacher_photo'], "../uploads/teacher_photos/");

    // Check if email already exists
    $stmt = $conn->prepare("SELECT email FROM user_table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Email already exists."]);
        exit();
    }
    $stmt->close();

    // Hash password
    $hashedPassword = hashPassword($password);

    // Insert into user_table
    $sqlUsers = "INSERT INTO user_table (user_id, first_name, last_name, email, password, role)
                 VALUES (?, ?, ?, ?, ?, 'Teacher')";
    $stmtUsers = $conn->prepare($sqlUsers);
    $stmtUsers->bind_param("sssss", $user_id, $first_name, $last_name, $email, $hashedPassword);

    if ($stmtUsers->execute()) {
        // Insert into teacher_table
        $sqlTeacher = "INSERT INTO teacher_table (user_id, first_name, last_name, gender, mobile_number, address, email, photo_path)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtTeacher = $conn->prepare($sqlTeacher);
        $stmtTeacher->bind_param("ssssssss", $user_id, $first_name, $last_name, $gender, $mobile_number, $address, $email, $photo_path);

        if ($stmtTeacher->execute()) {
            // Log activity
            $logSql = "INSERT INTO user_activity_log (user_id, first_name, last_name, action) 
                       VALUES (?, ?, ?, ?)";
            $action = "Added new teacher: $first_name $last_name";
            $stmtLog = $conn->prepare($logSql);
            $stmtLog->bind_param("ssss", $_SESSION['user_id'], $_SESSION['first_name'], $_SESSION['last_name'], $action);

            if (!$stmtLog->execute()) {
                file_put_contents("error_log.txt", "Log Insert Error: " . $stmtLog->error . "\n", FILE_APPEND);
                die(json_encode(["status" => "error", "message" => "Error inserting into user_activity_log"]));
            }
            $stmtLog->close();

            // Send SMS
            $smsQuery = "SELECT message_to_send FROM sms_setup_table WHERE sms_type = 'Add Teacher' AND sms_group = 'Teacher'";
            $smsResult = $conn->query($smsQuery);

            if ($smsResult && $smsResult->num_rows > 0) {
                $smsData = $smsResult->fetch_assoc();
                $message = $smsData['message_to_send'];

                $smsResponse = sendSMS($mobile_number, $message);

                if (!$smsResponse['status']) {
                    die(json_encode(["status" => "error", "message" => $smsResponse['message']]));
                }

                // Fetch Delivery Report
                $deliveryReport = getSMSDeliveryReport($smsResponse['campaign_id']);

                if (!$deliveryReport['status']) {
                    file_put_contents("sms_delivery_log.txt", date("Y-m-d H:i:s") . " - Failed Delivery Report: " . print_r($deliveryReport, true) . "\n", FILE_APPEND);
                    die(json_encode(["status" => "error", "message" => "Failed to retrieve SMS delivery report."]));
                }
            } else {
                die(json_encode(["status" => "error", "message" => "SMS template not found."]));
            }

            echo json_encode(["status" => "success", "message" => "Teacher added successfully. SMS sent."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error inserting teacher data."]);
        }

        $stmtTeacher->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Error inserting user data."]);
    }

    $stmtUsers->close();
    $conn->close();
}

// Function to send SMS via API
function sendSMS($mobileNumber, $message) {
    $apiKey = '1AFndTdUCUHgFzpOioeP0fVP3';
    $url = "https://api.mnotify.com/api/sms/quick?key=" . $apiKey;

    $data = [
        'recipient' => [$mobileNumber],
        'sender' => 'FoT School',
        'message' => $message,
        'is_schedule' => 'false',
        'schedule_date' => ''
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    curl_close($ch);

    $decodedResult = json_decode($result, true);
    file_put_contents("sms_log.txt", date("Y-m-d H:i:s") . " - SMS Response: " . print_r($decodedResult, true) . "\n", FILE_APPEND);

    return [
        "status" => $decodedResult['status'] === "success",
        "message" => $decodedResult['message'] ?? "Unknown error",
        "campaign_id" => $decodedResult['summary']['_id'] ?? null
    ];
}

// Function to fetch SMS delivery report
function getSMSDeliveryReport($campaign_id) {
    if (!$campaign_id) {
        return ["status" => false, "message" => "Invalid campaign_id."];
    }

    $apiKey = '1AFndTdUCUHgFzpOioeP0fVP3';
    $url = "https://api.mnotify.com/api/campaign/$campaign_id?key=" . $apiKey;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}
?>



<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Daddy - Add Teacher</title>

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

  <!-- Dashmix framework -->
  <link rel="stylesheet" id="css-main" href="../assets/css/dashmix.min.css">

</head>

<body>
  
    <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-fixed">
      
    <!-- Sidebar -->
    <nav id="sidebar" class="with-mini-nav" aria-label="Main Navigation">
      <!-- Sidebar Mini Nav -->
      <div class="sidebar-mini-nav">
        <!-- Logo -->
        <a href="sustainability-overview.php" class="mini-nav-item h-header">
          <i class="fa fa-tree text-primary-light fs-lg"></i>
        </a>
        <!-- END Logo -->

        <!-- Mini Main Nav -->
        <nav class="flex-grow-1 space-y-1">
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-users fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-location fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="javascript:void(0)">
            <i class="fa fa-city fs-sm"></i>
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
            <a class="fw-semibold text-white tracking-wide" href="sustainability-overview.php">
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
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
                <i class="nav-main-link-icon fa fa-user"></i>
                <span class="nav-main-link-name">Teacher</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link active" href="add-teacher.php">
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
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-print"></i>
                <span class="nav-main-link-name">Fees</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-fees.php">
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

      <div class="content">
        <div class="block block-rounded">
          <div class="block-header block-header-default">
            <h3 class="block-title">Add <small>Teacher</small></h3>
            <div class="block-options">
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#upload-teacher-modal"><i class="fa fa-upload"></i> Upload
              </button>
            </div>
          </div>
          <form action="add-teacher.php" method="POST" enctype="multipart/form-data">
          <div class="block-content">
            <div class="row items-push">
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="user_id" placeholder="ID12345">
                  <label class="form-label" for="example-text-input-floating">Staff ID</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="first_name" placeholder="John" required>
                  <label class="form-label" for="example-text-input-floating">First Name</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="last_name" placeholder="John Doe" required>
                  <label class="form-label" for="example-text-input-floating">Other Name(s)</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <select class="form-select" id="example-select-floating" name="gender" aria-label="Floating label select example">
                    <option selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                  </select>
                  <label class="form-label" for="example-select-floating">Gender</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="number" class="form-control" id="example-text-input-floating" name="mobile_number" placeholder="0241111111">
                  <label class="form-label" for="example-text-input-floating">Contact Number</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <textarea class="form-control" id="example-textarea-floating" name="address" placeholder="Leave a comment here"></textarea>
                  <label class="form-label" for="example-textarea-floating">Address</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="email" class="form-control" id="example-email-input-floating" name="email" placeholder="john.doe@example.com">
                  <label class="form-label" for="example-email-input-floating">Email address</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="password" class="form-control" id="example-password-input-floating" name="password" placeholder="**********">
                  <label class="form-label" for="example-email-input-floating">Password</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-4">
                  <label class="form-label" for="example-file-input">Photo</label>
                  <input class="form-control" type="file" name="teacher_photo" id="example-file-input">
                </div>
              </div>

              <div class="col-md-12">
                <button type="submit" class="btn btn-hero btn-primary me-1 mb-3 btn-block w-50">
                <i class="fa fa-fw fa-plus me-1"></i> Add Teacher
              </button>
              </div>

            </div>
          </div>
          </form>
        </div>
        <!-- END Paragraphs in Grid -->

        <!-- Upload Teacher Modal -->
          <div class="modal fade" id="upload-teacher-modal" tabindex="-1" role="dialog" aria-labelledby="upload-teacher-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-popin" role="document">
              <div class="modal-content">
                <div class="block block-rounded block-themed block-transparent mb-0">
                  <div class="block-header bg-primary-dark">
                    <h3 class="block-title">Upload Teacher</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content">
                    <p>Potenti elit lectus augue eget iaculis vitae etiam</p>
                    <div class="mb-4">
                      <input class="form-control" type="file" name="upload_teacher" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" id="example-file-input">
                    </div>
                    <button type="submit" class="btn btn-hero btn-primary me-1 mb-3 btn-block w-100">
                      <i class="fa fa-fw fa-upload me-1"></i> Upload Teacher
                    </button>
                  </div>
                  <div class="block-content block-content-full text-end bg-body">
                    <button type="button" class="btn btn-sm btn-alt-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary" data-bs-dismiss="modal">Done</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- END Upload Teacher Modal -->

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
</body>

</html>