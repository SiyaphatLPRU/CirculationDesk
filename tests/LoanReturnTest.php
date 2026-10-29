<?php
declare(strict_types=1);

// R-06 คืนหนังสือ (ยืม 2026-10-01 กำหนดคืน 2026-10-08)

function borrow_on(PDO $pdo, int $memberId, int $bookId, string $date): int
{
    $r = loan_service($pdo)->borrow($memberId, $bookId, 1, new DateTimeImmutable($date));
    assert_true($r['ok'], 'เตรียมข้อมูลยืมไม่สำเร็จ');
    return $r['loan_id'];
}

function loan_row(PDO $pdo, int $loanId): array
{
    $stmt = $pdo->prepare('SELECT * FROM loans WHERE loan_id = ?');
    $stmt->execute([$loanId]);
    return $stmt->fetch();
}

test('AT-25 R-06 คืนตรงเวลา ค่าปรับ 0 และ copies_available เพิ่มกลับ', function (PDO $pdo) {
    $id = borrow_on($pdo, 1, 2, '2026-10-01');
    assert_same(2, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 2'));

    $r = loan_service($pdo)->return($id, new DateTimeImmutable('2026-10-08')); // วันครบกำหนดพอดี
    assert_true($r['ok']);
    assert_same(0, $r['fine']);
    assert_same(0, $r['days_late']);
    $loan = loan_row($pdo, $id);
    assert_same('returned', $loan['status']);
    assert_same('2026-10-08', $loan['returned_at']);
    assert_same('0.00', $loan['fine_amount']);
    assert_same(3, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 2'));
});

test('AT-26 R-06 คืนเกินกำหนด 1 วัน ค่าปรับ 5 บาท', function (PDO $pdo) {
    $id = borrow_on($pdo, 1, 2, '2026-10-01');
    $r = loan_service($pdo)->return($id, new DateTimeImmutable('2026-10-09'));
    assert_same(1, $r['days_late']);
    assert_same(5, $r['fine']);
    assert_same('5.00', loan_row($pdo, $id)['fine_amount']);
});

test('AT-27 R-06 คืนเกินกำหนด 10 วัน ค่าปรับ 50 บาท', function (PDO $pdo) {
    $id = borrow_on($pdo, 1, 2, '2026-10-01');
    $r = loan_service($pdo)->return($id, new DateTimeImmutable('2026-10-18'));
    assert_same(10, $r['days_late']);
    assert_same(50, $r['fine']);
    assert_same('50.00', loan_row($pdo, $id)['fine_amount']);
});

test('AT-28 R-06 คืนซ้ำถูกปฏิเสธ และจำนวนคงเหลือไม่เพิ่มซ้ำ', function (PDO $pdo) {
    $id = borrow_on($pdo, 1, 4, '2026-10-01');
    $svc = loan_service($pdo);
    assert_true($svc->return($id, new DateTimeImmutable('2026-10-05'))['ok']);
    $r = $svc->return($id, new DateTimeImmutable('2026-10-20'));
    assert_true(!$r['ok']);
    assert_same('already_returned', $r['code']);
    assert_same(1, (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 4'));
    assert_same('0.00', loan_row($pdo, $id)['fine_amount'], 'ค่าปรับเดิมต้องไม่ถูกเขียนทับ');
    assert_same('loan_not_found', $svc->return(999999)['code']);
});

test('AT-29 R-06 รายการยืมค้างของสมาชิกแสดงเฉพาะที่ยังไม่คืน', function (PDO $pdo) {
    $a = borrow_on($pdo, 1, 1, '2026-10-01');
    borrow_on($pdo, 1, 2, '2026-10-02');
    borrow_on($pdo, 2, 3, '2026-10-02');
    loan_service($pdo)->return($a, new DateTimeImmutable('2026-10-03'));
    $titles = array_column(loan_service($pdo)->activeLoansForMember(1), 'title');
    assert_same(['Pro Git'], $titles);
});
