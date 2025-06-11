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

$sql = "SELECT * FROM student_table";
$result = $conn->query($sql);

// Create an array to store the data
$data = array();

while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Convert the data to JSON format
$jsonData = json_encode($data);

$conn->close();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Daddy - Manage Student</title>

  <meta name="description" content="">
  <meta name="author" content="">
  <meta name="robots" content="">

  <!-- Open Graph Meta -->
  <meta property="og:title" content="">
  <meta property="og:site_name" content="Dashmix">
  <meta property="og:description" content="">
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
  <link rel="stylesheet" href="../assets/js/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/ion-rangeslider/css/ion.rangeSlider.css">
  <link rel="stylesheet" href="../assets/js/plugins/dropzone/min/dropzone.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/flatpickr/flatpickr.min.css">

  <link rel="stylesheet" href="../assets/js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css">

  <!-- Stylesheets -->
  <link rel="stylesheet" id="css-main" href="../assets/css/dashmix.min.css">

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

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
      
      .text-center {
            text-align: left !important;
        }
        
        .dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0em 0em !important;
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
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
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
                  <a class="nav-main-link active" href="manage-student.php">
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

      <!-- Page Content -->
      <div class="content">
        <div class="block block-rounded text-center">
          <div class="block-content">
            <div class="col-lg-12 col-xl-12">
                
                <div class="row mb-3">
            <div class="col-md-3">
                <label for="communityFilter">Filter by Class</label>
                <select id="communityFilter" class="form-control" multiple="multiple">
                    <option value="">All Classes</option>
                    <?php while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['hh_district']) . "'>" . htmlspecialchars($row['hh_district']) . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="farmerFilter">Filter by Name</label>
                <input type="text" id="farmerFilter" class="form-control" placeholder="Enter Student Name">
            </div>
            <div class="col-md-2">
                <label for="startDateFilter">Start Date</label>
                <input type="date" id="startDateFilter" class="form-control">
            </div>
            <div class="col-md-2">
                <label for="endDateFilter">End Date</label>
                <input type="date" id="endDateFilter" class="form-control">
            </div>
            <div class="col-md-2">
                <button class="btn btn-secondary" id="exportBtn" style="margin-top: 25px">
                    <i class="bi bi-cloud-download-fill ms-auto"></i> <span>Export Records</span>
                </button>
            </div>
        </div>
                
          </div>
        </div>
      </div>

        <!-- Dynamic Table Full Pagination -->
        <div class="block block-rounded">
          <div class="block-header block-header-default">
            <h3 class="block-title">
              Manage <small>Student</small>
            </h3>
          </div>
          <div class="block-content block-content-full">
              
              
            <div id="demo_info" class="box"></div>
            <table id="example" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Farmer ID</th>
                        <th>District</th>
                        <th>Community</th>
                        <th>Is the head of the household a farm owner or a sharecropper?</th>
                        <th>Total number of people living in this household</th>
                        <th>How long has this household lived here?</th>
                        <th>Does head of this household hire the services of other workers?</th>
                        <th>Type of worker</th>
                        <th>Number of workers on your farm last season?</th>
                        <th>On the average, how much did the farmer spend on the farm last year?</th>
                        <th>In what form are workers paid?</th>
                        <th> If other: Please specify the answer to the previous question.\n</th>
                        <th> What is the average worker wage per year?\n</th>
                        <th> What is the frequency of the workers\\' wages?\n</th>
                        <th> Do workers have a workers contract with the farm? (either with the sharecropper of the farm owner)\n</th>
                        <th>What is the main construction material used for the building wall? \n</th>
                        <th>What type of toilet facility is usually used by the household?\n</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
    
          </div>
        </div>
        <!-- END Dynamic Table Full Pagination -->
      <div class="modal fade" id="exportModal" tabindex="-1" role="dialog" aria-labelledby="exportModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exportModalLabel">Export Options</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="exportForm">
            <div class="form-group">
                <input type="radio" id="exportAll" name="exportOption" value="all" checked>
                <label for="exportAll">Export All Tables</label><br>
                <input type="radio" id="exportSelected" name="exportOption" value="selected" disabled>
                <label for="exportSelected">Export Filtered Data</label>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="exportBtn">Export</button>
      </div>
    </div>
  </div>
</div>
      <!-- END Page Content -->
    </main>
    <!-- END Main Container -->

    <!-- Footer -->
    <?php include('../include/footer.php'); ?>
    <!-- END Footer -->
  </div>
  <!-- END Page Container -->

  <script>
    $(document).ready(function () {
      $('#example').DataTable().off('search.dt order.dt'); // Disable event logging
    });
  </script>

