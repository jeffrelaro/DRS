<?php
require_once __DIR__ . '/../includes/functions.php';

if (is_admin()) {
    redirect('dashboard.php');
}

$errors = [];
$email  = '';
$flash  = flash_get();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        $errors['form'] = 'Your session expired. Please try again.';
    }

    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if ($email === '') {
        $errors['email'] = 'Admin email is required.';
    } elseif (!validate_email($email)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (!$errors) {
        try {
            if (admin_is_locked()) {
                $errors['form'] = 'Too many failed attempts. Please wait ' . ADMIN_LOCK_MINUTES . ' minutes and try again.';
            } else {
                $stmt = db()->prepare('SELECT id, full_name, password_hash FROM admins WHERE email = ? LIMIT 1');
                $stmt->execute([strtolower($email)]);
                $admin = $stmt->fetch();

                $verified = password_verify($password, $admin['password_hash'] ?? DUMMY_HASH);

                if ($admin && $verified) {
                    admin_clear_failures();
                    session_regenerate_id(true);
                    $_SESSION['admin_id']   = (int) $admin['id'];
                    $_SESSION['admin_name'] = $admin['full_name'];

                    if ($remember) {
                        setcookie(session_name(), session_id(), [
                            'expires'  => time() + 60 * 60 * 24 * 7,
                            'path'     => '/',
                            'httponly' => true,
                            'samesite' => 'Lax',
                        ]);
                    }
                    redirect('dashboard.php');
                }

                admin_record_failure();
                $errors['form'] = 'Incorrect email or password.';
            }
        } catch (PDOException $ex) {
            error_log($ex->getMessage());
            $errors['form'] = 'Something went wrong. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Sign In | Resident Portal</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="page">
  <div class="card">

    <section class="left">
      <div class="logo"><img src="../assets/images/seal.png" alt=""></div>
      <h2>Resident Portal<br>Admin Panel</h2>
      <p>Manage document requests, residents, and barangay records for Barangay Catarman, Cordova, Cebu &mdash; all from one dashboard.</p>
    </section>

    <main class="right">
      <div class="form-wrap">
      <span class="chip">STAFF / ADMIN ACCESS</span>
      <h1>Welcome back</h1>
      <p class="sub">Sign in to manage the barangay resident system.</p>

      <?php if ($flash): ?>
        <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endif; ?>
      <?php if (isset($errors['form'])): ?>
        <div class="alert error"><?= e($errors['form']) ?></div>
      <?php endif; ?>

      <form method="post" action="login.php" novalidate>
        <?= csrf_field() ?>

        <div class="<?= field_class($errors, 'email') ?>">
          <label for="email">Admin Email</label>
          <input type="email" id="email" name="email" value="<?= e($email) ?>"
                 maxlength="100" autocomplete="username">
          <?php field_err($errors, 'email'); ?>
        </div>

        <div class="<?= field_class($errors, 'password') ?>">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" autocomplete="current-password">
          <?php field_err($errors, 'password'); ?>
        </div>

        <label class="remember"><input type="checkbox" name="remember" value="1"> Remember me</label>

        <button type="submit" class="btn">Sign In to Admin Panel</button>
      </form>
      </div>
    </main>

  </div>
</div>
</body>
</html>
