<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$counts = admin_counts();
$rows   = array_slice(admin_requests(), 0, 5);

$pageTitle = 'Dashboard';
$active    = 'dashboard';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Dashboard</h1>
<p class="lead">Overview of document requests and residents.</p>

<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-top"><?= icon('clock', 40) ?><strong><?= $counts['awaiting'] ?></strong></div>
    <h3>Needs Verification</h3>
    <p>New requests waiting for staff</p>
    <a href="requests.php?status=In+Progress">Review <?= icon('arrow', 18) ?></a>
  </div>

  <div class="stat-card">
    <div class="stat-top"><?= icon('file', 40) ?><strong><?= $counts['In Progress'] ?></strong></div>
    <h3>In Progress</h3>
    <p>Requests being processed</p>
    <a href="requests.php?status=In+Progress">View All <?= icon('arrow', 18) ?></a>
  </div>

  <div class="stat-card">
    <div class="stat-top"><?= icon('check', 40) ?><strong><?= $counts['Completed'] ?></strong></div>
    <h3>Completed</h3>
    <p>Ready for pickup or released</p>
    <a href="requests.php?status=Completed">View All <?= icon('arrow', 18) ?></a>
  </div>

  <div class="stat-card">
    <div class="stat-top"><?= icon('users', 40) ?><strong><?= $counts['residents'] ?></strong></div>
    <h3>Residents</h3>
    <p>Registered accounts</p>
    <a href="residents.php">View All <?= icon('arrow', 18) ?></a>
  </div>
</div>

<section class="panel">
  <div class="panel-head">
    <h2>Latest Requests</h2>
    <a href="requests.php">View All</a>
  </div>
  <?php
  $emptyMessage = 'No requests have been submitted yet.';
  require __DIR__ . '/../includes/admin_request_table.php';
  ?>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
