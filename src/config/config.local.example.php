<?php
declare(strict_types=1);

// แม่แบบค่าเฉพาะเครื่องสำหรับ XAMPP
// คัดลอกไฟล์นี้เป็น config.local.php (อยู่ใน .gitignore ห้าม commit) แล้วใส่รหัสผ่านจริง
return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'name' => 'circulation_desk',
        'user' => 'cdesk',
        'pass' => 'change_me_app',
    ],
];
