<?php
declare(strict_types=1);

// R-07 ตรวจรายการเกินกำหนด (วันนี้ = 2026-10-29)

/** เตรียมข้อมูล: เกิน 1 วัน, เกิน 10 วัน, ครบกำหนดวันนี้, ยังไม่ถึงกำหนด, เกินกำหนดแต่คืนแล้ว */
function seed_overdue(PDO $pdo): void
{
    put_on_loan($pdo, 1, 1, '2026-10-21', '2026-10-28'); // เกิน 1 วัน
    put_on_loan($pdo, 2, 2, '2026-10-12', '2026-10-19'); // เกิน 10 วัน
    put_on_loan($pdo, 3, 1, '2026-10-22', '2026-10-29'); // ครบกำหนดวันนี้ ยังไม่เกิน
    put_on_loan($pdo, 4, 2, '2026-10-28', '2026-11-04'); // ยังไม่ถึงกำหนด
    put_on_loan($pdo, 5, 1, '2026-10-01', '2026-10-08'); // เกินกำหนดแต่คืนแล้ว
    $pdo->exec("UPDATE loans SET status = 'returned', returned_at = '2026-10-10', fine_amount = 10 WHERE book_id = 5");
}

test('AT-30 R-07 แสดงเฉพาะรายการที่ยังไม่คืนและเกินกำหนด', function (PDO $pdo) {
    seed_overdue($pdo);
    $rows = report_service($pdo)->overdue(new DateTimeImmutable('2026-10-29'));
    assert_same(['Pro Git', 'Software Configuration Management Patterns'], array_column($rows, 'title'));
});

test('AT-31 R-07 จำนวนวันที่เกินและค่าปรับสะสม ณ วันนี้ เรียงจากเกินนานสุด', function (PDO $pdo) {
    seed_overdue($pdo);
    $rows = report_service($pdo)->overdue(new DateTimeImmutable('2026-10-29'));
    assert_same([10, 1], array_column($rows, 'days_late'));
    assert_same([50, 5], array_column($rows, 'accrued_fine'));
    assert_same(0, (int) fetch_value($pdo, "SELECT SUM(fine_amount) FROM loans WHERE status = 'borrowed'"), 'ค่าปรับสะสมต้องไม่ถูกบันทึกลงตาราง');
    $later = report_service($pdo)->overdue(new DateTimeImmutable('2026-10-31'));
    assert_same([60, 15, 10], array_column($later, 'accrued_fine'), 'วันที่เปลี่ยน ค่าปรับสะสมต้องเปลี่ยนตาม');
});

test('AT-32 R-07 จำนวนรายการเกินกำหนดบนแดชบอร์ด', function (PDO $pdo) {
    seed_overdue($pdo);
    $stats = report_service($pdo)->dashboard(new DateTimeImmutable('2026-10-29'));
    assert_same(2, $stats['overdue']);
    assert_same(4, $stats['active_loans']);
    assert_same(5, $stats['books']);
    assert_same(2, $stats['active_members']);
});