<script>
$(document).ready(function () {
    function loadTable() {
        if ($.fn.DataTable.isDataTable('#example')) {
            $('#example').DataTable().destroy();
        }

        let table = $('#example').DataTable({
            "ajax": {
                "url": "fetch_student_data.php",
                "type": "GET",
                "dataSrc": ""
            },
            "columns": [
                { "data": "id" },
                { "data": "user_id" },
                { "data": "hh_district" },
                { "data": "hh_community" },
                { "data": "hhquestion4" },
                { "data": "hhquestion5" },
                { "data": "hhquestion6" },
                { "data": "hhquestion7" },
                { "data": "hhquestion8" },
                { "data": "hhquestion9" },
                { "data": "hhquestion10" }
            ],
            "destroy": true
        });

        // Filter by Class
        $('#communityFilter').on('change', function () {
            let selectedDistricts = $(this).val();
            table.column(2).search(selectedDistricts ? selectedDistricts.join('|') : '', true, false).draw();
        });

        // Filter by Name
        $('#farmerFilter').on('keyup', function () {
            table.column(38).search(this.value).draw();
        });

        // Filter by Date Range
        $('#startDateFilter, #endDateFilter').on('change', function () {
            let startDate = $('#startDateFilter').val();
            let endDate = $('#endDateFilter').val();

            table.draw();
        });

        // Custom filtering for date range
        $.fn.dataTable.ext.search.push(function (settings, data) {
            let min = $('#startDateFilter').val();
            let max = $('#endDateFilter').val();
            let date = data[41] || ""; // Assuming hhquestion5 (date) is in column 5

            if ((min === "" && max === "") || 
                (min === "" && date <= max) || 
                (min <= date && max === "") || 
                (min <= date && date <= max)) {
                return true;
            }
            return false;
        });
    }

    loadTable();

    // Export CSV
    $('#exportBtn').on('click', function () {
        let exportType = $("input[name='exportOption']:checked").val();
        let csvData = [];

        if (exportType === "all") {
            window.location.href = "export_household_data.php"; // Export All Data
        } else {
            let filteredData = $('#example').DataTable().rows({ search: 'applied' }).data();
            filteredData.each(function (row) {
                csvData.push(row);
            });
            exportCSV(csvData);
        }
    });

    function exportCSV(data) {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "ID, Name, District, Community, Question 4, Question 5\n"; // Headers

        data.forEach(row => {
            csvContent += `${row.id}, ${row.user_id}, ${row.hh_district}, ${row.hh_community}, ${row.hhquestion4}, ${row.hhquestion5}\n`;
        });

        let encodedUri = encodeURI(csvContent);
        let link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "filtered_data.csv");
        document.body.appendChild(link);
        link.click();
    }
});

    </script>

  <script src="../assets/js/dashmix.app.min.js"></script>

  <!-- jQuery (required for DataTables plugin) -->
  <script src="../assets/js/lib/jquery.min.js"></script>

  <!-- Page JS Plugins -->
  <script src="../assets/js/plugins/datatables/jquery.dataTables.min.js"></script>
  <script src="../assets/js/plugins/datatables-bs5/js/dataTables.bootstrap5.min.js"></script>
  <script src="../assets/js/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="../assets/js/plugins/datatables-responsive-bs5/js/responsive.bootstrap5.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons/dataTables.buttons.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons-bs5/js/buttons.bootstrap5.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons-jszip/jszip.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons-pdfmake/pdfmake.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons-pdfmake/vfs_fonts.js"></script>
  <script src="../assets/js/plugins/datatables-buttons/buttons.print.min.js"></script>
  <script src="../assets/js/plugins/datatables-buttons/buttons.html5.min.js"></script>

  <!-- Page JS Plugins -->
  <script src="../assets/js/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="../assets/js/plugins/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
    <script src="../assets/js/plugins/select2/js/select2.full.min.js"></script>
    <script src="../assets/js/plugins/ion-rangeslider/js/ion.rangeSlider.min.js"></script>
    <script src="../assets/js/plugins/jquery.maskedinput/jquery.maskedinput.min.js"></script>
    <script src="../assets/js/plugins/dropzone/min/dropzone.min.js"></script>
    <script src="../assets/js/plugins/pwstrength-bootstrap/pwstrength-bootstrap.min.js"></script>
    <script src="../assets/js/plugins/flatpickr/flatpickr.min.js"></script>
    
    <script>
        function eventFired(type) {
    let n = document.querySelector('#demo_info');
    n.innerHTML +=
        '<div>' + type + ' event - ' + new Date().getTime() + '</div>';
    n.scrollTop = n.scrollHeight;
}
 
