<?php
declare(strict_types=1);

// SCI-009: ค่าตั้งระบบ อ่านจากตัวแปรสภาพแวดล้อม (Docker)
return [
    'db' => [
        'host' => getenv('DB_HOST') ?: 'db',
        'port' => (int) (getenv('DB_PORT') ?: 3306),
        'name' => getenv('DB_NAME') ?: 'circulation_desk',
        'user' => getenv('DB_USER') ?: 'cdesk',
        'pass' => getenv('DB_PASS') ?: '',
    ],
];
