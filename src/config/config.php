<?php
declare(strict_types=1);

// SCI-009: ค่าตั้งระบบ
// ลำดับการอ่านค่า: ตัวแปรสภาพแวดล้อม (Docker) -> config.local.php (XAMPP) -> ค่าเริ่มต้น

$local = [];
$localFile = __DIR__ . '/config.local.php';
if (is_file($localFile)) {
    $local = require $localFile;
}

$setting = static function (string $env, string $key, string|int $default) use ($local): string|int {
    $value = getenv($env);
    if ($value !== false && $value !== '') {
        return $value;
    }
    return $local['db'][$key] ?? $default;
};

return [
    'db' => [
        'host' => (string) $setting('DB_HOST', 'host', '127.0.0.1'),
        'port' => (int) $setting('DB_PORT', 'port', 3306),
        'name' => (string) $setting('DB_NAME', 'name', 'circulation_desk'),
        'user' => (string) $setting('DB_USER', 'user', 'cdesk'),
        'pass' => (string) $setting('DB_PASS', 'pass', ''),
    ],

    // กฎธุรกิจ (BR-01..BR-04) - แก้ที่นี่ที่เดียว
    'loan' => [
        'days'         => 7, // BR-01 ระยะเวลายืม (วัน)
        'max_active'   => 3, // BR-02 จำนวนเล่มที่ยืมค้างได้สูงสุดต่อสมาชิก
        'fine_per_day' => 5, // BR-03 ค่าปรับ (บาท/วัน/เล่ม)
        // BR-04 สมาชิก suspended ยืมไม่ได้ (ตรวจใน LoanService::borrow)
    ],

    'app' => [
        'name'     => 'Circulation Desk',
        'timezone' => 'Asia/Bangkok',
        'per_page' => 10,
    ],
];
