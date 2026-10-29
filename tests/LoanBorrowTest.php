<?php
declare(strict_types=1);

// R-05 ยืมหนังสือ

test('AT-19 R-05 ยืมสำเร็จ: due_date = +7 วัน copies_available ลดลง 1', function (PDO $pdo) {
    $today = new DateTimeImmutable('2026-10-29');
    $r = loan_service($pdo)->borrow(1, 2, 1, $today);
    assert_true($r['ok'], 'ยืมไม่สำเร็จ: ' . ($r['error'] ?? ''));
    assert_same('2026-11-05', $r['due_date']);

    $stmt = $pdo->prepare('SELECT * FROM loans WHERE loan_id = ?');
    $stmt->execute([$r['loan_id']]);
    $loan = $stmt->fetch();
    assert_same('2026-10-29', $loan['borrowed_at']);
    assert_same('2026-11-05', $loan['due_date']);
    assert_same('borrowed', $loan['status']);
    assert_same(1, (int) $loan['staff_id']);
    assert_same(null, $loan['returned_at']);
    assert_same(2, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 2'));
});

test('AT-20 R-05 สมาชิกถูกระงับยืมไม่ได้ (BR-04)', function (PDO $pdo) {
    $r = loan_service($pdo)->borrow(3, 2, 1);
    assert_true(!$r['ok']);
    assert_same('member_suspended', $r['code']);
    assert_same(0, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM loans'));
    assert_same(3, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 2'));
});

test('AT-21 R-05 ยืมครบ 3 เล่มแล้วยืมเล่มที่ 4 ไม่ได้ (BR-02)', function (PDO $pdo) {
    $svc = loan_service($pdo);
    foreach ([1, 2, 3] as $bookId) {
        assert_true($svc->borrow(1, $bookId, 1)['ok'], "ยืมเล่ม $bookId ไม่สำเร็จ");
    }
    $r = $svc->borrow(1, 5, 1);
    assert_true(!$r['ok']);
    assert_same('limit_reached', $r['code']);
    assert_same('ยืมไม่ได้: สมาชิกยืมค้างครบ 3 เล่มแล้ว', $r['error']);
    assert_same(1, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 5'));
});

test('AT-22 R-05 หนังสือหมดยืมไม่ได้', function (PDO $pdo) {
    $svc = loan_service($pdo);
    assert_true($svc->borrow(1, 4, 1)['ok']); // Learning SQL มี 1 เล่ม
    $r = $svc->borrow(2, 4, 1);
    assert_true(!$r['ok']);
    assert_same('out_of_stock', $r['code']);
    assert_same(0, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 4'));
    assert_same(1, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM loans WHERE book_id = 4'));
});

test('AT-23 R-05 สมาชิกหรือหนังสือที่ไม่มีอยู่ถูกปฏิเสธ', function (PDO $pdo) {
    assert_same('member_not_found', loan_service($pdo)->borrow(999, 1, 1)['code']);
    assert_same('book_not_found', loan_service($pdo)->borrow(1, 999, 1)['code']);
    assert_true(!$pdo->inTransaction(), 'ต้องไม่มี transaction ค้าง');
});

test('AT-24 R-05 แถวหนังสือถูกล็อกด้วย FOR UPDATE ระหว่างยืม (กันยืมพร้อมกัน)', function (PDO $pdo) {
    // การเชื่อมต่อที่ 2 จำลองเจ้าหน้าที่อีกคนที่กำลังยืมหนังสือเล่มเดียวกันค้างอยู่ใน transaction
    $other = db_connect(config('db'));
    $other->beginTransaction();
    $other->query('SELECT copies_available FROM books WHERE book_id = 4 FOR UPDATE')->fetch();

    $pdo->exec('SET SESSION innodb_lock_wait_timeout = 1');
    $blocked = false;
    try {
        loan_service($pdo)->borrow(1, 4, 1);
    } catch (PDOException $e) {
        $blocked = ($e->errorInfo[1] ?? 0) === 1205; // Lock wait timeout exceeded
    } finally {
        $other->rollBack();
        $pdo->exec('SET SESSION innodb_lock_wait_timeout = 50');
    }
    assert_true($blocked, 'การยืมต้องรอล็อกแถวหนังสือ');
    assert_true(!$pdo->inTransaction(), 'ต้อง rollback เมื่อเกิดข้อผิดพลาด');
    assert_same(0, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM loans'));
    assert_true(loan_service($pdo)->borrow(1, 4, 1)['ok'], 'หลังปลดล็อกต้องยืมได้');
});
