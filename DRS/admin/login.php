<?php
require_once "../config.php";
 
$errors = [];
$email = "";
$showResetSuccess = !empty($_SESSION['admin_password_reset_success']);
unset($_SESSION['admin_password_reset_success']);
 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
 
    // 1. REQUIRED FIELD VALIDATION
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
 
    if ($email === '')    $errors['email']    = "Enter your admin email.";
    if ($password === '') $errors['password'] = "Enter your password.";
 
    // 4. VALIDATING EMAIL ADDRESSES
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }
 
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, full_name, password FROM admin_users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();
 
        if (!$admin || !password_verify($password, $admin['password'])) {
            $errors['form'] = "Incorrect email or password.";
        } else {
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_full_name'] = $admin['full_name'];
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
<title>Admin Sign In — Resident Portal</title>
<link rel="stylesheet" href="../assets/admin-style.css">
</head>
<body>
 
<div class="admin-split-card">
  <div class="admin-brand-side">
    <div class="admin-badge">&#128737;</div>
    <h1>Resident Portal<br>Admin Panel</h1>
    <p>Manage document requests, residents, and barangay records for Barangay Catarman, Cordova, Cebu — all from one dashboard.</p>
  </div>
 
  <div class="admin-form-side">
    <span class="role-badge">STAFF / ADMIN ACCESS</span>
    <h2>Welcome back</h2>
    <p class="subtitle">Sign in to manage the barangay resident system.</p>
 
    <?php if ($showResetSuccess): ?>
      <p class="success-banner">Password reset! Please log in with your new password.</p>
    <?php endif; ?>
    <?php if (!empty($errors['form'])): ?>
      <p class="error-banner"><?= htmlspecialchars($errors['form']) ?></p>
    <?php endif; ?>
 
    <form method="post" action="login.php" novalidate>
      <label for="email">Admin Email</label>
      <input type="text" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
      <?php if (!empty($errors['email'])): ?><span class="error"><?= htmlspecialchars($errors['email']) ?></span><?php endif; ?>
 
      <label for="password">Password</label>
      <input type="password" id="password" name="password">
      <?php if (!empty($errors['password'])): ?><span class="error"><?= htmlspecialchars($errors['password']) ?></span><?php endif; ?>
 
      <div class="row-between">
        <label class="checkbox-row"><input type="checkbox" checked> Remember me</label>
      </div>
 
      <button type="submit">Sign In to Admin Panel</button>
    </form>
  </div>
</div>
 
</body>
</html>