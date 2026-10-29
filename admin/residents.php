<?php
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$search    = trim($_GET['q'] ?? '');
$residents = admin_residents($search);

$pageTitle = 'Residents';
$active    = 'residents';
require __DIR__ . '/../includes/admin_header.php';
?>
<h1>Residents</h1>
<p class="lead">Registered resident accounts.</p>

<form class="toolbar" method="get" action="residents.php">
  <label class="search">
    <?= icon('search', 20) ?>
    <input type="text" name="q" value="<?= e($search) ?>" placeholder="Search by name, email or address..." maxlength="60">
  </label>
  <button type="submit" class="btn">Search</button>
</form>

<section class="panel">
  <?php if (!$residents): ?>
    <div class="empty">No residents found.</div>
  <?php else: ?>
    <div class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>NAME</th>
            <th>EMAIL</th>
            <th>PHONE</th>
            <th>ADDRESS</th>
            <th>REQUESTS</th>
            <th>JOINED</th>
            <th>ACTION</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($residents as $resident): ?>
            <tr>
              <td><strong><?= e($resident['first_name'] . ' ' . $resident['last_name']) ?></strong></td>
              <td><?= e($resident['email']) ?></td>
              <td><?= e($resident['phone']) ?></td>
              <td><?= e($resident['address']) ?></td>
              <td><?= (int) $resident['request_count'] ?></td>
              <td><?= e(format_date($resident['created_at'])) ?></td>
              <td>
                <form method="post" action="resident-delete.php"
                      data-confirm="Delete this resident and all of their requests? This cannot be undone.">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id" value="<?= (int) $resident['id'] ?>">
                  <button type="submit" class="link-danger">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</section>
<?php require __DIR__ . '/../includes/admin_footer.php'; ?>
