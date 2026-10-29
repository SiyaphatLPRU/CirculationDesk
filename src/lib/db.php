<?php
declare(strict_types=1);

/**
 * คืน PDO ตัวเดียวใช้ทั้งคำขอ (singleton)
 * ตั้ง ERRMODE_EXCEPTION, utf8mb4 และปิด emulate prepares
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $pdo = db_connect(config('db'));
    }
    return $pdo;
}

function db_connect(array $c): PDO
{
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $c['host'], $c['port'], $c['name']);
    return new PDO($dsn, $c['user'], $c['pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
}
