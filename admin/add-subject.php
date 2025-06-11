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

// SMS functions remain the same as previous implementation
function sendSMS($mobileNumber, $message) { /* ... */ }
function getSMSDeliveryReport($campaign_id) { /* ... */ }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        // Validate required fields
        $required = ['class_name', 'subject'];
        foreach ($required as $field) {
            if ($field === 'subject' && !isset($_POST['subject'])) {
                throw new Exception("Please select at least one subject");
            }
            if (empty($_POST[$field]) && $field !== 'subject') {
                throw new Exception("Required field '$field' is missing");
            }
        }

        // Get and sanitize inputs
        $class_code = htmlspecialchars($_POST['class_name']);
        $rawSubjects = $_POST['subject'];
        $cleanSubjects = array_map(function($subject) {
            return trim(htmlspecialchars($subject));
        }, $rawSubjects);

        // Get class details
        $classQuery = "SELECT class_name FROM class_table WHERE class_code = ?";
        $classStmt = $conn->prepare($classQuery);
        $classStmt->bind_param("s", $class_code);
        $classStmt->execute();
        $classResult = $classStmt->get_result();
        
        if ($classResult->num_rows === 0) {
            throw new Exception("Invalid class selected");
        }
        
        $classData = $classResult->fetch_assoc();
        $class_name = $classData['class_name'];
        $classStmt->close();

        $conn->begin_transaction();

        // Check for existing subjects
        $subjectCheck = $conn->prepare("SELECT subject FROM subjects_table WHERE class_name = ?");
        $subjectCheck->bind_param("s", $class_name);
        $subjectCheck->execute();
        $subjectResult = $subjectCheck->get_result();
        
        $finalSubjects = $cleanSubjects;
        
        if ($subjectResult->num_rows > 0) {
            // Merge existing and new subjects
            $existingRow = $subjectResult->fetch_assoc();
            $existingSubjects = explode(',', $existingRow['subject']);
            $mergedSubjects = array_unique(array_merge($existingSubjects, $cleanSubjects));
            $finalSubjects = $mergedSubjects;
        }

        // Prepare subjects string
        $subjectString = implode(',', $finalSubjects);

        // Upsert operation
        $upsertSql = $subjectResult->num_rows > 0
            ? "UPDATE subjects_table SET subject = ?, on_update = CURRENT_TIMESTAMP() WHERE class_name = ?"
            : "INSERT INTO subjects_table (class_name, subject) VALUES (?, ?)";
        
        $upsertStmt = $conn->prepare($upsertSql);
        if ($subjectResult->num_rows > 0) {
            $upsertStmt->bind_param("ss", $subjectString, $class_name);
        } else {
            $upsertStmt->bind_param("ss", $class_name, $subjectString);
        }
        $upsertStmt->execute();
        $affectedRows = $upsertStmt->affected_rows;
        $upsertStmt->close();

        // Log activity
        $logSql = "INSERT INTO user_activity_log (user_id, first_name, last_name, action) 
                   VALUES (?, ?, ?, ?)";
        $action = "Updated subjects for $class_name: " . implode(', ', $cleanSubjects);
        $stmtLog = $conn->prepare($logSql);
        $stmtLog->bind_param("ssss", 
            $_SESSION['user_id'],
            $_SESSION['first_name'],
            $_SESSION['last_name'],
            $action
        );
        $stmtLog->execute();
        $stmtLog->close();

        // SMS Integration
        $school_mobile_number = ''; // Retrieve this from your database or form
        $smsResponse = null;
        
        if ($affectedRows > 0 && !empty($school_mobile_number)) {
            $message = "Subjects updated for $class_name: " . implode(', ', $cleanSubjects);
            $smsResponse = sendSMS($school_mobile_number, $message);

            if (!$smsResponse['status']) {
                throw new Exception("Subjects updated but SMS failed: " . $smsResponse['message']);
            }
        }

        $conn->commit();

        $_SESSION['success_alert'] = "Subjects updated successfully!" . 
                                   ($smsResponse ? " SMS sent." : "");
        header("Location: add-subject.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        error_log("Subject Error: " . $e->getMessage());
        $_SESSION['danger_alert'] = "Error: " . $e->getMessage();
        header("Location: add-subject.php");
        exit();
    } finally {
        if (isset($classStmt)) $classStmt->close();
        if (isset($subjectCheck)) $subjectCheck->close();
        if (isset($upsertStmt)) $upsertStmt->close();
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

  <title>Daddy - Add Subject</title>

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
                  <a class="nav-main-link" href="add-subject.php">
                    <span class="nav-main-link-name">School Setup</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-class.php">
                    <span class="nav-main-link-name">Add Class</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link active" href="add-subject.php">
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

      <!-- Page Content -->
      <div class="content">

        <div class="row">
            <div class="col-md-4">
                <form action="add-subject.php" method="POST" enctype="multipart/form-data">
                    <div class="block block-rounded">
                        <div class="block-header block-header-default">
                            <h3 class="block-title">Add <small>Subject</small></h3>
                            <div class="block-options">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#upload-teacher-modal">
                                    <i class="fa fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                        <div class="block-content">
                            <div class="col-lg-12 col-xl-12">
                                <!-- Class Dropdown -->
                                <div class="form-floating mb-4">
                                    <select class="form-select" id="class-select" name="class_name" required>
                                        <option value="">Select Class</option>
                                        <?php
                                        $classQuery = "SELECT id, class_name, class_code FROM class_table";
                                        $classResult = $conn->query($classQuery);
                                        while ($class = $classResult->fetch_assoc()) {
                                            echo '<option value="' . htmlspecialchars($class['class_code']) . '">'
                                                . htmlspecialchars($class['class_name']) 
                                                . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <label class="form-label" for="class-select">Class</label>
                                </div>
            
                                <!-- Subject Checkboxes -->
                                <div class="mb-4">
                                    <label class="form-label">Select Subjects:</label>
                                    <div class="row">
                                        <?php
                                        $subjects = ['English', 'Maths', 'Science', 'Computer', 'RME', 'Creative Art'];
                                        foreach ($subjects as $subject) {
                                            echo '<div class="col-md-6 mb-2">';
                                            echo '<div class="form-check">';
                                            echo '<input class="form-check-input" type="checkbox" 
                                                   name="subject[]" 
                                                   id="subject-' . htmlspecialchars($subject) . '" 
                                                   value="' . htmlspecialchars($subject) . '">';
                                            echo '<label class="form-check-label" for="subject-' . htmlspecialchars($subject) . '">'
                                                . htmlspecialchars($subject) . '</label>';
                                            echo '</div></div>';
                                        }
                                        ?>
                                    </div>
                                </div>
            
                                <div class="col-md-12">
                                    <button type="submit" class="btn btn-hero btn-primary me-1 mb-3 btn-block w-100">
                                        <i class="fa fa-fw fa-plus me-1"></i> Add Subjects
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-md-8">
              <form action="send-text.php" method="POST" enctype="multipart/form-data">
                <div class="block block-rounded">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Subject <small>List</small></h3>
                  </div>
                  <div class="block-content">
                    <div class="col-lg-12 col-xl-12">
                      mmm
                    </div>
                  </div>
                </div>
              </form>
            </div>
          </div>
          <!-- END Form Submission in Options -->

          <!-- Upload Student Pop In Block Modal -->
          <div class="modal fade" id="upload-student-modal" tabindex="-1" role="dialog" aria-labelledby="upload-student-modal" aria-hidden="true">
            <div class="modal-dialog modal-dialog-popin" role="document">
              <div class="modal-content">
                <div class="block block-rounded block-themed block-transparent mb-0">
                  <div class="block-header bg-primary-dark">
                    <h3 class="block-title">Upload Student</h3>
                    <div class="block-options">
                      <button type="button" class="btn-block-option" data-bs-dismiss="modal" aria-label="Close">
                        <i class="fa fa-fw fa-times"></i>
                      </button>
                    </div>
                  </div>
                  <div class="block-content">
                    <p>Potenti elit lectus augue eget iaculis vitae etiam</p>
                    <div class="mb-4">
                      <input class="form-control" type="file" name="upload_student" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" id="example-file-input">
                    </div>
                    <button type="submit" class="btn btn-hero btn-primary me-1 mb-3 btn-block w-100">
                      <i class="fa fa-fw fa-upload me-1"></i> Upload Student
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
          <!-- END Upload Student Pop In Block Modal -->


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