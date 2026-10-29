<?php
declare(strict_types=1);

// R-02 จัดการข้อมูลหนังสือ

function book_input(array $override = []): array
{
    return array_merge([
        'isbn' => '9781111111111', 'title' => 'Clean Code', 'author' => 'Martin',
        'category_id' => '1', 'copies_total' => '3',
    ], $override);
}

/** จำลองการยืมค้างโดยตรงในฐานข้อมูล (ไม่ผ่าน LoanService) */
function put_on_loan(PDO $pdo, int $bookId, int $memberId = 1): void
{
    $pdo->prepare("INSERT INTO loans (member_id, book_id, staff_id, borrowed_at, due_date, status)
                   VALUES (?, ?, 1, '2026-10-01', '2026-10-08', 'borrowed')")->execute([$memberId, $bookId]);
    $pdo->prepare('UPDATE books SET copies_available = copies_available - 1 WHERE book_id = ?')->execute([$bookId]);
}

test('AT-01 R-02 เพิ่มหนังสือใหม่ copies_available = copies_total', function (PDO $pdo) {
    $r = (new BookService($pdo))->create(book_input());
    assert_true($r['ok'], 'เพิ่มไม่สำเร็จ');
    $book = (new BookService($pdo))->find($r['id']);
    assert_same(3, (int) $book['copies_total']);
    assert_same(3, (int) $book['copies_available']);
});

test('AT-02 R-02 ISBN ว่างเก็บเป็น NULL และเพิ่มได้หลายเล่ม', function (PDO $pdo) {
    $svc = new BookService($pdo);
    $a = $svc->create(book_input(['isbn' => '  ', 'title' => 'เล่ม ก']));
    $b = $svc->create(book_input(['isbn' => '', 'title' => 'เล่ม ข']));
    assert_true($a['ok'] && $b['ok'], 'ต้องเพิ่มหนังสือที่ไม่มี ISBN ได้มากกว่าหนึ่งเล่ม');
    assert_same(null, $svc->find($a['id'])['isbn']);
    assert_same(2, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM books WHERE isbn IS NULL'));
});

test('AT-03 R-02 ISBN ซ้ำถูกปฏิเสธทั้งตอนเพิ่มและแก้ไข', function (PDO $pdo) {
    $svc = new BookService($pdo);
    $r = $svc->create(book_input(['isbn' => '9780000000028']));
    assert_true(!$r['ok'] && isset($r['errors']['isbn']), 'ต้องปฏิเสธ ISBN ซ้ำตอนเพิ่ม');

    $r = $svc->update(1, book_input(['isbn' => '9780000000028']));
    assert_true(!$r['ok'] && isset($r['errors']['isbn']), 'ต้องปฏิเสธ ISBN ซ้ำตอนแก้ไข');

    $r = $svc->update(2, book_input(['isbn' => '9780000000028', 'title' => 'Pro Git 2nd']));
    assert_true($r['ok'], 'แก้ไขโดยใช้ ISBN เดิมของตัวเองต้องได้');
});

test('AT-04 R-02 ชื่อ ผู้แต่ง จำเป็น และ copies_total >= 1', function (PDO $pdo) {
    $r = (new BookService($pdo))->create(book_input(['title' => ' ', 'author' => '', 'copies_total' => '0']));
    assert_true(!$r['ok']);
    assert_true(isset($r['errors']['title'], $r['errors']['author'], $r['errors']['copies_total']), 'ต้องแจ้งข้อผิดพลาดครบทั้ง 3 ช่อง');
    $r = (new BookService($pdo))->create(book_input(['copies_total' => 'abc']));
    assert_true(isset($r['errors']['copies_total']), 'copies_total ที่ไม่ใช่ตัวเลขต้องถูกปฏิเสธ');
});

test('AT-05 R-02 ลบหนังสือที่มีประวัติการยืมถูกปฏิเสธ', function (PDO $pdo) {
    put_on_loan($pdo, 1);
    $pdo->exec("UPDATE loans SET status = 'returned', returned_at = '2026-10-05'");
    $r = (new BookService($pdo))->delete(1);
    assert_true(!$r['ok'], 'ต้องลบไม่ได้แม้คืนแล้ว เพราะมีประวัติ');
    assert_same(1, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM books WHERE book_id = 1'));
});

test('AT-06 R-02 ลบหนังสือที่ไม่เคยถูกยืมได้', function (PDO $pdo) {
    $r = (new BookService($pdo))->delete(5);
    assert_true($r['ok']);
    assert_same(0, (int) fetch_value($pdo, 'SELECT COUNT(*) FROM books WHERE book_id = 5'));
});

test('AT-07 R-02 ลด copies_total ต่ำกว่าจำนวนที่ถูกยืมถูกปฏิเสธ', function (PDO $pdo) {
    $svc = new BookService($pdo);
    put_on_loan($pdo, 2, 1);
    put_on_loan($pdo, 2, 2); // Pro Git: ทั้งหมด 3 ยืมอยู่ 2 คงเหลือ 1
    $input = book_input(['isbn' => '9780000000028', 'title' => 'Pro Git', 'copies_total' => '1']);
    $r = $svc->update(2, $input);
    assert_true(!$r['ok'] && isset($r['errors']['copies_total']), 'ต้องปฏิเสธเมื่อน้อยกว่าที่ถูกยืม');
    assert_same(3, (int) $svc->find(2)['copies_total'], 'ข้อมูลต้องไม่เปลี่ยน');
});

test('AT-08 R-02 แก้ copies_total แล้ว copies_available ปรับตามจำนวนที่ถูกยืม', function (PDO $pdo) {
    $svc = new BookService($pdo);
    put_on_loan($pdo, 2);
    $input = book_input(['isbn' => '9780000000028', 'title' => 'Pro Git']);
    assert_true($svc->update(2, array_merge($input, ['copies_total' => '5']))['ok']);
    assert_same(4, (int) $svc->find(2)['copies_available'], 'เพิ่มเป็น 5 ยืมอยู่ 1 ต้องเหลือ 4');
    assert_true($svc->update(2, array_merge($input, ['copies_total' => '1']))['ok']);
    assert_same(0, (int) $svc->find(2)['copies_available'], 'ลดเป็น 1 ยืมอยู่ 1 ต้องเหลือ 0');
});
