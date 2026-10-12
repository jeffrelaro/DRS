<?php
require_once "config.php";
require_once "icons.php";

$errors = [];
$identifier = "";
$showSignupSuccess = !empty($_SESSION['signup_success']);
$showResetSuccess  = !empty($_SESSION['password_reset_success']);
unset($_SESSION['signup_success'], $_SESSION['password_reset_success']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ---------------------------------------------------------
    // 1. REQUIRED FIELD VALIDATION
    // ---------------------------------------------------------
    $identifier = trim($_POST['identifier'] ?? '');
    $password   = $_POST['password'] ?? '';

    if ($identifier === '') $errors['identifier'] = "Enter your email or contact number.";
    if ($password === '')   $errors['password']   = "Enter your password.";

    if (empty($errors)) {
        $stmt = $conn->prepare(
            "SELECT id, full_name, password FROM users WHERE email = ? OR contact_number = ?"
        );
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if (!$user || !password_verify($password, $user['password'])) {
            // Same message either way — don't reveal whether the account exists
            $errors['form'] = "Incorrect email/contact number or password.";
        } else {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            header("Location: dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="split-card split-card--login">
  <div class="form-side">
    <div class="form-inner">

      <?php if ($showSignupSuccess): ?>
        <p class="banner banner--ok" role="status">Account created! You can log in now.</p>
      <?php elseif ($showResetSuccess): ?>
        <p class="banner banner--ok" role="status">Password reset! Please log in with your new password.</p>
      <?php elseif (!empty($errors['form'])): ?>
        <p class="banner banner--error" role="alert"><?= htmlspecialchars($errors['form']) ?></p>
      <?php endif; ?>

      <h1 class="auth-title">Sign in to DRS</h1>

      <form method="post" action="login.php" novalidate>

        <div class="field">
          <label for="identifier" class="sr-only">Email or Contact Number</label>
          <div class="input-wrap">
          <?= icon('phonemail', 'field-icon--wide') ?>
            <input type="text" id="identifier" name="identifier"
                   placeholder="Email or Contact Number"
                 value="<?= htmlspecialchars($identifier) ?>"
                   autocomplete="username"<?= !empty($errors['identifier']) ? ' aria-invalid="true" aria-describedby="identifier-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['identifier'])): ?>
            <span class="error" id="identifier-error"><?= htmlspecialchars($errors['identifier']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field field--last">
          <label for="password" class="sr-only">Password</label>
          <div class="input-wrap">
          <?= icon('lock') ?>
            <input type="password" id="password" name="password"
                   placeholder="Password"
                   autocomplete="current-password"<?= !empty($errors['password']) ? ' aria-invalid="true" aria-describedby="password-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <span class="error" id="password-error"><?= htmlspecialchars($errors['password']) ?></span>
          <?php endif; ?>
        </div>

        <div class="forgot-row"><a href="forgot-password.php">Forgot Password?</a></div>

        <button type="submit" class="btn btn-primary btn-signin">Sign In</button>
      </form>
    </div>
  </div>

  <aside class="panel-side">
    <h2>Hello!</h2>
    <p>Enter your personal details and start journey with us</p>
    <a href="signup.php" class="btn btn-outline">Sign Up</a>
  </aside>
</main>

</body>
</html>
