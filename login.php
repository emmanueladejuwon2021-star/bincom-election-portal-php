<?php
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Officer Login - Delta State Election Portal';
$currentPage = 'login';

$error = null;
$redirectTarget = isset($_GET['redirect']) ? trim($_GET['redirect']) : 'new-polling-unit.php';

// If already logged in, redirect straight away
if (isLoggedIn()) {
    header("Location: " . (!empty($redirectTarget) ? $redirectTarget : 'new-polling-unit.php'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $redirect = isset($_POST['redirect']) ? trim($_POST['redirect']) : 'new-polling-unit.php';

    if (empty($email) || empty($password)) {
        $error = "Please enter both your email address and password.";
    } elseif ($email === ADMIN_EMAIL && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_email'] = $email;
        $_SESSION['login_time'] = time();

        $destination = (!empty($redirect) && !str_starts_with($redirect, 'http')) ? $redirect : 'new-polling-unit.php';
        header("Location: {$destination}");
        exit;
    } else {
        $error = "Incorrect email address or password. Please verify your credentials.";
    }
}

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/navbar.php';
?>

<main class="container my-5 flex-grow-1 d-flex align-items-center justify-content-center">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-sm-10 col-md-8 col-lg-5">

      <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <!-- Card Header -->
        <div class="p-4 text-white text-center" style="background: linear-gradient(135deg, #064e3b 0%, #047857 100%);">
          <div class="rounded-circle bg-white bg-opacity-20 d-inline-flex p-3 mb-3">
            <i class="bi bi-shield-lock-fill fs-2 text-warning"></i>
          </div>
          <h4 class="fw-bold mb-1">Electoral Officer Sign In</h4>
          <p class="small text-white-50 mb-0">Authorized access required to record election returns</p>
        </div>

        <!-- Card Body -->
        <div class="card-body p-4 p-md-5">

          <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 mb-4" role="alert">
              <i class="bi bi-exclamation-triangle-fill fs-5"></i>
              <div class="small fw-medium"><?= htmlspecialchars($error) ?></div>
            </div>
          <?php endif; ?>

          <?php if (isset($_GET['redirect'])): ?>
            <div class="alert alert-warning d-flex align-items-center gap-2 mb-4 py-2" role="alert">
              <i class="bi bi-info-circle-fill"></i>
              <div class="small">Sign in to access the data entry portal.</div>
            </div>
          <?php endif; ?>

          <form action="login.php" method="POST" autocomplete="on">
            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTarget) ?>">

            <div class="mb-3">
              <label for="adminEmail" class="form-label small fw-semibold text-secondary">Official Email Address</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-envelope"></i>
                </span>
                <input type="email" 
                       class="form-control border-start-0" 
                       id="adminEmail" 
                       name="email" 
                       placeholder="e.g. admin@bincom.net" 
                       required 
                       autofocus>
              </div>
            </div>

            <div class="mb-4">
              <label for="adminPassword" class="form-label small fw-semibold text-secondary">Security Password</label>
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted">
                  <i class="bi bi-key"></i>
                </span>
                <input type="password" 
                       class="form-control border-start-0" 
                       id="adminPassword" 
                       name="password" 
                       placeholder="Enter your password" 
                       required>
              </div>
            </div>

            <button type="submit" class="btn btn-success w-100 py-2 fw-semibold shadow-sm mb-3">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Portal
            </button>

            <!-- Demo Credentials Helper Card -->
            <div class="p-3 rounded-3 bg-light border text-center">
              <div class="small text-muted mb-1">
                <strong>Evaluation Credentials:</strong>
              </div>
              <div class="font-monospace small text-dark mb-2">
                Email: <code><?= htmlspecialchars(ADMIN_EMAIL) ?></code><br>
                Password: <code><?= htmlspecialchars(ADMIN_PASSWORD) ?></code>
              </div>
              <button type="button" 
                      id="fillDemoBtn" 
                      class="btn btn-outline-secondary btn-sm py-1 px-3 small">
                <i class="bi bi-magic me-1"></i> Auto-Fill Credentials
              </button>
            </div>

          </form>

        </div>

        <!-- Card Footer -->
        <div class="card-footer bg-light border-0 py-3 text-center">
          <a href="index.php" class="small text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i> Return to Public Results Dashboard
          </a>
        </div>
      </div>

    </div>
  </div>
</main>

<script>
  document.getElementById('fillDemoBtn')?.addEventListener('click', function() {
    document.getElementById('adminEmail').value = '<?= addslashes(ADMIN_EMAIL) ?>';
    document.getElementById('adminPassword').value = '<?= addslashes(ADMIN_PASSWORD) ?>';
  });
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
