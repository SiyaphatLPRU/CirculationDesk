<?php
declare(strict_types=1);

/** @var string $message */
render('layout/header', ['title' => 'ข้อผิดพลาด']);
?>
<div class="card">
  <h1>เกิดข้อผิดพลาด</h1>
  <p><?= e($message) ?></p>
  <p><a class="btn" href="<?= e(url('index.php')) ?>">กลับหน้าแรก</a></p>
</div>
<?php render('layout/footer'); ?>
