<?php
declare(strict_types=1);

// หน้าตรวจสภาพแวดล้อม (baseline) - ยืนยันว่า PHP และฐานข้อมูลพร้อมใช้งาน
$config = require __DIR__ . '/../config/config.php';
$db = $config['db'];

$dbStatus = 'ยังไม่ได้ตรวจ';
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']),
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $dbStatus = 'เชื่อมต่อสำเร็จ';
} catch (PDOException $e) {
    error_log($e->getMessage());
    $dbStatus = 'เชื่อมต่อไม่สำเร็จ';
}
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><title>Circulation Desk - ตรวจสภาพแวดล้อม</title></head>
<body>
  <h1>Circulation Desk</h1>
  <ul>
    <li>PHP: <?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></li>
    <li>pdo_mysql: <?= extension_loaded('pdo_mysql') ? 'พร้อม' : 'ไม่พบ' ?></li>
    <li>ฐานข้อมูล: <?= htmlspecialchars($dbStatus, ENT_QUOTES, 'UTF-8') ?></li>
  </ul>
</body>
</html>
