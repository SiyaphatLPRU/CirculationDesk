<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';

$staff = require_login();
$stats = (new ReportService(db(), config('loan')))->dashboard(today());

render('layout/header', ['title' => 'แดชบอร์ด']);
?>
<h1>แดชบอร์ด</h1>
<p>สวัสดี <?= e($staff['full_name']) ?> · วันนี้ <?= e(thai_date(today()->format('Y-m-d'))) ?></p>

<div class="stats">
  <a class="stat<?= $stats['overdue'] > 0 ? ' alert-stat' : '' ?>" href="<?= e(url('loans/overdue.php')) ?>">
    <span class="value"><?= e($stats['overdue']) ?></span>
    <span class="label">รายการเกินกำหนด</span>
  </a>
  <div class="stat">
    <span class="value"><?= e($stats['active_loans']) ?></span>
    <span class="label">กำลังถูกยืม</span>
  </div>
  <a class="stat" href="<?= e(url('books/index.php')) ?>">
    <span class="value"><?= e($stats['books']) ?></span>
    <span class="label">รายการหนังสือ</span>
  </a>
  <a class="stat" href="<?= e(url('members/index.php')) ?>">
    <span class="value"><?= e($stats['active_members']) ?></span>
    <span class="label">สมาชิกที่ใช้งาน</span>
  </a>
</div>

<div class="quick">
  <a class="btn btn-primary" href="<?= e(url('loans/borrow.php')) ?>">ยืมหนังสือ</a>
  <a class="btn btn-primary" href="<?= e(url('loans/return.php')) ?>">คืนหนังสือ</a>
  <a class="btn" href="<?= e(url('books/index.php')) ?>">ค้นหาหนังสือ</a>
</div>
<?php render('layout/footer'); ?>
