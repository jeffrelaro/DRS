<?php
require_once "config.php";
require_once "icons.php";
require_once "functions.php";

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // 1. REQUIRED FIELD VALIDATION
    $identifier = trim($_POST['identifier'] ?? '');
    if ($identifier === '') {
        $errors['identifier'] = "Enter your email or contact number.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR contact_number = ?");
        $stmt->bind_param("ss", $identifier, $identifier);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        // Always behave the same whether the account exists or not —
        // prevents attackers from discovering which accounts are real.
        if ($user) {
            $otp = generate_otp();
            $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $insert = $conn->prepare(
                "INSERT INTO password_reset_otps (user_id, otp_hash, expires_at) VALUES (?, ?, ?)"
            );
            $insert->bind_param("iss", $user['id'], $otp_hash, $expires_at);
            $insert->execute();
            $insert->close();

            // TODO (production): send $otp to the resident's email/phone here.
            // For local testing, temporarily uncomment the line below to see it:
            // echo "DEBUG OTP: $otp";

            $_SESSION['reset_user_id'] = $user['id'];
        }

        $_SESSION['reset_identifier'] = $identifier;
        header("Location: verify-otp.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Forgot Password — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<a href="login.php" class="page-back" aria-label="Back to sign in"><svg viewBox="0 0 64 46" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 3 L6 15 L20 27"/><path d="M6 15 H44 a13 13 0 0 1 0 26 H26"/></svg></a>

<main class="center-card center-card--forgot">
  <img src="assets/thinking.png" alt="" class="thinking">
  <h1>Forgot your password?</h1>
  <p class="subtitle">Enter your email or contact number<br>to retrieve your password</p>

  <form method="post" action="forgot-password.php" novalidate>

        <div class="field">
          <label for="identifier" class="sr-only">Email or Contact number</label>
          <div class="input-wrap">
          <?= icon('phonemail', 'field-icon--wide') ?>
            <input type="text" id="identifier" name="identifier"
                   placeholder="Email or Contact number"
                   autocomplete="username"<?= !empty($errors['identifier']) ? ' aria-invalid="true" aria-describedby="identifier-error"' : '' ?>>
          </div>
          <?php if (!empty($errors['identifier'])): ?>
            <span class="error" id="identifier-error"><?= htmlspecialchars($errors['identifier']) ?></span>
          <?php endif; ?>
        </div>

    <button type="submit" class="btn btn-primary btn-wide">Continue</button>
  </form>
</main>

</body>
</html>
