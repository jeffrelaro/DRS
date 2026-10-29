<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_check()) {
    redirect('requests.php');
}

$id      = (int) ($_POST['id'] ?? 0);
$action  = $_POST['action'] ?? '';
$request = find_request($id);

if (!$request) {
    flash_set('error', 'That request was not found.');
    redirect('requests.php');
}

try {
    switch ($action) {
        case 'verify':
            $done    = admin_verify_request($id);
            $message = 'Request verified.';
            if ($done) {
                notify_request_update($request, 'verified');
            }
            break;

        case 'ready':
            $done    = admin_ready_request($id);
            $message = 'Marked as ready for pickup. The resident can now download the document.';
            if ($done) {
                notify_request_update($request, 'ready');
            }
            break;

        case 'decline':
            $reason = trim($_POST['reason'] ?? '');

            if ($reason === '') {
                flash_set('error', 'Please write a reason for declining.');
                redirect('request-view.php?id=' . $id);
            }
            if (mb_strlen($reason) > 255) {
                flash_set('error', 'The reason must be 255 characters or fewer.');
                redirect('request-view.php?id=' . $id);
            }

            $done    = admin_decline_request($id, $reason);
            $message = 'Request declined.';
            if ($done) {
                notify_request_update($request, 'declined', $reason);
            }
            break;

        case 'delete':
            $done = admin_delete_request($id);
            flash_set($done ? 'success' : 'error', $done ? 'Request deleted.' : 'The request could not be deleted.');
            redirect('requests.php');

        default:
            $done    = false;
            $message = '';
    }
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    $done    = false;
    $message = '';
}

flash_set($done ? 'success' : 'error', $done ? $message : 'That action is not available for this request.');
redirect('request-view.php?id=' . $id);
