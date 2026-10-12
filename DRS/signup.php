<?php
require_once "config.php";
require_once "icons.php";

$errors = [];
$full_name = $email = $contact_number = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ---------------------------------------------------------
    // 1. REQUIRED FIELD VALIDATION
    // ---------------------------------------------------------
    $full_name      = trim($_POST['full_name'] ?? '');
    $email          = trim($_POST['email'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $password       = $_POST['password'] ?? '';

    if ($full_name === '')      $errors['full_name']      = "Name is required.";
    if ($email === '')          $errors['email']          = "Email is required.";
    if ($contact_number === '') $errors['contact_number'] = "Contact number is required.";
    if ($password === '')       $errors['password']       = "Password is required.";

    // ---------------------------------------------------------
    // 2. VALIDATING TEXT INPUT (full name)
    // ---------------------------------------------------------
    if ($full_name !== '' && !preg_match("/^[A-Za-z .'-]{2,50}$/", $full_name)) {
        $errors['full_name'] = "Name must be 2-50 letters (letters, spaces, hyphens, apostrophes only).";
    }

    // ---------------------------------------------------------
    // 3. VALIDATING NUMBERS (contact number)
    // Phone numbers aren't a single integer value (they can start
    // with 0 or +), so we validate that every character is a digit
    // and the length is realistic — same idea as FILTER_VALIDATE_INT,
    // adapted for a phone number instead of an age.
    // ---------------------------------------------------------
    if ($contact_number !== '') {
        $digits_only = preg_replace('/[^0-9]/', '', $contact_number);
        if (!preg_match('/^[0-9]{10,13}$/', $digits_only)) {
            $errors['contact_number'] = "Enter a valid contact number (10-13 digits).";
        }
    }

    // ---------------------------------------------------------
    // 4. VALIDATING EMAIL ADDRESSES
    // ---------------------------------------------------------
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Invalid email address.";
    }

    // Extra check needed for a real signup: password strength
    if ($password !== '' && strlen($password) < 8) {
        $errors['password'] = "Password must be at least 8 characters.";
    }

    if (empty($errors)) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $errors['email'] = "An account with this email already exists.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare(
                "INSERT INTO users (full_name, email, contact_number, password) VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("ssss", $full_name, $email, $contact_number, $hashed_password);

            if ($stmt->execute()) {
                $_SESSION['signup_success'] = true;
                header("Location: login.php");
                exit;
            } else {
                $errors['form'] = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Account — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="split-card split-card--signup">
  <aside class="panel-side">
    <h2>Welcome!</h2>
    <p>To keep connected with us please login with your personal info</p>
    <a href="login.php" class="btn btn-outline">Sign In</a>
  </aside>

  <div class="form-side">
    <div class="form-inner">

      <?php if (!empty($errors['form'])): ?>
        <p class="banner banner--error" role="alert"><?= htmlspecialchars($errors['form']) ?></p>
      <?php endif; ?>

      <h1 class="auth-title">Create Account</h1>

      <form method="post" action="signup.php" novalidate>

        <div class="field">
          <label for="full_name" class="sr-only">Name</label>
          <div class="input-wrap">
          <?= icon('user') ?>
            <input type="text" id="full_name" name="full_name"
                   placeholder="Name"
                 value="<?= htmlspecialchars($full_name) ?>"
                   autocomplete="name"<?= !empty($errors['full_name']) ? ' aria-invalid="true" aria-describedby="full_name-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['full_name'])): ?>
            <span class="error" id="full_name-error"><?= htmlspecialchars($errors['full_name']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="email" class="sr-only">Email</label>
          <div class="input-wrap">
          <?= icon('mail') ?>
            <input type="text" id="email" name="email"
                   placeholder="Email"
                 value="<?= htmlspecialchars($email) ?>"
                   autocomplete="email"<?= !empty($errors['email']) ? ' aria-invalid="true" aria-describedby="email-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['email'])): ?>
            <span class="error" id="email-error"><?= htmlspecialchars($errors['email']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field">
          <label for="contact_number" class="sr-only">Contact Number</label>
          <div class="input-wrap">
          <?= icon('phone') ?>
            <input type="text" id="contact_number" name="contact_number"
                   placeholder="Contact Number"
                 value="<?= htmlspecialchars($contact_number) ?>"
                   autocomplete="tel"<?= !empty($errors['contact_number']) ? ' aria-invalid="true" aria-describedby="contact_number-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['contact_number'])): ?>
            <span class="error" id="contact_number-error"><?= htmlspecialchars($errors['contact_number']) ?></span>
          <?php endif; ?>
        </div>

        <div class="field field--last">
          <label for="password" class="sr-only">Password</label>
          <div class="input-wrap">
          <?= icon('lock') ?>
            <input type="password" id="password" name="password"
                   placeholder="Password"
                   autocomplete="new-password"<?= !empty($errors['password']) ? ' aria-invalid="true" aria-describedby="password-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['password'])): ?>
            <span class="error" id="password-error"><?= htmlspecialchars($errors['password']) ?></span>
          <?php endif; ?>
        </div>

        <button type="submit" class="btn btn-primary btn-signin">Sign Up</button>
      </form>
    </div>
  </div>
</main>

</body>
</html>
