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

$sql = "SELECT * FROM fedco_users";
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

  <title>FEDCO - Farmer Profiling</title>

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
  <link rel="stylesheet" href="../assets/js/plugins/datatables-bs5/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/datatables-buttons-bs5/css/buttons.bootstrap5.min.css">
  <link rel="stylesheet" href="../assets/js/plugins/datatables-responsive-bs5/css/responsive.bootstrap5.min.css">

  <!-- Stylesheets -->
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
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
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
                  <a class="nav-main-link active" href="manage-user.php">
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
            <li class="nav-main-item">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="false" href="#">
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
                  <a class="nav-main-link" href="upload-document.php">
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
      <div class="content">
        <!-- Dynamic Table Full Pagination -->
        <div class="block block-rounded">
          <div class="block-header block-header-default">
            <h3 class="block-title">
              Active <small>Users</small>
            </h3>
          </div>
          <div class="block-content block-content-full">
            <!-- DataTables init on table by adding .js-dataTable-full-pagination class, functionality is initialized in js/pages/be_tables_datatables.min.js which was auto compiled from _js/pages/be_tables_datatables.js -->
            <table id="myTable" class="table table-striped table-bordered dataTable table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>First Name</th>
                  <th>Other Name(s)</th>
                  <th>Role</th>
                  <th>Phone</th>
                  <th>District</th>
                  <th>Address</th>
                  <th>Photo</th>
                  <th>Email</th>
                  <th>Status</th>
                  <th>server_creation_date</th>
                  <th>server_update_date</th>
                </tr>
              </thead>
              <tbody>
              </tbody>
            </table>
          </div>
        </div>
        <!-- END Dynamic Table Full Pagination -->
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

  <!-- Page JS Code -->
  <script src="../assets/js/pages/be_tables_datatables.min.js"></script>

  <script>
      $(document).ready(function() {
        $('#myTable').DataTable({   
          data: <?php echo $jsonData; ?>,
          columns: [
            { data: 'id' },
            { data: 'first_name' },
            { data: 'last_name' },
            { data: 'role' },
            { data: 'user_phone' },
            { data: 'user_district' },
            { data: 'user_address' },
            { data: 'user_photo' },
            { data: 'email' },
            { data: 'is_active' },
            { data: 'server_creation_date' },
            { data: 'server_update_date' },
          ]
        });
      });
    </script>

</body>

</html>