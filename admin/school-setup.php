<?php
require_once '../config/paths.php';
require_once '../config/db-config.php';

session_set_cookie_params([
    'lifetime' => 86400,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    $_SESSION['danger_alert'] = "Unauthorized access!";
    header("Location: index.php");
    exit();
}

function uploadFile($file, $uploadDir) {
    try {
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Upload error: " . $file['error']);
        }
        $fileName = uniqid() . "-" . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new Exception("Failed to move uploaded file");
        }
        return $filePath;
    } catch (Exception $e) {
        error_log("Upload error: " . $e->getMessage());
        return null;
    }
}

function sendSMS($mobileNumber, $message) {
    $apiKey = '1AFndTdUCUHgFzpOioeP0fVP3';
    $url = "https://api.mnotify.com/api/sms/quick?key=$apiKey";
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

    $res = json_decode($result, true);
    file_put_contents("sms_log.txt", date("Y-m-d H:i:s") . " - SMS Response: " . print_r($res, true) . "\n", FILE_APPEND);

    return [
        "status" => ($res['status'] ?? '') === "success",
        "message" => $res['message'] ?? "Unknown error",
        "campaign_id" => $res['summary']['_id'] ?? null
    ];
}

function getSMSDeliveryReport($campaign_id) {
    if (!$campaign_id) {
        return ["status" => false, "message" => "Invalid campaign_id."];
    }

    $apiKey = '1AFndTdUCUHgFzpOioeP0fVP3';
    $url = "https://api.mnotify.com/api/campaign/$campaign_id?key=$apiKey";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");

    $result = curl_exec($ch);
    curl_close($ch);

    return json_decode($result, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $required = ['school_type', 'school_name', 'head_first_name', 'head_last_name', 'school_mobile_number'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Required field '$field' is missing");
            }
        }

        $school_type = htmlspecialchars($_POST['school_type']);
        $school_name = trim(htmlspecialchars($_POST['school_name']));
        $head_first_name = htmlspecialchars($_POST['head_first_name']);
        $head_last_name = htmlspecialchars($_POST['head_last_name']);
        $school_mobile_number = htmlspecialchars($_POST['school_mobile_number']);
        $school_address = htmlspecialchars($_POST['school_address'] ?? '');
        $school_email = filter_var($_POST['school_email'] ?? '', FILTER_VALIDATE_EMAIL);
        $school_logo = !empty($_FILES['school_logo']['name']) ? uploadFile($_FILES['school_logo'], "../uploads/school_logo/") : null;

        if (!preg_match('/^0\d{9}$/', $school_mobile_number)) {
            throw new Exception("Invalid mobile number format. Use 10-digit Ghanaian number");
        }

        $checkSql = "SELECT id FROM school_setup_table WHERE LOWER(school_name) = LOWER(?)";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $school_name);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows > 0) {
            $_SESSION['danger_alert'] = "School already exists";
            $checkStmt->close();
            header("Location: school-setup.php");
            exit();
        }
        $checkStmt->close();

        $conn->begin_transaction();

        $sqlSchool = "INSERT INTO school_setup_table (school_type, school_name, head_first_name, head_last_name, school_mobile_number, school_address, school_email, school_logo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmtSchool = $conn->prepare($sqlSchool);
        $stmtSchool->bind_param("ssssssss", $school_type, $school_name, $head_first_name, $head_last_name, $school_mobile_number, $school_address, $school_email, $school_logo);
        $stmtSchool->execute();

        $logSql = "INSERT INTO user_activity_log (user_id, first_name, last_name, action) VALUES (?, ?, ?, ?)";
        $action = "Setup school: $school_name";
        $stmtLog = $conn->prepare($logSql);
        $stmtLog->bind_param("ssss", $_SESSION['user_id'], $_SESSION['first_name'], $_SESSION['last_name'], $action);
        $stmtLog->execute();

        $conn->commit();

        $smsQuery = "SELECT message_to_send FROM sms_setup_table WHERE sms_type = 'School Setup' AND sms_group = 'Setup'";
        $smsResult = $conn->query($smsQuery);
        $message = $smsResult && $smsResult->num_rows ? $smsResult->fetch_assoc()['message_to_send'] : '';

        if ($message && $school_mobile_number) {
            $smsResponse = sendSMS($school_mobile_number, $message);

            if (!$smsResponse['status']) {
                $_SESSION['danger_alert'] = "School setup completed but SMS failed: " . $smsResponse['message'];
                header("Location: school-setup.php");
                exit();
            }

            $deliveryReport = getSMSDeliveryReport($smsResponse['campaign_id']);
            if (!$deliveryReport['status']) {
                file_put_contents("sms_log.txt", date("Y-m-d H:i:s") . " - Failed Delivery Report: " . print_r($deliveryReport, true) . "\n", FILE_APPEND);
            }
        }

        $_SESSION['success_alert'] = "School setup completed successfully. SMS sent.";
        header("Location: school-setup.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Setup Error: " . $e->getMessage());
        $_SESSION['danger_alert'] = "Error: " . $e->getMessage();
        header("Location: school-setup.php");
        exit();
    } finally {
        if (isset($stmtSchool)) $stmtSchool->close();
        if (isset($stmtLog)) $stmtLog->close();
        if (isset($conn)) $conn->close();
    }
} 
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Daddy - School Setup</title>

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
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
                <i class="nav-main-link-icon fa fa-cog"></i>
                <span class="nav-main-link-name">App Settings</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link active" href="school-setup.php">
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
            <h3 class="block-title">School <small>Setup</small></h3>
            <div class="block-options">
              <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#upload-teacher-modal"><i class="fa fa-upload"></i> Upload
              </button>
            </div>
          </div>
          <form action="school-setup.php" method="POST" enctype="multipart/form-data">
          <div class="block-content">
            <div class="row items-push">
                
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <select class="form-select" id="example-select-floating" name="school_type" aria-label="Floating label select example">
                    <option selected>Select School Type</option>
                    <option value="Primary">Primary</option>
                    <option value="JHS">JHS</option>
                    <option value="SHS">SHS</option>
                    <option value="Primary and JHS">Primary and JHS</option>
                    <option value="Primary and SHS">Primary and SHS</option>
                    <option value="Primary, JHS, and SHS">Primary, JHS, and SHS</option>
                  </select>
                  <label class="form-label" for="example-select-floating">School Type</label>
                </div>
              </div>    
                
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="school_name" placeholder="School Name">
                  <label class="form-label" for="example-text-input-floating">Name of School</label>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="head_first_name" placeholder="John" required>
                  <label class="form-label" for="example-text-input-floating">Head's First Name</label>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="text" class="form-control" id="example-text-input-floating" name="head_last_name" placeholder="Doe" required>
                  <label class="form-label" for="example-text-input-floating">Head's Last Name</label>
                </div>
              </div>
              
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="number" class="form-control" id="example-text-input-floating" name="school_mobile_number" placeholder="0241111111">
                  <label class="form-label" for="example-text-input-floating">School Contact Number</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <textarea class="form-control" id="example-textarea-floating" name="school_address" placeholder="Address"></textarea>
                  <label class="form-label" for="example-textarea-floating">Address</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="form-floating mb-4">
                  <input type="email" class="form-control" id="example-email-input-floating" name="school_email" placeholder="john.doe@example.com">
                  <label class="form-label" for="example-email-input-floating">School's Email address</label>
                </div>
              </div>
              <div class="col-md-3">
                <div class="mb-4">
                  <label class="form-label" for="example-file-input">Logo of School</label>
                  <input class="form-control" type="file" name="school_logo" id="example-file-input">
                </div>
              </div>

              <div class="col-md-12">
                <button type="submit" class="btn btn-hero btn-primary me-1 mb-3 btn-block w-50">
                <i class="fa fa-fw fa-plus me-1"></i> Setup School
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

  <?php if (isset($_SESSION['success_alert']) || isset($_SESSION['danger_alert'])) : ?>
    <script>
        window.addEventListener('DOMContentLoaded', () => {
            <?php if (isset($_SESSION['success_alert'])) : ?>
            Dashmix.helpers('jq-notify', {
                type: 'success',
                icon: 'fa fa-check-circle me-1',
                message: <?= json_encode($_SESSION['success_alert']) ?>
            });
            <?php unset($_SESSION['success_alert']); endif; ?>

            <?php if (isset($_SESSION['danger_alert'])) : ?>
            Dashmix.helpers('jq-notify', {
                type: 'danger',
                icon: 'fa fa-exclamation-triangle me-1',
                message: <?= json_encode($_SESSION['danger_alert']) ?>
            });
            <?php unset($_SESSION['danger_alert']); endif; ?>
        });
    </script>
    <?php endif; ?>
    
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
  
  <!-- Page JS Plugins -->
    <script src="../assets/js/plugins/bootstrap-notify/bootstrap-notify.min.js"></script>

    <!-- Page JS Helpers (BS Notify Plugin) -->
    <script>Dashmix.helpersOnLoad(['jq-notify']);</script>
    
</body>

</html>