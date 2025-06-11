<?php
include "config/db-config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $newPassword = password_hash($_POST['newPassword'], PASSWORD_BCRYPT);

    // Verify the token and its expiry
    $stmt = $conn->prepare("SELECT email FROM user_table WHERE reset_token = ? AND token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($email);
        $stmt->fetch();

        // Update the password and clear the token
        $updateStmt = $conn->prepare("UPDATE user_table SET password = ?, reset_token = NULL, token_expiry = NULL WHERE email = ?");
        $updateStmt->bind_param("ss", $newPassword, $email);

        if ($updateStmt->execute()) {
            $message = "Your password has been reset successfully.";
        } else {
            $message = "Error resetting your password. Please try again.";
        }

        $updateStmt->close();
    } else {
        $message = "Invalid or expired token.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>Daddy - Reset Password</title>

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
    <link rel="shortcut icon" href="assets/media/favicons/favicon.png">
    <link rel="icon" type="image/png" sizes="192x192" href="assets/media/favicons/favicon-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/media/favicons/apple-touch-icon-180x180.png">
    <!-- END Icons -->

    <!-- Stylesheets -->
    <!-- Dashmix framework -->
    <link rel="stylesheet" id="css-main" href="assets/css/dashmix.min.css">
	  
  </head>

  <body>
    <div id="page-container">

      <!-- Main Container -->
      <main id="main-container">
        <!-- Page Content -->
        <div class="row g-0 justify-content-center bg-body-dark">
          <div class="hero-static col-sm-10 col-md-8 col-xl-6 d-flex align-items-center p-2 px-sm-0">
            <!-- Reminder Block -->
            <div class="block block-rounded block-transparent block-fx-pop w-100 mb-0 overflow-hidden bg-image" style="background-image: url('assets/media/photos/login_side_img.jpg');">
              <div class="row g-0">
                <div class="col-md-6 order-md-1 bg-body-extra-light">
                  <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6">
                    <!-- Header -->
                    <div class="p-2">
						<h5 class="mb-2 text-left text-primary">Hello! Reset your password</h5>
						<a style="font-size: 14px;">Enter a new password</a>
					</div><br>
					<?php if (isset($message)): ?>
						<div class="alert alert-info text-center"><?php echo htmlspecialchars($message); ?></div>
					<?php endif; ?>
                    <!-- END Header -->

                    <!-- Forget Password Form -->
                    <form class="js-validation-reminder" action="reset-password.php" method="POST">
                      <div class="mb-4">
                        <input type="password" class="form-control form-control-alt" id="reminder-credential" name="reminder-credential" placeholder="**********">
                      </div>
                      <div class="text-center mb-4">
                        <button type="submit" class="btn w-100 btn-hero btn-primary">
                          <i class="fa fa-fw fa-lock-open opacity-50 me-1"></i> Reset Password
                        </button>
                      </div>
                    </form>
                    <!-- END Forget Password Form -->
                  </div>
                </div>
                <div class="col-md-6 order-md-0 bg-primary-dark-op d-flex align-items-center">
                  <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6 text-center">
                    <img class="img-fluid" src="assets/media/photos/fot_fedco_round_ed.png" alt="Responsive image" style="width: 90%;">
                  </div>
                </div>
              </div>
            </div>
            <!-- END Reminder Block -->
          </div>
        </div>
        <!-- END Page Content -->
      </main>
      <!-- END Main Container -->
    </div>
    <!-- END Page Container -->

    <!--
      Dashmix JS

      Core libraries and functionality
      webpack is putting everything together at assets/_js/main/app.js
    -->
    <script src="assets/js/dashmix.app.min.js"></script>

    <!-- jQuery (required for jQuery Validation plugin) -->
    <script src="assets/js/lib/jquery.min.js"></script>

    <!-- Page JS Plugins -->
    <script src="assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>

    <!-- Page JS Code -->
    <script src="assets/js/pages/op_auth_reminder.min.js"></script>
	<script type="text/javascript">
		$(".alert").delay(4000).slideUp(500, function() {
			$(this).alert('close');
		});
	</script>
  </body>
</html>
