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

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>Daddy - Inbox</title>

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
  <!-- Dashmix framework -->
  <link rel="stylesheet" id="css-main" href="../assets/css/dashmix.min.css">

  <!-- END Stylesheets -->
</head>

<body>
  
  <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-fixed">

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
      <div class="row g-0 flex-md-grow-1">
        <div class="col-md-4 col-lg-5 col-xl-3">
          <div class="content">
            <!-- Toggle Side Content -->
            <div class="d-md-none push">
              <!-- Class Toggle, functionality initialized in Helpers.dmToggleClass() -->
              <button type="button" class="btn w-100 btn-alt-primary" data-toggle="class-toggle"
                data-target="#side-content" data-class="d-none">
                Inbox Menu
              </button>
            </div>
            <!-- END Toggle Side Content -->

            <!-- Side Content -->
            <div id="side-content" class="d-none d-md-block push">
              <!-- New Message -->
              <button type="button" class="btn w-100 btn-alt-primary mb-3">
                <i class="fa fa-plus opacity-50 me-1"></i> New Message
              </button>
              <!-- END New Message -->

              <!-- Search Messages -->
              <form action="../be_pages_generic_inbox.html" method="POST" onsubmit="return false;">
                <div class="mb-4">
                  <div class="input-group">
                    <input type="text" class="form-control border-0" placeholder="Search Messages..">
                    <span class="input-group-text border-0 bg-body-extra-light">
                      <i class="fa fa-fw fa-search"></i>
                    </span>
                  </div>
                </div>
              </form>
              <!-- END Search Messages -->

              <!-- Sorting/Filtering -->
              <div class="d-flex justify-content-between mb-2">
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-link fw-semibold dropdown-toggle" id="inbox-msg-sort"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Sort by
                  </button>
                  <div class="dropdown-menu fs-sm" aria-labelledby="inbox-msg-sort">
                    <a class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-fw fa-sort-amount-down me-1"></i> Newest
                    </a>
                    <a class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-fw fa-sort-amount-up me-1"></i> Oldest
                    </a>
                  </div>
                </div>
                <div class="dropdown">
                  <button type="button" class="btn btn-sm btn-link fw-semibold dropdown-toggle" id="inbox-msg-filter"
                    data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Filter by
                  </button>
                  <div class="dropdown-menu dropdown-menu-end fs-sm" aria-labelledby="inbox-msg-filter">
                    <a class="dropdown-item active" href="javascript:void(0)">
                      <i class="fa fa-fw fa-asterisk me-1"></i> New
                    </a>
                    <a class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-fw fa-archive me-1"></i> Archived
                    </a>
                    <a class="dropdown-item" href="javascript:void(0)">
                      <i class="fa fa-fw fa-times-circle me-1"></i> Deleted
                    </a>
                  </div>
                </div>
              </div>
              <!-- END Sorting/Filtering -->

              <!-- Messages -->
              <div class="list-group fs-sm">
                <a class="list-group-item list-group-item-action" href="javascript:void(0)">
                  <span class="badge rounded-pill bg-dark m-1 float-end">1</span>
                  <p class="fs-6 fw-bold mb-0">
                    Welcome
                  </p>
                  <p class="text-muted mb-2">
                    Welcome to..
                  </p>
                  <p class="fs-sm text-muted mb-0">
                    <strong>FruitofTek</strong>, 3 min ago
                  </p>
                </a>
              </div>
              <!-- END Messages -->
            </div>
            <!-- END Side Content -->
          </div>
        </div>
        <div class="col-md-8 col-lg-7 col-xl-9 bg-body-dark">
          <!-- Main Content -->
          <div class="content">
            <!-- Message -->
            <div class="block block-rounded">
              <div class="block-content block-content-sm block-content-full bg-body-light">
                <div class="d-flex py-3">
                  <div class="flex-shrink-0 me-3 ms-2 overlay-container overlay-right">
                    <img class="img-avatar img-avatar48" src="../assets/media/avatars/avatar2.jpg" alt="">
                    <i class="far fa-circle text-white overlay-item fs-sm bg-success rounded-circle"></i>
                  </div>
                  <div class="flex-grow-1">
                    <div class="row">
                      <div class="col-sm-7">
                        <a class="fw-semibold link-fx" href="../be_pages_generic_profile.html">Sara Fields</a>
                        <div class="fs-sm text-muted">s.fields@example.com</div>
                      </div>
                      <div class="col-sm-5 d-sm-flex align-items-sm-center">
                        <div class="fs-sm text-muted text-sm-end w-100 mt-2 mt-sm-0">
                          <p class="mb-0">March 15, 2018</p>
                          <p class="mb-0">14:25</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="block-content">
                <p>Dear John,</p>
                <p>Dolor posuere proin blandit accumsan senectus netus nullam curae, ornare laoreet adipiscing luctus
                  mauris adipiscing pretium eget fermentum, tristique lobortis est ut metus lobortis tortor tincidunt
                  himenaeos habitant quis dictumst proin odio sagittis purus mi, nec taciti vestibulum quis in sit
                  varius lorem sit metus mi.</p>
                <p>Best Regards,</p>
                <p>Sara</p>
              </div>
              <div class="block-content bg-body-light">
                <div class="row g-sm">
                  <div class="col-6 col-sm-4 col-md-5 col-lg-4 col-xl-3">
                    <div class="options-container fx-item-zoom-in">
                      <img class="img-fluid options-item" src="../assets/media/photos/photo7.jpg" alt="">
                      <div class="options-overlay bg-black-75">
                        <div class="options-overlay-content">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i> Download
                          </a>
                        </div>
                      </div>
                    </div>
                    <p class="fs-sm text-muted pt-2">
                      <i class="fa fa-paperclip"></i> Mountain.jpg (785Kb)
                    </p>
                  </div>
                  <div class="col-6 col-sm-4 col-md-5 col-lg-4 col-xl-3">
                    <div class="options-container fx-item-zoom-in">
                      <img class="img-fluid options-item" src="../assets/media/photos/photo8.jpg" alt="">
                      <div class="options-overlay bg-black-75">
                        <div class="options-overlay-content">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i> Download
                          </a>
                        </div>
                      </div>
                    </div>
                    <p class="fs-sm text-muted pt-2">
                      <i class="fa fa-paperclip"></i> Fireplace.jpg (685kb)
                    </p>
                  </div>
                </div>
              </div>
            </div>
            <!-- END Message -->

            <!-- Reply -->
            <div class="block block-rounded">
              <div class="block-content block-content-full">
                <!-- CKEditor 5 Classic (js-ckeditor5-classic in Helpers.jsCkeditor5()) -->
                <!-- For more info and examples you can check out http://ckeditor.com -->
                <!-- CKEditor 5 Classic Container -->
                <div id="js-ckeditor5-classic"></div>
                <button type="button" class="btn btn-alt-primary mt-2">Send Message</button>
              </div>
            </div>
            <!-- END Reply -->
          </div>
          <!-- END Main Content -->
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

  <!--
      Dashmix JS

      Core libraries and functionality
      webpack is putting everything together at assets/_js/main/app.js
    -->
  <script src="../assets/js/dashmix.app.min.js"></script>

  <!-- Page JS Plugins -->
  <script src="../assets/js/plugins/ckeditor5-classic/build/ckeditor.js"></script>

  <!-- Page JS Helpers (CKEditor 5 plugins) -->
  <script>Dashmix.helpersOnLoad(['js-ckeditor5']);</script>
  <script type="text/javascript">
    $(".alert").delay(4000).slideUp(500, function() {
        $(this).alert('close');
    });
  </script>
</body>

</html>