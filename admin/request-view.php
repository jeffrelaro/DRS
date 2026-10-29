<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$request = find_request((int) ($_GET['id'] ?? 0));

if (!$request) {
    flash_set('error', 'That request was not found.');
    redirect('requests.php');
}

$id         = (int) $request['id'];
$inProgress = $request['status'] === 'In Progress';
$verified   = $request['verified_at'] !== null;

$steps = [
    ['Request submitted', $request['created_at'], 'Submitted'],
    ['Verified by barangay staff', $request['verified_at'], 'Pending'],
    ['Ready for pickup', $request['ready_at'], 'Pending'],
];

$pageTitle = $request['document_name'];
$active    = 'requests';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1><?= e($request['document_name']) ?></h1>
<p class="lead">
  Reference <?= e((string) $request['reference_no']) ?> &middot; Requested <?= e(format_date($request['created_at'])) ?>
</p>

<a class="back" href="requests.php" aria-label="Back to requests"><?= icon('back', 28) ?></a>

<div class="detail-grid">
  <section class="card">
    <div class="card-head">
      <h2><?= $request['status'] === 'Declined' ? 'Status' : 'Status Timeline' ?></h2>
      <span class="badge <?= e(status_class($request['status'])) ?>"><?= e($request['status']) ?></span>
    </div>

    <?php if ($request['status'] === 'Declined'): ?>
      <div class="reason"><strong>Reason:</strong> <?= e((string) $request['decline_reason']) ?></div>
    <?php else: ?>
      <ol class="timeline">
        <?php foreach ($steps as [$label, $when, $pending]): ?>
          <li class="<?= $when ? 'done' : '' ?>">
            <span class="dot"><?= $when ? icon('check', 16) : '' ?></span>
            <div>
              <strong><?= e($label) ?></strong>
              <small><?= $when ? e(format_datetime($when)) : e($pending) ?></small>
            </div>
          </li>
        <?php endforeach; ?>
      </ol>
    <?php endif; ?>
  </section>

  <section class="card">
    <ul class="facts">
      <li><span>Resident</span><strong><?= e($request['first_name'] . ' ' . $request['last_name']) ?></strong></li>
      <li><span>Email</span><strong><?= e($request['email']) ?></strong></li>
      <li><span>Phone</span><strong><?= e($request['phone']) ?></strong></li>
      <li><span>Address</span><strong><?= e($request['address']) ?></strong></li>
      <li><span>Document Type</span><strong><?= e($request['document_name']) ?></strong></li>
      <li><span>Purpose</span><strong><?= e($request['purpose']) ?></strong></li>
      <li><span>Copies</span><strong><?= (int) $request['copies'] ?></strong></li>
      <?php if ($request['notes'] !== ''): ?>
        <li><span>Notes</span><strong><?= e($request['notes']) ?></strong></li>
      <?php endif; ?>
    </ul>
  </section>
</div>

<div class="actions">
  <?php if ($inProgress && !$verified): ?>
    <form method="post" action="request-action.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="action" value="verify">
      <button type="submit" class="btn btn-wide">Verify request</button>
    </form>
  <?php endif; ?>

  <?php if ($inProgress && $verified): ?>
    <form method="post" action="request-action.php">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= $id ?>">
      <input type="hidden" name="action" value="ready">
      <button type="submit" class="btn btn-wide">Mark ready for pickup</button>
    </form>
  <?php endif; ?>

  <form method="post" action="request-action.php" data-confirm="Delete this request permanently? This cannot be undone.">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="action" value="delete">
    <button type="submit" class="btn btn-danger">Delete request</button>
  </form>
</div>

<?php if ($inProgress): ?>
  <form class="decline-form" method="post" action="request-action.php">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= $id ?>">
    <input type="hidden" name="action" value="decline">
    <h3>Decline this request</h3>
    <textarea name="reason" maxlength="255" placeholder="Tell the resident why (required)" required></textarea>
    <div class="actions"><button type="submit" class="btn btn-danger">Decline request</button></div>
  </form>
<?php endif; ?>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
