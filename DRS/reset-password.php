<?php
require_once "config.php";

if (empty($_SESSION['reset_verified']) || empty($_SESSION['reset_user_id'])) {
    header("Location: forgot-password.php");
    exit;
}

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. REQUIRED FIELD VALIDATION
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($password === '') $errors['password'] = "Enter a new password.";
    if ($confirm === '')  $errors['confirm_password'] = "Please confirm your password.";

    if ($password !== '' && strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }
    if ($password !== '' && $confirm !== '' && $password !== $confirm) {
        $errors['confirm_password'] = "Passwords do not match.";
    }

    if (empty($errors)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashed, $_SESSION['reset_user_id']);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['reset_verified'], $_SESSION['reset_user_id'], $_SESSION['reset_identifier']);
        $_SESSION['password_reset_success'] = true;
        header("Location: login.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<a href="login.php" class="page-back" aria-label="Cancel and go back to sign in"><svg viewBox="0 0 64 46" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 3 L6 15 L20 27"/><path d="M6 15 H44 a13 13 0 0 1 0 26 H26"/></svg></a>

<main class="center-card center-card--reset">
  <img src="assets/lock.svg" alt="" class="lock-badge">
  <h1>Reset Password</h1>

  <form method="post" action="reset-password.php" novalidate>

        <div class="field field--1">
          <label for="password" class="sr-only">New password</label>
          <div class="input-wrap">
            <input type="password" id="password" name="password"
                   placeholder="New password"
                   autocomplete="new-password"<?= !empty($errors['password']) ? ' aria-invalid="true" aria-describedby="password-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <span class="error" id="password-error"><?= htmlspecialchars($errors['password']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field field--2">
          <label for="confirm_password" class="sr-only">Confirm New password</label>
          <div class="input-wrap">
            <input type="password" id="confirm_password" name="confirm_password"
                   placeholder="Confirm New password"
                   autocomplete="new-password"<?= !empty($errors['confirm_password']) ? ' aria-invalid="true" aria-describedby="confirm_password-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['confirm_password'])): ?>
            <span class="error" id="confirm_password-error"><?= htmlspecialchars($errors['confirm_password']) ?></span>
          <?php endif; ?>
        </div>

    <button type="submit" class="btn btn-primary btn-pill-sm">Continue</button>
    <a href="login.php" class="cancel-text">Cancel</a>
  </form>
</main>

</body>
</html>
