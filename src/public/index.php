<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';

$staff = require_login();

render('layout/header', ['title' => 'แดชบอร์ด']);
?>
<h1>แดชบอร์ด</h1>
<p>สวัสดี <?= e($staff['full_name']) ?> · วันนี้ <?= e(thai_date(today()->format('Y-m-d'))) ?></p>
<?php render('layout/footer'); ?>
