<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$filters = [
    ''            => 'All Requests',
    'In Progress' => 'In Progress',
    'Completed'   => 'Completed',
    'Declined'    => 'Declined',
];

$status = $_GET['status'] ?? '';
if (!array_key_exists($status, $filters)) {
    $status = '';
}
$search = trim($_GET['q'] ?? '');

$rows = admin_requests($status, $search);

$pageTitle = 'Requests';
$active    = 'requests';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Document Requests</h1>
<p class="lead">Review, verify, approve or decline resident requests.</p>

<div class="tabs">
  <?php foreach ($filters as $value => $label): ?>
    <?php
    $query = array_filter(['status' => $value, 'q' => $search], fn($item) => $item !== '');
    ?>
    <a class="tab <?= $status === $value ? 'active' : '' ?>"
       href="requests.php<?= $query ? '?' . http_build_query($query) : '' ?>"><?= e($label) ?></a>
  <?php endforeach; ?>
</div>

<form class="toolbar" method="get" action="requests.php">
  <input type="hidden" name="status" value="<?= e($status) ?>">
  <label class="search">
    <?= icon('search', 20) ?>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by reference no., resident or document..." maxlength="60">
  </label>
  <button type="submit" class="btn">Search</button>
</form>

<section class="panel">
  <?php
  $emptyMessage = 'No requests match your filters.';
  require __DIR__ . '/../includes/admin_request_table.php';
  ?>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
