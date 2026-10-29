<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Run this from the command line.');
}

require_once __DIR__ . '/../config/config.php';

function ask(string $question): string
{
    echo $question;
    return trim((string) fgets(STDIN));
}

$name  = $argv[1] ?? ask('Full name: ');
$email = strtolower($argv[2] ?? ask('Admin email: '));

if ($name === '' || strlen($name) > 100) {
    exit("Name is required (max 100 characters).\n");
}
if (strlen($email) > 100 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
    exit("That is not a valid email address.\n");
}

$password = ask('Password (8+ characters, letters and numbers; it will be visible as you type): ');
$repeat   = ask('Repeat password: ');

if ($password !== $repeat) {
    exit("Passwords do not match.\n");
}
if (strlen($password) < 8 || strlen($password) > 72
    || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
    exit("Password must be 8-72 characters with at least one letter and one number.\n");
}

try {
    $pdo = db();

    $check = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        exit("An admin with that email already exists.\n");
    }

    $pdo->prepare('INSERT INTO admins (full_name, email, password_hash) VALUES (?, ?, ?)')
        ->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT)]);

    echo "Admin created. Sign in at http://localhost/DRS/admin/login.php\n";
} catch (PDOException $ex) {
    exit('Database error: ' . $ex->getMessage() . "\n");
}
