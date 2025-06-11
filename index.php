<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">

    <title>Daddy - Login</title>

    <meta name="description" content="">
    <meta name="author" content="pixelcave">
    <meta name="robots" content="noindex, nofollow">

    <!-- Open Graph Meta -->
    <meta property="og:title" content="">
    <meta property="og:site_name" content="">
    <meta property="og:description" content="">
    <meta property="og:type" content="website">
    <meta property="og:url" content="">
    <meta property="og:image" content="">

    <!-- Icons -->
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
            <!-- Sign In Block -->
            <div class="block block-rounded block-transparent block-fx-pop w-100 mb-0 overflow-hidden bg-image" style="background-image: url('assets/media/photos/login_side_img.jpg');">
              <div class="row g-0">
                <div class="col-md-6 order-md-1 bg-body-extra-light">
                  <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6">
                    <!-- Header -->
              			<div class="p-2">
              				<h5 class="mb-2 text-left text-primary">Hello! Let's get started</h5>
              				<a style="font-size: 14px;">Sign in to continue</a>
              			</div><br>
              			<?php
              				session_start();
              				if (isset($_SESSION['danger_alert'])) {
              					echo '<div class="alert alert-danger" role="alert">' . $_SESSION['danger_alert'] . '</div>';
              					unset($_SESSION['danger_alert']); // Clear message after displaying
              				}
              			?>
              			<br>
                    <!-- END Header -->

                    <!-- Sign In Form -->
                    <form class="js-validation-signin" action="process-login.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                      <div class="mb-4">
                        <input type="email" class="form-control form-control-alt" id="login-username" name="email" placeholder="Email" autocomplete="email" required>
                      </div>
                      <div class="mb-4">
                        <input type="password" class="form-control form-control-alt" id="login-password" name="password" placeholder="Password" autocomplete="current-password" required>
                      </div>
                      
                      <div class="mb-4">
                        <button type="submit" class="btn w-100 btn-hero btn-primary">
                          <i class="fa fa-fw fa-sign-in-alt opacity-50 me-1"></i> Sign In
                        </button>
                      </div>
          						<div class="p-2 text-center text-muted" style="font-size: 14px;">
          							Forgot your password?  <a class="text-black" href="forgot-password.php"><span class="underline">Reset Password</span></a>
          						</div>
                    </form>
                    <!-- END Sign In Form -->
                  </div>
                </div>
                <div class="col-md-6 order-md-0 bg-primary-dark-op d-flex align-items-center">
                  <div class="block-content block-content-full px-lg-5 py-md-5 py-lg-6 text-center">
                    <img class="img-fluid" src="assets/media/photos/fot_fedco_round_ed.png" alt="Responsive image" style="width: 90%;">
                  </div>
                </div>
              </div>
            </div>
            <!-- END Sign In Block -->
          </div>
        </div>
        <!-- END Page Content -->
      </main>
      <!-- END Main Container -->
    </div>
    <!-- END Page Container -->

    <script src="assets/js/dashmix.app.min.js"></script>

    <!-- jQuery (required for jQuery Validation plugin) -->
    <script src="assets/js/lib/jquery.min.js"></script>

    <!-- Page JS Plugins -->
    <script src="assets/js/plugins/jquery-validation/jquery.validate.min.js"></script>

    <!-- Page JS Code -->
    <script src="assets/js/pages/op_auth_signin.min.js"></script>
  	<script type="text/javascript">
  		$(".alert").delay(4000).slideUp(500, function() {
  			$(this).alert('close');
  		});
  	</script>
  	
  	<script>
document.getElementById('togglePassword').addEventListener('click', function() {
  const password = document.getElementById('login-password');
  const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
  password.setAttribute('type', type);
  this.querySelector('i').classList.toggle('fa-eye-slash');
});
</script>
  </body>
</html>
