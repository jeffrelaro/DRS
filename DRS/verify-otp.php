<?php
require_once "config.php";
require_once "functions.php";

if (empty($_SESSION['reset_identifier'])) {
    header("Location: forgot-password.php");
    exit;
}

$errors = [];
$resendMessage = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (isset($_POST['resend'])) {
        if (!empty($_SESSION['reset_user_id'])) {
            $otp = generate_otp();
            $otp_hash = password_hash($otp, PASSWORD_DEFAULT);
            $expires_at = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $insert = $conn->prepare(
                "INSERT INTO password_reset_otps (user_id, otp_hash, expires_at) VALUES (?, ?, ?)"
            );
            $insert->bind_param("iss", $_SESSION['reset_user_id'], $otp_hash, $expires_at);
            $insert->execute();
            $insert->close();
            // TODO (production): resend $otp via email/SMS.
        }
        $resendMessage = "A new code has been sent if the account exists.";
    } else {

        // ---------------------------------------------------------
        // 1. REQUIRED FIELD VALIDATION (all 4 boxes filled in)
        // ---------------------------------------------------------
        $d1 = trim($_POST['otp1'] ?? '');
        $d2 = trim($_POST['otp2'] ?? '');
        $d3 = trim($_POST['otp3'] ?? '');
        $d4 = trim($_POST['otp4'] ?? '');

        if ($d1 === '' || $d2 === '' || $d3 === '' || $d4 === '') {
            $errors['otp'] = "Enter the 4-digit code sent to you.";
        } else {
            $code = $d1 . $d2 . $d3 . $d4;

            // ---------------------------------------------------------
            // 3. VALIDATING NUMBERS (the OTP must be a whole number,
            // exactly 4 digits — same filter_var technique as the
            // Age example, just a fixed-width code instead of a range)
            // ---------------------------------------------------------
            $otp_value = filter_var($code, FILTER_VALIDATE_INT);
            if ($otp_value === false || strlen($code) !== 4) {
                $errors['otp'] = "The code must be 4 digits.";
            } elseif (empty($_SESSION['reset_user_id'])) {
                $errors['otp'] = "That code is invalid or has expired.";
            } else {
                $stmt = $conn->prepare(
                    "SELECT id, otp_hash, attempts, expires_at FROM password_reset_otps
                     WHERE user_id = ? AND consumed_at IS NULL
                     ORDER BY created_at DESC LIMIT 1"
                );
                $stmt->bind_param("i", $_SESSION['reset_user_id']);
                $stmt->execute();
                $record = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $expired = !$record || strtotime($record['expires_at']) < time();
                $tooMany = $record && $record['attempts'] >= 5;

                if ($expired || $tooMany || !password_verify($code, $record['otp_hash'])) {
                    if ($record && !$expired) {
                        $conn->query("UPDATE password_reset_otps SET attempts = attempts + 1 WHERE id = " . (int)$record['id']);
                    }
                    $errors['otp'] = "That code is invalid or has expired.";
                } else {
                    $conn->query("UPDATE password_reset_otps SET consumed_at = NOW() WHERE id = " . (int)$record['id']);
                    $_SESSION['reset_verified'] = true;
                    header("Location: reset-password.php");
                    exit;
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>OTP Verification — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<a href="forgot-password.php" class="page-back" aria-label="Back"><svg viewBox="0 0 64 46" fill="none" stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M20 3 L6 15 L20 27"/><path d="M6 15 H44 a13 13 0 0 1 0 26 H26"/></svg></a>

<main class="center-card center-card--otp">
  <h1>OTP verification</h1>
  <p class="otp-text">Please enter the OTP sent to your registered email/<br>phone number to complete your verification</p>

  <?php if ($resendMessage): ?>
    <p class="banner banner--ok" role="status"><?= htmlspecialchars($resendMessage) ?></p>
  <?php endif; ?>

  <form method="post" action="verify-otp.php" novalidate>
    <span id="otp-label" class="sr-only">4-digit verification code</span>
    <div class="otp-group" role="group" aria-labelledby="otp-label">
      <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" name="otp1" class="otp-box" aria-label="Digit 1" autocomplete="off"<?= !empty($errors['otp']) ? ' aria-invalid="true"' : '' ?>>
      <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" name="otp2" class="otp-box" aria-label="Digit 2" autocomplete="off"<?= !empty($errors['otp']) ? ' aria-invalid="true"' : '' ?>>
      <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" name="otp3" class="otp-box" aria-label="Digit 3" autocomplete="off"<?= !empty($errors['otp']) ? ' aria-invalid="true"' : '' ?>>
      <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" name="otp4" class="otp-box" aria-label="Digit 4" autocomplete="off"<?= !empty($errors['otp']) ? ' aria-invalid="true"' : '' ?>>
    </div>
    <?php if (!empty($errors['otp'])): ?>
      <span class="error" role="alert"><?= htmlspecialchars($errors['otp']) ?></span>
    <?php endif; ?>

    <div class="otp-meta">
      <span>Remaining time: <strong class="time" id="countdown">10:00</strong></span>
      <span>Didn't get the code? <button type="submit" form="resend-form" name="resend" value="1" class="link-btn">Resend</button></span>
    </div>

    <button type="submit" class="btn btn-primary btn-wide">Reset Password</button>
  </form>

  <!-- separate form so "Resend" works without touching the code boxes -->
  <form id="resend-form" method="post" action="verify-otp.php"></form>

  <a href="login.php" class="btn btn-wide btn-outline-green">Cancel</a>
</main>

<script>
  // Auto-advance between boxes. Only a convenience: the form works (and the
  // server validates) without JavaScript.
  const boxes = document.querySelectorAll('.otp-box');
  boxes.forEach((box, i) => {
    box.addEventListener('input', () => {
      box.value = box.value.replace(/[^0-9]/g, '').slice(0, 1);
      if (box.value && boxes[i + 1]) boxes[i + 1].focus();
    });
    box.addEventListener('keydown', (e) => {
      if (e.key === 'Backspace' && !box.value && boxes[i - 1]) boxes[i - 1].focus();
    });
    box.addEventListener('paste', (e) => {
      const digits = (e.clipboardData.getData('text') || '').replace(/[^0-9]/g, '').slice(0, boxes.length);
      if (!digits) return;
      e.preventDefault();
      digits.split('').forEach((d, k) => { boxes[k].value = d; });
      boxes[Math.min(digits.length, boxes.length) - 1].focus();
    });
  });

  // Visual countdown only — the server decides the real expiry.
  let seconds = 600;
  const el = document.getElementById('countdown');
  setInterval(() => {
    if (seconds <= 0) return;
    seconds--;
    el.textContent = String(Math.floor(seconds / 60)).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
  }, 1000);
</script>

</body>
</html>
