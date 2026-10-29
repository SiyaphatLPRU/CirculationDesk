<?php
declare(strict_types=1);

// R-04 ค้นหาและดูสถานะหนังสือ

function titles(array $result): array
{
    return array_column($result['rows'], 'title');
}

test('AT-15 R-04 ค้นด้วยคำเดียวจากชื่อ ผู้แต่ง และ ISBN', function (PDO $pdo) {
    $svc = new BookService($pdo);
    assert_same(['Pro Git'], titles($svc->search('git')), 'ค้นจากชื่อ');
    assert_same(['Database System Concepts'], titles($svc->search('silber')), 'ค้นจากผู้แต่ง');
    assert_same(['Learning SQL'], titles($svc->search('0000000042')), 'ค้นจาก ISBN');
    assert_same(0, $svc->search('%')['total'], 'อักขระ % ต้องถูกค้นตรงตัว ไม่ใช่ wildcard');
});

test('AT-16 R-04 กรองตามหมวดหมู่ และใช้ร่วมกับคำค้น', function (PDO $pdo) {
    $svc = new BookService($pdo);
    assert_same(2, $svc->search('', 2)['total']);
    assert_same(['Learning SQL'], titles($svc->search('learning', 2)));
    assert_same(0, $svc->search('learning', 1)['total']);
});

test('AT-17 R-04 แบ่งหน้า 10 รายการต่อหน้า', function (PDO $pdo) {
    $stmt = $pdo->prepare("INSERT INTO books (title, author, category_id, copies_total, copies_available) VALUES (?, 'ผู้แต่ง', 3, 1, 1)");
    for ($i = 1; $i <= 10; $i++) {
        $stmt->execute([sprintf('ZZ เล่มที่ %02d', $i)]);
    }
    $svc = new BookService($pdo);
    $p1 = $svc->search('', null, 1, 10);
    $p2 = $svc->search('', null, 2, 10);
    assert_same(15, $p1['total']);
    assert_same(2, $p1['pages']);
    assert_same(10, count($p1['rows']));
    assert_same(5, count($p2['rows']));
    assert_same(2, $svc->search('', null, 99, 10)['page'], 'หน้าเกินต้องถูกปรับเป็นหน้าสุดท้าย');
});

test('AT-18 R-04 จำนวนคงเหลือตรงกับรายการยืมจริง', function (PDO $pdo) {
    put_on_loan($pdo, 4); // Learning SQL มี 1 เล่ม
    put_on_loan($pdo, 2, 1);
    put_on_loan($pdo, 2, 2);
    $pdo->exec("UPDATE loans SET status = 'returned', returned_at = '2026-10-05' WHERE member_id = 2");
    $pdo->exec('UPDATE books SET copies_available = copies_available + 1 WHERE book_id = 2');

    $rows = array_column((new BookService($pdo))->search()['rows'], null, 'book_id');
    assert_same(0, (int) $rows[4]['available'], 'Learning SQL ต้องหมด');
    assert_same(2, (int) $rows[2]['available'], 'Pro Git 3 เล่ม ยืมค้าง 1 ต้องเหลือ 2');
    $stored = (int) fetch_value($pdo, 'SELECT copies_available FROM books WHERE book_id = 2');
    assert_same($stored, (int) $rows[2]['available'], 'ค่าที่คำนวณต้องตรงกับ copies_available');
});
