<?php
require_once "config.php";
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — DRS</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<main class="center-card center-card--flow">
  <h1>Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?>!</h1>
  <p class="subtitle">You are signed in to the Resident Portal.</p>
  <a href="logout.php" class="cancel-link">Logout</a>
</main>

</body>
</html>
