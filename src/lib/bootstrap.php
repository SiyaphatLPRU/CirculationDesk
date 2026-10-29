<?php
declare(strict_types=1);

// โหลดทุกอย่างที่หน้าเว็บต้องใช้: config, การจัดการข้อผิดพลาด, session, helper และบริการ

define('APP_ROOT', dirname(__DIR__));

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';

foreach (glob(APP_ROOT . '/services/*.php') ?: [] as $serviceFile) {
    require_once $serviceFile;
}

date_default_timezone_set(config('app')['timezone']);

// ไม่แสดงข้อผิดพลาดบนหน้าเว็บ บันทึกลง error log แทน
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

if (PHP_SAPI !== 'cli') {
    set_exception_handler(static function (Throwable $e): void {
        error_log((string) $e);
        if (!headers_sent()) {
            http_response_code(500);
        }
        render('error', ['message' => 'ระบบขัดข้อง กรุณาลองใหม่อีกครั้ง']);
    });

    session_name('CDESKSESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    session_start();
}
