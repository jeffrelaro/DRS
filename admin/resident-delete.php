<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    redirect('residents.php');
}

try {
    $deleted = delete_resident((int) ($_POST['id'] ?? 0));
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $deleted = false;
}

flash_set($deleted ? 'success' : 'error', $deleted ? 'Resident deleted.' : 'The resident could not be deleted.');
redirect('residents.php');
