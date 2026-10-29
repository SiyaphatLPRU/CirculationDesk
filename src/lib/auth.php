<?php
declare(strict_types=1);

/**
 * ตรวจชื่อผู้ใช้/รหัสผ่าน สำเร็จแล้วเก็บข้อมูลเจ้าหน้าที่ใน session
 * คืน false โดยไม่บอกว่าผิดที่ช่องใด
 */
function login(string $username, string $password): bool
{
    $stmt = db()->prepare('SELECT staff_id, username, password_hash, full_name, role FROM staff WHERE username = ?');
    $stmt->execute([$username]);
    $staff = $stmt->fetch();

    // เรียก password_verify เสมอ เพื่อไม่ให้เวลาตอบสนองบอกได้ว่ามีชื่อผู้ใช้นี้หรือไม่
    $hash = $staff['password_hash'] ?? '$2y$10$1cBixYzU/M1WqP3DYZcb2uvLa8jsal9FutV/n5u3ic4xtxktpyeIq';
    if (!password_verify($password, $hash) || $staff === false) {
        return false;
    }

    session_regenerate_id(true);
    $_SESSION['staff'] = [
        'staff_id'  => (int) $staff['staff_id'],
        'username'  => $staff['username'],
        'full_name' => $staff['full_name'],
        'role'      => $staff['role'],
    ];
    unset($_SESSION['csrf_token']);
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $p['path'],
            'secure'   => $p['secure'],
            'httponly' => $p['httponly'],
            'samesite' => $p['samesite'],
        ]);
    }
    session_destroy();
}

function current_staff(): ?array
{
    return $_SESSION['staff'] ?? null;
}

/** ใช้ที่ต้นทุกหน้า ยกเว้น login.php และ health.php */
function require_login(): array
{
    $staff = current_staff();
    if ($staff === null) {
        redirect('login.php');
    }
    return $staff;
}
