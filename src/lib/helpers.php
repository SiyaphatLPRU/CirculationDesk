<?php
declare(strict_types=1);

/** อ่านค่าตั้งระบบจาก config.php (โหลดครั้งเดียว) */
function config(string $section): array
{
    static $config = null;
    if ($config === null) {
        $config = require APP_ROOT . '/config/config.php';
    }
    return $config[$section] ?? [];
}

/** escape ค่าก่อนแสดงผลใน HTML */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * base path ของ src/public ใน URL เช่น '' (VirtualHost, php -S)
 * หรือ '/Circulation-Desk/src/public' (เปิดผ่าน htdocs ของ XAMPP)
 */
function base_path(): string
{
    static $base = null;
    if ($base === null) {
        $publicDir = realpath(APP_ROOT . '/public');
        $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: '';
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $base = '';
        if ($publicDir !== false && $script !== '' && str_starts_with(strtolower($script), strtolower($publicDir))) {
            $relative = str_replace('\\', '/', substr($script, strlen($publicDir)));
            $base = substr($scriptName, 0, max(0, strlen($scriptName) - strlen($relative)));
        }
        $base = rtrim($base, '/');
    }
    return $base;
}

/** สร้าง URL ภายในระบบ เช่น url('books/index.php') */
function url(string $path = '', array $query = []): string
{
    $url = base_path() . '/' . ltrim($path, '/');
    $query = array_filter($query, static fn($v) => $v !== null && $v !== '');
    return $query ? $url . '?' . http_build_query($query) : $url;
}

function redirect(string $path, array $query = []): never
{
    header('Location: ' . url($path, $query));
    exit;
}

/** เก็บข้อความแจ้งเตือนไว้แสดงในหน้าถัดไป: type = success | error | info */
function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

/** แสดง view จาก src/views เช่น render('layout/header', ['title' => '...']) */
function render(string $view, array $data = []): void
{
    extract($data, EXTR_SKIP);
    require APP_ROOT . '/views/' . $view . '.php';
}

/** ยอมรับเฉพาะ POST ที่มี CSRF token ถูกต้อง */
function require_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method Not Allowed');
    }
    csrf_check();
}

function input(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function input_int(string $key): ?int
{
    $value = filter_var(input($key), FILTER_VALIDATE_INT);
    return $value === false ? null : $value;
}

/** ตรวจรูปแบบวันที่ YYYY-MM-DD */
function valid_date(string $value): bool
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value;
}

function today(): DateTimeImmutable
{
    return new DateTimeImmutable('today');
}

/** แสดงวันที่แบบไทย dd/mm/yyyy (พ.ศ.) */
function thai_date(?string $date): string
{
    if ($date === null || $date === '') {
        return '-';
    }
    $d = new DateTimeImmutable($date);
    return $d->format('d/m/') . ((int) $d->format('Y') + 543);
}

function money(string|float|int $amount): string
{
    return number_format((float) $amount, 2);
}
