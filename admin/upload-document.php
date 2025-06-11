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

    <title>FEDCO - Upload Document</title>

    <meta name="description" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave and published on Themeforest">
    <meta name="author" content="pixelcave">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework">
    <meta property="og:site_name" content="Dashmix">
    <meta property="og:description" content="Dashmix - Bootstrap 5 Admin Template &amp; UI Framework created by pixelcave and published on Themeforest">
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
    <link rel="stylesheet" id="css-main" href="../assets/css/dashmix.min.css">
    <!-- END Stylesheets -->
  </head>

  <body>
    
    <div id="page-container" class="sidebar-o enable-page-overlay side-scroll page-header-fixed main-content-narrow">

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
              <span class="smini-visible">FEDCO</span>
              <span class="smini-hidden">FEDCO</span>
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
                  <a class="nav-main-link" href="sustainability-overview.php">
                    <span class="nav-main-link-name">Sustainability</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="commdev-overview.php">
                    <span class="nav-main-link-name">CommDev</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="traceability-overview.php">
                    <span class="nav-main-link-name">Traceability</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="environmental-overview.php">
                    <span class="nav-main-link-name">Environmental</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-heading">Surveys</li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-tree"></i>
                <span class="nav-main-link-name">Sustainability</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="farmer-profiling.php">
                    <span class="nav-main-link-name">Farmer Profiling</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="inspection-survey.php">
                    <span class="nav-main-link-name">Inspection Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="farmer-training.php">
                    <span class="nav-main-link-name">Farmer Training</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="mapping.php">
                    <span class="nav-main-link-name">Mapping</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-users"></i>
                <span class="nav-main-link-name">CommDev</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="community-survey.php">
                    <span class="nav-main-link-name">Comm. Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="school-survey.php">
                    <span class="nav-main-link-name">School Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="household-survey.php">
                    <span class="nav-main-link-name">Household Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="observation-survey.php">
                    <span class="nav-main-link-name">Observation</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="child-curvey.php">
                    <span class="nav-main-link-name">Child Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="field-activites.php">
                    <span class="nav-main-link-name">Field Activites</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-location"></i>
                <span class="nav-main-link-name">Traceability</span>
              </a>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-city"></i>
                <span class="nav-main-link-name">Environmental</span>
              </a>
            </li>
            <li class="nav-main-heading">Management</li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-users"></i>
                <span class="nav-main-link-name">User</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-user.php">
                    <span class="nav-main-link-name">Add User</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-user.php">
                    <span class="nav-main-link-name">Manage User</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
                <i class="nav-main-link-icon fa fa-users-between-lines"></i>
                <span class="nav-main-link-name">Farmer</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="add-farmer.php">
                    <span class="nav-main-link-name">Add Farmer</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="manage-farmer.php">
                    <span class="nav-main-link-name">Manager Farmer</span>
                  </a>
                </li>
              </ul>
            </li>
            <li class="nav-main-heading">Upload Records</li>
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
                <i class="nav-main-link-icon fa fa-upload"></i>
                <span class="nav-main-link-name">Upload</span>
              </a>
              <ul class="nav-main-submenu">
                <li class="nav-main-item">
                  <a class="nav-main-link" href="upload-registry.php">
                    <span class="nav-main-link-name">Upload Registry</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link active" href="upload-document.php">
                    <span class="nav-main-link-name">Upload Document</span>
                  </a> 
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="upload-image.php">
                    <span class="nav-main-link-name">Upload Image</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="upload-polygon.php">
                    <span class="nav-main-link-name">Upload Polygon</span>
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
                  <a class="nav-main-link" href="farmer-profiling-archive.php">
                    <span class="nav-main-link-name">Profiling</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="inspection-survey-archive.php">
                    <span class="nav-main-link-name">Inspection</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="farmer-training-archive.php">
                    <span class="nav-main-link-name">Training</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="community-survey-archive.php">
                    <span class="nav-main-link-name">Com. Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="school-survey-archive.php">
                    <span class="nav-main-link-name">School Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="household-survey-archive.php">
                    <span class="nav-main-link-name">Household Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="observation-survey-archive.php">
                    <span class="nav-main-link-name">Observation</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="child-curvey.php-archive">
                    <span class="nav-main-link-name">Child Survey</span>
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
          <div class="col-md-4 col-lg-5 col-xl-3 order-md-1">
            <div class="content">
              <!-- Toggle Storage Info -->
              <div class="d-md-none push">
                <!-- Class Toggle, functionality initialized in Helpers.dmToggleClass() -->
                <button type="button" class="btn w-100 btn-alt-primary" data-toggle="class-toggle" data-target="#side-content" data-class="d-none">
                  Storage Info
                </button>
              </div>
              <!-- END Toggle Storage Info -->

              <!-- Storage Info -->
              <div id="side-content" class="d-none d-md-block push">
                <!-- Current Status -->
                <div class="block block-rounded">
                  <div class="block-content">
                    <h4 class="h5 text-muted mb-2">Storage</h4>
                    <p class="h1 fw-bold mb-1">
                      920GB
                      <span class="fs-sm text-muted">of 1TB</span>
                    </p>
                    <div class="progress push" style="height: 5px;">
                      <div class="progress-bar bg-danger" role="progressbar" style="width: 92%;" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <p class="fs-sm text-muted">
                      <a href="javascript:void(0)">Upgrade Plan?</a>
                    </p>
                  </div>
                </div>
                <!-- END Current Status -->

                <!-- Storage Stats -->
                <div class="block block-rounded mb-2">
                  <div class="block-content border-bottom">
                    <h4 class="h5 text-muted mb-2">Documents</h4>
                    <p class="h2 fw-bold mb-1">
                      80GB
                      <span class="fs-sm text-muted">of 1TB</span>
                    </p>
                    <div class="progress push" style="height: 5px;">
                      <div class="progress-bar bg-success" role="progressbar" style="width: 8%;" aria-valuenow="8" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <div class="block-content border-bottom">
                    <h4 class="h5 text-muted mb-2">Photos</h4>
                    <p class="h2 fw-bold mb-1">
                      40GB
                      <span class="fs-sm text-muted">of 1TB</span>
                    </p>
                    <div class="progress push" style="height: 5px;">
                      <div class="progress-bar bg-success" role="progressbar" style="width: 4%;" aria-valuenow="4" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <div class="block-content border-bottom">
                    <h4 class="h5 text-muted mb-2">Videos</h4>
                    <p class="h2 fw-bold mb-1">
                      780GB
                      <span class="fs-sm text-muted">of 1TB</span>
                    </p>
                    <div class="progress push" style="height: 5px;">
                      <div class="progress-bar bg-warning" role="progressbar" style="width: 78%;" aria-valuenow="78" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                  <div class="block-content">
                    <h4 class="h5 text-muted mb-2">Audio</h4>
                    <p class="h2 fw-bold mb-1">
                      20GB
                      <span class="fs-sm text-muted">of 1TB</span>
                    </p>
                    <div class="progress push" style="height: 5px;">
                      <div class="progress-bar bg-success" role="progressbar" style="width: 2%;" aria-valuenow="2" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                  </div>
                </div>
                <!-- END Storage Stats -->
              </div>
              <!-- END Storage Status -->
            </div>
          </div>
          <div class="col-md-8 col-lg-7 col-xl-9 order-md-0 bg-body-dark">
            <div class="content">
              <!-- Breadcrumb -->
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb breadcrumb-alt bg-body-extra-light px-4 py-2 rounded push fs-sm">
                  <li class="breadcrumb-item">
                    <a href="javascript:void(0)">
                      <i class="fa fa-home"></i>
                    </a>
                  </li>
                  <li class="breadcrumb-item" aria-current="page">
                    <div class="dropdown d-inline">
                      <a href="javascript:void(0)" id="example-dropdown-folder" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Work <i class="fa fa-angle-down opacity-50 ms-1"></i>
                      </a>
                      <div class="dropdown-menu fs-sm" aria-labelledby="example-dropdown-folder">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> Personal
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> Travel
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> Meeting
                        </a>
                      </div>
                    </div>
                  </li>
                  <li class="breadcrumb-item active" aria-current="page">
                    <div class="dropdown d-inline">
                      <a href="javascript:void(0)" id="example-dropdown-folder-2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Project X <i class="fa fa-angle-down opacity-50 ms-1"></i>
                      </a>
                      <div class="dropdown-menu fs-sm" aria-labelledby="example-dropdown-folder-2">
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> WordPress Theme
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> Admin Template
                        </a>
                        <a class="dropdown-item" href="javascript:void(0)">
                          <i class="fa fa-folder me-1"></i> Web App
                        </a>
                      </div>
                    </div>
                  </li>
                </ol>
              </nav>
              <!-- END Breadcrumb -->

              <!-- Folders -->
              <h2 class="content-heading border-black-op">
                <i class="far fa-fw fa-folder me-1"></i> Folders (4)
              </h2>
              <div class="row items-push">
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Inspiration Folder -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Inspiration Folder Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2">
                          <i class="fa fa-folder fa-4x text-info"></i>
                        </p>
                        <p class="fw-semibold fs-lg mb-0">
                          Inspiration
                        </p>
                        <p class="fs-sm text-muted">
                          14 Files
                        </p>
                      </div>
                    </div>
                    <!-- END Inspiration Folder Block -->

                    <!-- Inspiration Folder Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-share opacity-50 me-1"></i> Open
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Inspiration Folder Hover Options -->
                  </div>
                  <!-- END Inspiration Folder -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Source Code Folder -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Source Code Folder Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2">
                          <i class="fa fa-folder fa-4x text-info"></i>
                        </p>
                        <p class="fw-semibold fs-lg mb-0">
                          Source Code
                        </p>
                        <p class="fs-sm text-muted">
                          25 Files
                        </p>
                      </div>
                    </div>
                    <!-- END Source Code Folder Block -->

                    <!-- Source Code Folder Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-share opacity-50 me-1"></i> Open
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Source Code Folder Hover Options -->
                  </div>
                  <!-- END Source Code Folder -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- PSDs Folder -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- PSDs Folder Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2">
                          <i class="fa fa-folder fa-4x text-info"></i>
                        </p>
                        <p class="fw-semibold fs-lg mb-0">
                          PSDs
                        </p>
                        <p class="fs-sm text-muted">
                          67 Files
                        </p>
                      </div>
                    </div>
                    <!-- END PSDs Folder Block -->

                    <!-- PSDs Folder Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-share opacity-50 me-1"></i> Open
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END PSDs Folder Hover Options -->
                  </div>
                  <!-- END PSDs Folder -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Backup Folder -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Backup Folder Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2">
                          <i class="fa fa-folder fa-4x text-info"></i>
                        </p>
                        <p class="fw-semibold fs-lg mb-0">
                          Backup
                        </p>
                        <p class="fs-sm text-muted">
                          3 Files
                        </p>
                      </div>
                    </div>
                    <!-- END Backup Folder Block -->

                    <!-- Backup Folder Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-share opacity-50 me-1"></i> Open
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Backup Folder Hover Options -->
                  </div>
                  <!-- END Backup Folder -->
                </div>
              </div>
              <!-- END Folders -->

              <!-- Files -->
              <h2 class="content-heading border-black-op">
                <i class="far fa-fw fa-file me-1"></i> Files (7)
              </h2>
              <div class="row items-push">
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <img class="img-fluid" src="../assets/media/photos/photo22.jpg" alt="">
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          background_1.jpg
                        </p>
                        <p class="fs-sm text-muted">
                          0.9mb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <img class="img-fluid" src="../assets/media/photos/photo21.jpg" alt="">
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          background_2.jpg
                        </p>
                        <p class="fs-sm text-muted">
                          3.4mb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <img class="img-fluid" src="../assets/media/photos/photo24.jpg" alt="">
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          background_3.jpg
                        </p>
                        <p class="fs-sm text-muted">
                          2.3mb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <i class="fa fa-fw fa-4x fa-file-alt text-muted"></i>
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          notes.txt
                        </p>
                        <p class="fs-sm text-muted">
                          3kb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <i class="fa fa-fw fa-4x fa-file-excel text-danger"></i>
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          Accounting.xlsx
                        </p>
                        <p class="fs-sm text-muted">
                          33kb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <i class="fa fa-fw fa-4x fa-file-word text-default"></i>
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          Research.docx
                        </p>
                        <p class="fs-sm text-muted">
                          50kb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
                <div class="col-sm-6 col-md-4 col-xl-3 d-flex flex-column">
                  <!-- Example File -->
                  <div class="options-container w-100 flex-grow-1 rounded bg-body d-flex align-items-center">
                    <!-- Example File Block -->
                    <div class="options-item block block-rounded block-transparent mb-0 w-100">
                      <div class="block-content text-center">
                        <p class="mb-2 overflow-hidden">
                          <i class="fa fa-fw fa-4x fa-file-powerpoint text-warning"></i>
                        </p>
                        <p class="fw-semibold text-break mb-0">
                          Presentation.pptx
                        </p>
                        <p class="fs-sm text-muted">
                          4.5mb
                        </p>
                      </div>
                    </div>
                    <!-- END Example File Block -->

                    <!-- Example File Hover Options -->
                    <div class="options-overlay rounded bg-primary-dark-op">
                      <div class="options-overlay-content">
                        <div class="mb-3">
                          <a class="btn btn-primary" href="javascript:void(0)">
                            <i class="fa fa-eye opacity-50 me-1"></i> View
                          </a>
                        </div>
                        <div class="btn-group">
                          <a class="btn btn-sm btn-primary" href="javascript:void(0)">
                            <i class="fa fa-download me-1"></i>
                          </a>
                        </div>
                      </div>
                    </div>
                    <!-- END Example File Hover Options -->
                  </div>
                  <!-- END Example File -->
                </div>
              </div>
              <!-- END Files -->
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

    <!--
      Dashmix JS

      Core libraries and functionality
      webpack is putting everything together at assets/_js/main/app.js
    -->
    <script src="../assets/js/dashmix.app.min.js"></script>
  </body>
</html>