new DataTable('#example')
    .on('order.dt', () => eventFired('Order'))
    .on('search.dt', () => eventFired('Search'))
    .on('page.dt', () => eventFired('Page'));
    </script>

    <!-- Page JS Helpers (Flatpickr + BS Datepicker + BS Maxlength + Select2 + Ion Range Slider + Masked Inputs + Password Strength Meter plugins) -->
    <script>Dashmix.helpersOnLoad(['js-flatpickr', 'jq-datepicker', 'jq-maxlength', 'jq-select2', 'jq-rangeslider', 'jq-masked-inputs', 'jq-pw-strength']);</script>

  <!-- Page JS Code -->
  <script src="../assets/js/pages/be_tables_datatables.min.js"></script>

  <script>
    $(document).ready(function() {
    $('#communityFilter').select2({
        placeholder: "Select Class"
    });
    
    var table = $('#farmerTable').DataTable({
        "pageLength": 10,
        "lengthMenu": [5, 10, 15, 20],
        "ordering": true,
        "searching": true
    });

    // Disable Export Filtered Data (initially)
    $('#exportSelected').prop('disabled', true);

    // Check if filters are applied
    function checkFilters() {
        var communityFilter = $('#communityFilter').val();
        var farmerFilter = $('#farmerFilter').val();
        var startDate = $('#startDateFilter').val();
        var endDate = $('#endDateFilter').val();

        if (communityFilter.length > 0 || farmerFilter || (startDate && endDate)) {
            $('#exportSelected').prop('disabled', false); // Enable ExportSelected
        } else {
            $('#exportSelected').prop('disabled', true); // Disable ExportSelected
        }
    }

    // Multi-select filter for Community
    $('#communityFilter').on('change', function() {
        var selectedCommunitys = $(this).val();
        checkFilters(); // Check if any filters are applied

        if (selectedCommunitys.length === 0) {
            table.column(4).search('').draw(); // Clear the filter if no community selected
        } else {
            var communityRegex = selectedCommunitys.join('|');
            table.column(4).search(communityRegex, true, false).draw();
        }
    });

    // Farmer Name filter
    $('#farmerFilter').on('keyup', function() {
        checkFilters(); // Check if any filters are applied
        table.column(2).search(this.value).draw();
    });

    // Start and End Date filter with reset when cleared
    $('#startDateFilter, #endDateFilter').on('change', function() {
        var startDate = $('#startDateFilter').val();
        var endDate = $('#endDateFilter').val();
        var currentDate = new Date().toISOString().split("T")[0];

        // If the end date exceeds the current date
        if (endDate && (new Date(endDate) > new Date(currentDate))) {
            Swal.fire('Error', 'End date cannot exceed the current date.', 'error');
            $('#endDateFilter').val(''); // Clear the end date if it exceeds the current date
            return;
        }

        // If the start date is greater than the end date
        if (startDate && endDate && new Date(startDate) > new Date(endDate)) {
            Swal.fire('Error', 'End date cannot be earlier than start date.', 'error');
            $('#endDateFilter').val(''); // Clear the end date if invalid
            return;
        }

        // Check filters and enable or disable 'exportSelected'
        checkFilters();

        // Clear date filter when both dates are empty (reset to default)
        if (!startDate && !endDate) {
            $.fn.dataTable.ext.search.pop();  // Remove the last search function (date filter)
            table.draw(); // Redraw the table with no date filter
            return;
        }

        // Apply date filtering only when the end date is selected
        if (endDate) {
            // Remove any previous date filters
            $.fn.dataTable.ext.search.pop();

            // Apply new date filter
            $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
                var timestamp = data[5].split(" ")[0]; // Extract date part from the timestamp
                return (startDate === "" || timestamp >= startDate) && timestamp <= endDate;
            });

            table.draw(); // Redraw the table with the new date filter
        }
    });

    // Export functionality using SheetJS based on selection
    $('#exportBtn').on('click', function() {
        var exportOption = $('input[name="exportOption"]:checked').val();

        if (exportOption === 'all') {
            // Export All data from database (from server-side)
            $.ajax({
                url: 'inspection_twifo_praso_endpoint.php',
                method: 'POST',
                data: { exportAll: true },
                success: function(response) {
                    var wb = XLSX.utils.book_new();
                    var ws = XLSX.utils.json_to_sheet(response);
                    XLSX.utils.book_append_sheet(wb, ws, 'All Farmers Data');
                    XLSX.writeFile(wb, 'all_farmers_data.xlsx');
                    Swal.fire('Success', 'All data exported successfully!', 'success');
                },
                error: function(xhr, status, error) {
                    Swal.fire('Error', 'Could not export all data.', 'error');
                }
            });
        } else if (exportOption === 'selected') {
            // Export Filtered data (from table)
            var filteredData = [];
            table.rows({ filter: 'applied' }).every(function(rowIdx) {
                filteredData.push(table.row(rowIdx).data());
            });

            if (filteredData.length === 0) {
                Swal.fire('Error', 'No filtered data to export.', 'error');
            } else {
                var wb = XLSX.utils.book_new();
                var ws = XLSX.utils.json_to_sheet(filteredData);
                XLSX.utils.book_append_sheet(wb, ws, 'Filtered Farmers Data');
                XLSX.writeFile(wb, 'filtered_farmers_data.xlsx');
                Swal.fire('Success', 'Filtered data exported successfully!', 'success');
            }
        }
    });
});
</script>

</body>

</html>