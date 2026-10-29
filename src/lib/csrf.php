<?php
declare(strict_types=1);

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** ช่อง hidden สำหรับใส่ในทุกฟอร์ม POST */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** หยุดทำงานถ้า token ไม่ตรง */
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $sent)) {
        http_response_code(400);
        render('error', ['message' => 'คำขอไม่ถูกต้องหรือหมดอายุ กรุณาโหลดหน้าใหม่แล้วลองอีกครั้ง']);
        exit;
    }
}
