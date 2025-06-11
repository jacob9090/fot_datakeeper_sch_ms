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

// Check for success or error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : null;
$error = isset($_SESSION['error']) ? $_SESSION['error'] : null;

// Clear session messages to prevent repeated display
unset($_SESSION['success']);
unset($_SESSION['error']);

$query = "SELECT first_name, last_name, user_district, role, user_phone FROM fedco_users ORDER BY id DESC;";
$result = $conn->query($query);

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>FEDCO - Add User</title>

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
                  <a class="nav-main-link active" href="add-user.php">
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

        <div class="row">
            <div class="col-md-4">
              <form action="add-user-backend.php" method="POST" enctype="multipart/form-data">
                <div class="block block-rounded">
                  <div class="block-header block-header-default">
                    <h3 class="block-title">Block Form</h3>
                    <div class="block-options">
                      <button type="submit" class="btn btn-sm btn-outline-primary">
                        <i class="fa fa-check"></i> Submit
                      </button>
                      <button type="reset" class="btn btn-sm btn-outline-danger">Reset</button>
                    </div>
                  </div>
                  <div class="block-content">
                    <div class="col-lg-12 col-xl-12">
                    <div class="form-floating mb-4">
                      <input type="text" class="form-control" id="example-text-input-floating" name="first_name" placeholder="John Doe">
                      <label class="form-label" for="example-text-input-floating">First Name</label>
                    </div>
                    <div class="form-floating mb-4">
                      <input type="text" class="form-control" id="example-text-input-floating" name="last_name" placeholder="John Doe">
                      <label class="form-label" for="example-text-input-floating">Other Name(s)</label>
                    </div>

                    <div class="form-floating mb-4">
                      <select class="form-select" id="example-select-floating" name="role" aria-label="Floating label select example">
                        <option selected>Select Role</option>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                        <option value="Field Agent">Field Agent</option>
                      </select>
                      <label class="form-label" for="example-select-floating">Role</label>
                    </div>

                    <div class="form-floating mb-4">
                      <select class="form-select" id="example-select-floating" name="user_district" aria-label="Floating label select example">
                        <option selected>Select District</option>
                        <option value="Asankragwa, Agona Amenfi">Asankragwa, Agona Amenfi</option>
                        <option value="Asantie Bekwai, Antoakrom">Asantie Bekwai, Antoakrom</option>
                        <option value="Enchi">Enchi</option>
                        <option value="Goaso, Hwidiem, Asumura">Goaso, Hwidiem, Asumura</option>
                        <option value="Kumasi">Kumasi</option>
                        <option value="Manso Amenfi">Manso Amenfi</option>
                        <option value="Nkawkaw, Juaso, Konogo">Nkawkaw, Juaso, Konogo</option>
                        <option value="Obuase, Dunkwa, Bawdie, Wassa Akropong">Obuase, Dunkwa, Bawdie, Wassa Akropong</option>
                        <option value="Oda, Ofoasi, Keda, Akroso">Oda, Ofoasi, Keda, Akroso</option>
                        <option value="Samreboi, Prestea Nkwanta">Samreboi, Prestea Nkwanta</option>
                        <option value="Sefwi Bekwai, Diaso">Sefwi Bekwai, Diaso</option>
                        <option value="Sunyani, Techiman, Nsuatre, Atronie">Sunyani, Techiman, Nsuatre, Atronie</option>
                        <option value="Twifo Praso, Atobease">Twifo Praso, Atobease</option>
                      </select>
                      <label class="form-label" for="example-select-floating">District</label>
                    </div>

                    <div class="form-floating mb-4">
                      <input type="number" class="form-control" id="example-text-input-floating" name="user_phone" placeholder="0241111111">
                      <label class="form-label" for="example-text-input-floating">Contact Number</label>
                    </div>

                    <div class="form-floating mb-4">
                      <textarea class="form-control" id="example-textarea-floating" name="user_address" style="height: 200px" placeholder="Leave a comment here"></textarea>
                      <label class="form-label" for="example-textarea-floating">Address</label>
                    </div>

                    <div class="mb-4">
                      <label class="form-label" for="example-file-input">Photo</label>
                      <input class="form-control" type="file" name="user_photo" id="example-file-input">
                    </div>

                    <div class="form-floating mb-4">
                      <input type="email" class="form-control" id="example-email-input-floating" name="email" placeholder="john.doe@example.com">
                      <label class="form-label" for="example-email-input-floating">Email address</label>
                    </div>

                    <div class="form-floating mb-4">
                      <input type="text" class="form-control" id="example-text-input-readonly-floating" name="example-text-input-readonly-floating" placeholder="**********" value="FEDCO@123" readonly>
                      <label for="example-text-input-readonly-floating">Password (readonly)</label>
                    </div>
                    
                  </div>
                  </div>
                </div>
              </form>
            </div>
            <div class="col-md-8">
              <div class="block block-rounded">
                <div class="block-header block-header-default">
                  <h3 class="block-title">Block Form</h3>
                </div>
                <div class="block-content">
                  <div class="row py-sm-2 py-md-2">
                    <div class="col-sm-12 col-md-12">
                      <table id="myTable" class="table table-striped table-bordered dataTable table-hover">
                        <thead>
                          <tr>
                            <th>Name</th>
                            <th>District</th>
                            <th>Role</th>
                            <th style="text-align: center;">Action</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                          <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                          <td><?php echo htmlspecialchars($row['user_district']); ?></td>
                          <td><?php echo htmlspecialchars($row['role']); ?></td>
                          <td style="text-align: center;"><i class="fa fa-eye"></i></td>
                        </tr>
                        <?php endwhile; ?>
                          <?php else: ?>
                            <tr>
                              <td colspan="3">No users found.</td>
                            </tr>
                          <?php endif; ?>
                        </tbody>
                      </table>
                      <?php
                    // Close the database connection
                    $conn->close();
                    ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- END Form Submission in Options -->
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