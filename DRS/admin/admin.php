<?php
require_once "../config.php";

$full_name = "Mary Jane Pinote";
$email     = "admin@catarman-cordova.gov.ph";
$password  = "admin@12345";

$check = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
$check->bind_param("s", $email);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    die("An admin with that email already exists. Delete the old row in phpMyAdmin first, then reload this page.");
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO admin_users (full_name, email, password) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $full_name, $email, $hashed);

if ($stmt->execute()) {
    echo "Admin created. Email: " . htmlspecialchars($email) . " / Password: " . htmlspecialchars($password);
} else {
    echo "Something went wrong: " . $conn->error;
}