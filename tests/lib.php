<?php
declare(strict_types=1);

// ฟังก์ชันช่วยทดสอบ: ลงทะเบียนกรณี, assert, ล้าง/ใส่ข้อมูลเริ่มต้น

final class AssertionFailed extends Exception
{
}

$GLOBALS['tests'] = [];

function test(string $name, callable $fn): void
{
    $GLOBALS['tests'][] = [$name, $fn];
}

function assert_true(bool $condition, string $message = 'ค่าไม่เป็นจริง'): void
{
    if (!$condition) {
        throw new AssertionFailed($message);
    }
}

function assert_same(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected !== $actual) {
        throw new AssertionFailed(trim($message . ' คาดว่า ' . var_export($expected, true) . ' ได้ ' . var_export($actual, true)));
    }
}

/** ล้างข้อมูลทุกตารางตามลำดับ FK แล้วใส่ข้อมูลเริ่มต้น (ใช้แค่ DELETE/INSERT ไม่ต้องมีสิทธิ์ CREATE) */
function reset_db(PDO $pdo): void
{
    foreach (['loans', 'books', 'categories', 'members', 'staff'] as $table) {
        $pdo->exec("DELETE FROM $table");
    }
    $pdo->exec("INSERT INTO staff (staff_id, username, password_hash, full_name, role) VALUES
        (1, 'admin', '" . password_hash('admin1234', PASSWORD_DEFAULT, ['cost' => 4]) . "', 'ผู้ดูแลระบบ', 'admin')");
    $pdo->exec("INSERT INTO categories (category_id, name) VALUES
        (1, 'วิศวกรรมซอฟต์แวร์'), (2, 'ฐานข้อมูล'), (3, 'คณิตศาสตร์และสถิติ')");
    $pdo->exec("INSERT INTO members (member_id, member_code, full_name, phone, email, status) VALUES
        (1, 'M0001', 'สมชาย ใจดี', '0810000001', 'somchai@example.com', 'active'),
        (2, 'M0002', 'สมหญิง รักเรียน', '0810000002', 'somying@example.com', 'active'),
        (3, 'M0003', 'ตัวอย่าง ถูกระงับ', NULL, NULL, 'suspended')");
    $pdo->exec("INSERT INTO books (book_id, isbn, title, author, category_id, copies_total, copies_available) VALUES
        (1, '9780000000011', 'Software Configuration Management Patterns', 'Berczuk', 1, 2, 2),
        (2, '9780000000028', 'Pro Git', 'Chacon and Straub', 1, 3, 3),
        (3, '9780000000035', 'Database System Concepts', 'Silberschatz', 2, 2, 2),
        (4, '9780000000042', 'Learning SQL', 'Beaulieu', 2, 1, 1),
        (5, '9780000000059', 'Statistics for Engineers', 'Sample', 3, 1, 1)");
}

/** จำลองการยืมค้างโดยตรงในฐานข้อมูล (ไม่ผ่าน LoanService) */
function put_on_loan(PDO $pdo, int $bookId, int $memberId = 1, string $borrowedAt = '2026-10-01', string $dueDate = '2026-10-08'): void
{
    $pdo->prepare("INSERT INTO loans (member_id, book_id, staff_id, borrowed_at, due_date, status)
                   VALUES (?, ?, 1, ?, ?, 'borrowed')")->execute([$memberId, $bookId, $borrowedAt, $dueDate]);
    $pdo->prepare('UPDATE books SET copies_available = copies_available - 1 WHERE book_id = ?')->execute([$bookId]);
}

function fetch_value(PDO $pdo, string $sql, array $params = []): mixed
{
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}

function run_tests(PDO $pdo): bool
{
    $passed = 0;
    $failed = 0;
    foreach ($GLOBALS['tests'] as [$name, $fn]) {
        reset_db($pdo);
        try {
            $fn($pdo);
            $passed++;
            echo "[ผ่าน]    $name" . PHP_EOL;
        } catch (Throwable $e) {
            $failed++;
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $detail = $e instanceof AssertionFailed ? $e->getMessage() : get_class($e) . ': ' . $e->getMessage();
            echo "[ไม่ผ่าน] $name" . PHP_EOL . "          -> $detail" . PHP_EOL;
        }
    }
    echo PHP_EOL . "สรุป: ผ่าน $passed, ไม่ผ่าน $failed, ทั้งหมด " . ($passed + $failed) . PHP_EOL;
    return $failed === 0;
}
