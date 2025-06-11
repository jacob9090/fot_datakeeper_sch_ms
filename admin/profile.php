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

  <title>Daddy - Profile</title>

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
      <div class="content content-full content-boxed">
        <!-- Hero -->
        <div class="rounded border overflow-hidden push">
          <div class="bg-image pt-9" style="background-image: url('../assets/media/photos/photo19@2x.jpg');"></div>
          <div class="px-4 py-3 bg-body-extra-light d-flex flex-column flex-md-row align-items-center">
            <a class="d-block img-link mt-n5" href="../be_pages_generic_profile_v2.html">
              <img class="img-avatar img-avatar128 img-avatar-thumb" src="../assets/media/avatars/avatar13.jpg" alt="">
            </a>
            <div class="ms-3 flex-grow-1 text-center text-md-start my-3 my-md-0">
              <h1 class="fs-4 fw-bold mb-1">John Smith</h1>
              <h2 class="fs-sm fw-medium text-muted mb-0">
                Edit Account
              </h2>
            </div>
            <div class="space-x-1">
              <a href="school-overview.php" class="btn btn-sm btn-alt-secondary space-x-1">
                <i class="fa fa-arrow-left opacity-50"></i>
                <span>Back to Dashboard</span>
              </a>
            </div>
          </div>
        </div>
        <!-- END Hero -->

        <!-- Edit Account -->
        <div class="block block-bordered block-rounded">
          <ul class="nav nav-tabs nav-tabs-alt" role="tablist">
            <li class="nav-item">
              <button class="nav-link space-x-1 active" id="account-profile-tab" data-bs-toggle="tab"
                data-bs-target="#account-profile" role="tab" aria-controls="account-profile" aria-selected="true">
                <i class="fa fa-user-circle d-sm-none"></i>
                <span class="d-none d-sm-inline">Profile</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link space-x-1" id="account-password-tab" data-bs-toggle="tab"
                data-bs-target="#account-password" role="tab" aria-controls="account-password" aria-selected="false">
                <i class="fa fa-asterisk d-sm-none"></i>
                <span class="d-none d-sm-inline">Password</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link space-x-1" id="account-connections-tab" data-bs-toggle="tab"
                data-bs-target="#account-connections" role="tab" aria-controls="account-connections"
                aria-selected="false">
                <i class="fa fa-share-alt d-sm-none"></i>
                <span class="d-none d-sm-inline">Connections</span>
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link space-x-1" id="account-billing-tab" data-bs-toggle="tab"
                data-bs-target="#account-billing" role="tab" aria-controls="account-billing" aria-selected="false">
                <i class="fa fa-credit-card d-sm-none"></i>
                <span class="d-none d-sm-inline">Billing</span>
              </button>
            </li>
          </ul>
          <div class="block-content tab-content">
            <div class="tab-pane active" id="account-profile" role="tabpanel" aria-labelledby="account-profile-tab"
              tabindex="0">
              <div class="row push p-sm-2 p-lg-4">
                <div class="offset-xl-1 col-xl-4 order-xl-1">
                  <p class="bg-body-light p-4 rounded-3 text-muted fs-sm">
                    Your account’s vital info. Your username will be publicly visible.
                  </p>
                </div>
                <div class="col-xl-6 order-xl-0">
                  <form action="../be_pages_generic_profile_v2_edit.html" method="POST" enctype="multipart/form-data"
                    onsubmit="return false;">
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-username">Username</label>
                      <input type="text" class="form-control" id="dm-profile-edit-username"
                        name="dm-profile-edit-username" placeholder="Enter your username.." value="john.doe">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-name">Name</label>
                      <input type="text" class="form-control" id="dm-profile-edit-name" name="dm-profile-edit-name"
                        placeholder="Enter your name.." value="John Doe">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-email">Email Address</label>
                      <input type="email" class="form-control" id="dm-profile-edit-email" name="dm-profile-edit-email"
                        placeholder="Enter your email.." value="john.doe@example.com">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-job-title">Job Title</label>
                      <input type="text" class="form-control" id="dm-profile-edit-job-title"
                        name="dm-profile-edit-job-title" placeholder="Add your job title.." value="Product Manager">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-company">Company</label>
                      <input type="text" class="form-control" id="dm-profile-edit-company"
                        name="dm-profile-edit-company" value="@ProXdesign" readonly>
                    </div>
                    <div class="mb-4">
                      <label class="form-label">Your Avatar</label>
                      <div class="push">
                        <img class="img-avatar" src="../assets/media/avatars/avatar13.jpg" alt="">
                      </div>
                      <label class="form-label" for="dm-profile-edit-avatar">Choose a new avatar</label>
                      <input class="form-control" type="file" id="dm-profile-edit-avatar">
                    </div>
                    <button type="submit" class="btn btn-alt-primary">
                      <i class="fa fa-check-circle opacity-50 me-1"></i> Update Profile
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="account-password" role="tabpanel" aria-labelledby="account-password-tab"
              tabindex="0">
              <div class="row push p-sm-2 p-lg-4">
                <div class="offset-xl-1 col-xl-4 order-xl-1">
                  <p class="bg-body-light p-4 rounded-3 text-muted fs-sm">
                    Changing your sign in password is an easy way to keep your account secure.
                  </p>
                </div>
                <div class="col-xl-6 order-xl-0">
                  <form action="../be_pages_generic_profile_v2_edit.html" method="POST" onsubmit="return false;">
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-password">Current Password</label>
                      <input type="password" class="form-control" id="dm-profile-edit-password"
                        name="dm-profile-edit-password">
                    </div>
                    <div class="row mb-4">
                      <div class="col-12">
                        <label class="form-label" for="dm-profile-edit-password-new">New Password</label>
                        <input type="password" class="form-control" id="dm-profile-edit-password-new"
                          name="dm-profile-edit-password-new">
                      </div>
                    </div>
                    <div class="row mb-4">
                      <div class="col-12">
                        <label class="form-label" for="dm-profile-edit-password-new-confirm">Confirm New
                          Password</label>
                        <input type="password" class="form-control" id="dm-profile-edit-password-new-confirm"
                          name="dm-profile-edit-password-new-confirm">
                      </div>
                    </div>
                    <button type="submit" class="btn btn-alt-primary">
                      <i class="fa fa-check-circle opacity-50 me-1"></i> Update Password
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="account-connections" role="tabpanel" aria-labelledby="account-connections-tab"
              tabindex="0">
              <div class="row push p-sm-2 p-lg-4">
                <div class="offset-xl-1 col-xl-4 order-xl-1">
                  <p class="bg-body-light p-4 rounded-3 text-muted fs-sm">
                    You can connect your account to third party networks to get extra features.
                  </p>
                </div>
                <div class="col-xl-6 order-xl-0">
                  <form action="../be_pages_generic_profile_v2_edit.html" method="POST" onsubmit="return false;">
                    <div class="row mb-4">
                      <div class="col-sm-10 col-lg-8">
                        <a class="btn w-100 btn-alt-danger text-start" href="javascript:void(0)">
                          <i class="fab fa-fw fa-google opacity-50 me-1"></i> Connect to Google
                        </a>
                      </div>
                    </div>
                    <div class="row mb-4">
                      <div class="col-sm-10 col-lg-8">
                        <a class="btn w-100 btn-alt-info text-start" href="javascript:void(0)">
                          <i class="fab fa-fw fa-twitter opacity-50 me-1"></i> Connect to Twitter
                        </a>
                      </div>
                    </div>
                    <div class="row mb-4">
                      <div class="col-sm-10 col-lg-8">
                        <a class="btn w-100 btn-alt-primary bg-white d-flex align-items-center justify-content-between"
                          href="javascript:void(0)">
                          <span>
                            <i class="fab fa-fw fa-facebook me-1"></i> John Doe
                          </span>
                          <i class="fa fa-fw fa-check me-1"></i>
                        </a>
                      </div>
                      <div class="mt-2">
                        <a class="btn btn-sm btn-alt-secondary rounded-pill" href="javascript:void(0)">
                          <i class="fa fa-fw fa-pencil-alt opacity-50 me-1"></i> Edit Facebook Connection
                        </a>
                      </div>
                    </div>
                    <div class="row mb-4">
                      <div class="col-sm-10 col-lg-8">
                        <a class="btn w-100 btn-alt-warning bg-white d-flex align-items-center justify-content-between"
                          href="javascript:void(0)">
                          <span>
                            <i class="fab fa-fw fa-instagram me-1"></i> @john_doe
                          </span>
                          <i class="fa fa-fw fa-check me-1"></i>
                        </a>
                      </div>
                      <div class="mt-2">
                        <a class="btn btn-sm btn-alt-secondary rounded-pill" href="javascript:void(0)">
                          <i class="fa fa-fw fa-pencil-alt opacity-50 me-1"></i> Edit Instagram Connection
                        </a>
                      </div>
                    </div>
                    <button type="submit" class="btn btn-alt-primary">
                      <i class="fa fa-check-circle opacity-50 me-1"></i> Update Connections
                    </button>
                  </form>
                </div>
              </div>
            </div>
            <div class="tab-pane" id="account-billing" role="tabpanel" aria-labelledby="account-billing-tab"
              tabindex="0">
              <div class="row push p-sm-2 p-lg-4">
                <div class="offset-xl-1 col-xl-4 order-xl-1">
                  <p class="bg-body-light p-4 rounded-3 text-muted fs-sm">
                    Your billing information is never shown to other users and only used for creating your invoices.
                  </p>
                </div>
                <div class="col-xl-6 order-xl-0">
                  <form action="../be_pages_generic_profile_v2_edit.html" method="POST" onsubmit="return false;">
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-company-name">Company Name (Optional)</label>
                      <input type="text" class="form-control" id="dm-profile-edit-company-name"
                        name="dm-profile-edit-company-name">
                    </div>
                    <div class="row mb-4">
                      <div class="col-6">
                        <label class="form-label" for="dm-profile-edit-firstname">Firstname</label>
                        <input type="text" class="form-control" id="dm-profile-edit-firstname"
                          name="dm-profile-edit-firstname">
                      </div>
                      <div class="col-6">
                        <label class="form-label" for="dm-profile-edit-lastname">Lastname</label>
                        <input type="text" class="form-control" id="dm-profile-edit-lastname"
                          name="dm-profile-edit-lastname">
                      </div>
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-street-1">Street Address 1</label>
                      <input type="text" class="form-control" id="dm-profile-edit-street-1"
                        name="dm-profile-edit-street-1">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-street-2">Street Address 2</label>
                      <input type="text" class="form-control" id="dm-profile-edit-street-2"
                        name="dm-profile-edit-street-2">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-city">City</label>
                      <input type="text" class="form-control" id="dm-profile-edit-city" name="dm-profile-edit-city">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-postal">Postal code</label>
                      <input type="text" class="form-control" id="dm-profile-edit-postal" name="dm-profile-edit-postal">
                    </div>
                    <div class="mb-4">
                      <label class="form-label" for="dm-profile-edit-vat">VAT Number</label>
                      <input type="text" class="form-control" id="dm-profile-edit-vat" name="dm-profile-edit-vat"
                        value="EA00000000" disabled>
                    </div>
                    <button type="submit" class="btn btn-alt-primary">
                      <i class="fa fa-check-circle opacity-50 me-1"></i> Update Billing
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- END Edit Account -->
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
  <script type="text/javascript">
    $(".alert").delay(4000).slideUp(500, function() {
        $(this).alert('close');
    });
  </script>
</body>

</html>