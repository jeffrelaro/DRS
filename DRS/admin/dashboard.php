<?php
require_once "../config.php";
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../assets/admin-style.css">
</head>
<body style="display:flex; align-items:center; justify-content:center; min-height:100vh;">
 
<div style="background:#fff; padding:40px; border-radius:16px; text-align:center; max-width:400px;">
  <h2>Welcome, <?= htmlspecialchars($_SESSION['admin_full_name']) ?>!</h2>
  <p style="color:#6b7770;">You are signed in to the Admin Panel.</p>
  <a href="logout.php" style="color:#c0392b; font-weight:bold; text-decoration:none;">Logout</a>
</div>
 
</body>
</html>