<?php
session_start();

include "../config/db-config.php";

// Check if user is logged in and has the appropriate role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// Optional: include additional security headers
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' https://fonts.googleapis.com');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

// Fetch districts for filtering
$query = "SELECT DISTINCT hh_district FROM (
    SELECT hh_district FROM anyinam_a_hh
    UNION ALL
    SELECT hh_district FROM asante_bekwai_hh
    UNION ALL
    SELECT hh_district FROM dunkwa_hh
    UNION ALL
    SELECT hh_district FROM enchi_hh
    UNION ALL
    SELECT hh_district FROM goaso_hh
    UNION ALL
    SELECT hh_district FROM juaso_hh
    UNION ALL
    SELECT hh_district FROM new_edubiase_hh
    UNION ALL
    SELECT hh_district FROM nkawei_hh
    UNION ALL
    SELECT hh_district FROM sefwi_bekwai_hh
    UNION ALL
    SELECT hh_district FROM sunyani_hh
) AS districts ORDER BY hh_district ASC";

$result = $conn->query($query);
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">

  <title>FEDCO - Household Survey</title>

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
  
  <!-- Page JS Plugins CSS -->
    <link rel="stylesheet" href="../assets/js/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css">
    <link rel="stylesheet" href="../assets/js/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/js/plugins/ion-rangeslider/css/ion.rangeSlider.css">
    <link rel="stylesheet" href="../assets/js/plugins/dropzone/min/dropzone.min.css">
    <link rel="stylesheet" href="../assets/js/plugins/flatpickr/flatpickr.min.css">

  <!-- Stylesheets -->
  <!-- Page JS Plugins CSS -->
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
        <a href="sustainability-overview.php" class="mini-nav-item h-header">
          <i class="fa fa-tree text-primary-light fs-lg"></i>
        </a>
        <!-- END Logo -->

        <!-- Mini Main Nav -->
        <nav class="flex-grow-1 space-y-1">
          <a class="mini-nav-item" href="commdev-overview.php">
            <i class="fa fa-users fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="add-user.php">
            <i class="fa fa-user fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="upload-registry.php">
            <i class="fa fa-city fs-sm"></i>
          </a>
        </nav>
        <!-- END Mini Main Nav -->

        <!-- Mini User Nav -->
        <nav class="space-y-1 pb-2">
          <a class="mini-nav-item" href="profile.php">
            <i class="fa fa-cog fs-sm"></i>
          </a>
          <a class="mini-nav-item" href="../signout.php">
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
              <span class="smini-visible">FEDCO</span>
              <span class="smini-hidden">FEDCO</span>
            </a>
            <!-- END Logo -->

            <!-- Options -->
            <div>
                
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
            <li class="nav-main-item open">
              <a class="nav-main-link nav-main-link-submenu" data-toggle="submenu" aria-haspopup="true"
                aria-expanded="true" href="#">
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
                  <a class="nav-main-link active" href="household-survey.php">
                    <span class="nav-main-link-name">Household Survey</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="observation-survey.php">
                    <span class="nav-main-link-name">Observation</span>
                  </a>
                </li>
                <li class="nav-main-item">
                  <a class="nav-main-link" href="child-survey.php">
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
                  <a class="nav-main-link" href="child-survey.php-archive">
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
        <div class="block block-rounded text-center">
          <div class="block-content">
            <div class="col-lg-12 col-xl-12">
                
                <div class="row mb-3">
            <div class="col-md-3">
                <label for="communityFilter">Filter by District</label>
                <select id="communityFilter" class="form-control" multiple="multiple">
                    <option value="">All Districts</option>
                    <?php while ($row = $result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($row['hh_district']) . "'>" . htmlspecialchars($row['hh_district']) . "</option>";
                    } ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="farmerFilter">Filter by Name</label>
                <input type="text" id="farmerFilter" class="form-control" placeholder="Enter Farmer Name">
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
              Household <small>Survey</small>
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
                        <th>What is the main fuel used by the household for cooking? \n</th>
                        <th>Does any household member own a working box iron or electric iron? \n</th>
                        <th>Does any household member own a working television or radio set?\n</th>
                        <th>Does any household member own a working mobile phone? \n</th>
                        <th>Does any household member own a working bicycle, motor cycle, or car. \n</th>
                        <th>What is the type of medical establishment usually used by this household?\n</th>
                        <th> What is the head of household\\'s educational level?\n</th>
                        <th>Can the head read a phrase/sentence in English? \n</th>
                        <th>Has the head of the household or farm owner benefited from adult literacy training programs?\r\n</th>
                        <th>Has the head of the household or farm owner been trained on revenue and livelihood diversification?\n</th>
                        <th>Has the head of the household or farm owner been trained in financial literacy?\n</th>
                        <th>Does the head of the household or farm owner uses mobile banking services?\r\n</th>
                        <th>If Yes Provide Momo Number\r\n</th>
                        <th>How many children between 5–17 years are in this household?\n</th>
                        <th>How many children between 5–17 years have birth certificates?\n</th>
                        <th>What other income generating activity do you have in addition to Cocoa</th>
                        <th>How much were you able to receive from the above activity in the past 12 months?</th>
                        <th>GPS address of the household</th>
                        <th>Signature of field agent</th>
                        <th>Photo of household\n</th>
                        <th>FT First Name</th>
                        <th>FT Last Name</th>
                        <th>FT Email</th>
                        <th>Start</th>
                        <th>Updated</th>
                        <th>Sync</th>
                        <th>Sync Updated</th>
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
                "url": "fetch_household_data.php",
                "type": "GET",
                "dataSrc": ""
            },
            "columns": [
                { "data": "id" },
                { "data": "hh_name" },
                { "data": "hh_district" },
                { "data": "hh_community" },
                { "data": "hhquestion4" },
                { "data": "hhquestion5" },
                { "data": "hhquestion6" },
                { "data": "hhquestion7" },
                { "data": "hhquestion8" },
                { "data": "hhquestion9" },
                { "data": "hhquestion10" },
                { "data": "hhquestion11" },
                { "data": "hhquestion12" },
                { "data": "hhquestion13" },
                { "data": "hhquestion14" },
                { "data": "hhquestion15" },
                { "data": "hhquestion16" },
                { "data": "hhquestion17" },
                { "data": "hhquestion18" },
                { "data": "hhquestion19" },
                { "data": "hhquestion20" },
                { "data": "hhquestion21" },
                { "data": "hhquestion22" },
                { "data": "hhquestion23" },
                { "data": "hhquestion24" },
                { "data": "hhquestion25" },
                { "data": "hhquestion26" },
                { "data": "hhquestion27" },
                { "data": "hhquestion28" },
                { "data": "hhquestion29" },
                { "data": "hhquestion30" },
                { "data": "hhquestion31" },
                { "data": "hhquestion32" },
                { "data": "hhquestion36" },
                { "data": "hhquestion37" },
                { "data": "hh_location" },
                { "data": "farmer_photo" },
                { "data": "signature" },
                { "data": "user_fname" },
                { "data": "user_oname" },
                { "data": "user_email" },
                { "data": "on_create" },
                { "data": "on_update" },
                { "data": "server_on_create" },
                { "data": "server_on_update" }
            ],
            "destroy": true
        });

        // Filter by District
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
            csvContent += `${row.id}, ${row.hh_name}, ${row.hh_district}, ${row.hh_community}, ${row.hhquestion4}, ${row.hhquestion5}\n`;
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
        placeholder: "Select Community(s)"
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