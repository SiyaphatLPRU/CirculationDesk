<?php
declare(strict_types=1);

// หน้าตรวจสภาพแวดล้อม (ย้ายมาจาก index.php เดิม) - ไม่ต้องเข้าสู่ระบบ
require __DIR__ . '/../lib/bootstrap.php';

$checks = [
    'PHP'         => PHP_VERSION,
    'pdo_mysql'   => extension_loaded('pdo_mysql') ? 'พร้อม' : 'ไม่พบ',
    'เขตเวลา'     => date_default_timezone_get() . ' (' . date('Y-m-d H:i') . ')',
    'แหล่งค่าตั้ง' => getenv('DB_HOST') !== false ? 'ตัวแปรสภาพแวดล้อม' : (is_file(APP_ROOT . '/config/config.local.php') ? 'config.local.php' : 'ค่าเริ่มต้น'),
];

$dbOk = false;
try {
    $pdo = db();
    $checks['ฐานข้อมูล'] = 'เชื่อมต่อสำเร็จ (' . config('db')['name'] . ')';
    $checks['รุ่นฐานข้อมูล'] = (string) $pdo->query('SELECT VERSION()')->fetchColumn();
    $checks['จำนวนตาราง'] = (string) count($pdo->query('SHOW TABLES')->fetchAll());
    $dbOk = true;
} catch (PDOException $e) {
    error_log('health: ' . $e->getMessage());
    $checks['ฐานข้อมูล'] = 'เชื่อมต่อไม่สำเร็จ (ดูรายละเอียดใน error log)';
}

http_response_code($dbOk ? 200 : 503);
render('layout/header', ['title' => 'ตรวจสภาพแวดล้อม']);
?>
<div class="card">
  <h1>ตรวจสภาพแวดล้อม</h1>
  <table class="table">
    <?php foreach ($checks as $label => $value): ?>
      <tr><th><?= e($label) ?></th><td><?= e($value) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <p><span class="badge <?= $dbOk ? 'badge-ok' : 'badge-no' ?>"><?= $dbOk ? 'พร้อมใช้งาน' : 'ยังไม่พร้อม' ?></span></p>
  <p><a href="<?= e(url('login.php')) ?>">ไปหน้าเข้าสู่ระบบ</a></p>
</div>
<?php render('layout/footer'); ?>
