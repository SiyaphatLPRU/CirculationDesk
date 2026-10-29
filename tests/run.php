<?php
declare(strict_types=1);

/**
 * ตัวรันทดสอบ CLI อย่างง่าย (ไม่ใช้ PHPUnit)
 *   php tests/run.php            รันทุกไฟล์ tests/*Test.php
 *   php tests/run.php Loan       รันเฉพาะไฟล์ที่ชื่อมีคำว่า Loan
 *
 * ใช้ฐานข้อมูล circulation_desk_test เท่านั้น ล้างและใส่ข้อมูลเริ่มต้นใหม่ก่อนทุกกรณี
 */

if (PHP_SAPI !== 'cli') {
    exit('CLI only');
}

// บังคับใช้ฐานข้อมูลทดสอบ (ค่าอื่น เช่น รหัสผ่าน อ่านจาก env หรือ config.local.php ตามปกติ)
putenv('DB_NAME=' . (getenv('TEST_DB_NAME') ?: 'circulation_desk_test'));

require __DIR__ . '/../src/lib/bootstrap.php';
require __DIR__ . '/lib.php';

try {
    $pdo = db();
} catch (PDOException $e) {
    fwrite(STDERR, "เชื่อมต่อฐานข้อมูลทดสอบ '" . config('db')['name'] . "' ไม่ได้: " . $e->getMessage() . PHP_EOL);
    fwrite(STDERR, "ถ้ายังไม่มีฐานข้อมูลทดสอบ ให้รัน tests/create-test-db.sql ด้วยบัญชี root ใน phpMyAdmin ก่อน" . PHP_EOL);
    exit(2);
}

$dbName = (string) $pdo->query('SELECT DATABASE()')->fetchColumn();
if (!str_ends_with($dbName, '_test')) {
    fwrite(STDERR, "ปฏิเสธการรัน: ฐานข้อมูล '$dbName' ไม่ใช่ฐานข้อมูลทดสอบ (ชื่อต้องลงท้ายด้วย _test)" . PHP_EOL);
    exit(2);
}

$filter = $argv[1] ?? '';
foreach (glob(__DIR__ . '/*Test.php') ?: [] as $file) {
    if ($filter === '' || stripos(basename($file), $filter) !== false) {
        require $file;
    }
}

exit(run_tests($pdo) ? 0 : 1);
