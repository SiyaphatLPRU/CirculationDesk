<?php
declare(strict_types=1);

// R-08 รายงานประวัติการยืม-คืน

/** สมาชิก 1: ยืม 10-01 (คืนช้า ปรับ 15), 10-15 (ค้าง) · สมาชิก 2: ยืม 10-10 (คืนช้า ปรับ 5), 10-31 (ค้าง) */
function seed_history(PDO $pdo): void
{
    $svc = loan_service($pdo);
    $a = $svc->borrow(1, 1, 1, new DateTimeImmutable('2026-10-01'));
    $svc->return($a['loan_id'], new DateTimeImmutable('2026-10-11'));
    $b = $svc->borrow(2, 2, 1, new DateTimeImmutable('2026-10-10'));
    $svc->return($b['loan_id'], new DateTimeImmutable('2026-10-18'));
    $svc->borrow(1, 3, 1, new DateTimeImmutable('2026-10-15'));
    $svc->borrow(2, 4, 1, new DateTimeImmutable('2026-10-31'));
}

test('AT-33 R-08 กรองตามสมาชิก แสดงคอลัมน์ครบ เรียงล่าสุดก่อน', function (PDO $pdo) {
    seed_history($pdo);
    $r = report_service($pdo)->history(1);
    assert_true($r['ok']);
    assert_same(['Database System Concepts', 'Software Configuration Management Patterns'], array_column($r['rows'], 'title'));
    $row = $r['rows'][1];
    assert_same('2026-10-01', $row['borrowed_at']);
    assert_same('2026-10-08', $row['due_date']);
    assert_same('2026-10-11', $row['returned_at']);
    assert_same('15.00', $row['fine_amount']);
    assert_same('returned', $row['status']);
    assert_same(null, $r['rows'][0]['returned_at']);
});

test('AT-34 R-08 กรองตามช่วงวันที่ยืม (รวมวันต้นและวันปลาย)', function (PDO $pdo) {
    seed_history($pdo);
    $r = report_service($pdo)->history(null, '2026-10-10', '2026-10-15');
    assert_same(['2026-10-15', '2026-10-10'], array_column($r['rows'], 'borrowed_at'));
    assert_same(1, report_service($pdo)->history(2, '2026-10-01', '2026-10-20')['count'], 'กรองสมาชิกร่วมกับช่วงวันที่');
    assert_same(2, report_service($pdo)->history(null, '2026-10-15')['count'], 'ระบุแค่วันเริ่มต้น');
});

test('AT-35 R-08 สรุปจำนวนรายการและค่าปรับรวม', function (PDO $pdo) {
    seed_history($pdo);
    $all = report_service($pdo)->history();
    assert_same(4, $all['count']);
    assert_same(20.0, $all['total_fine']);
    assert_same(15.0, report_service($pdo)->history(1)['total_fine']);
});

test('AT-36 R-08 วันที่ผิดรูปแบบหรือช่วงกลับด้านถูกปฏิเสธ', function (PDO $pdo) {
    assert_true(!report_service($pdo)->history(null, '2026-13-01')['ok']);
    assert_true(!report_service($pdo)->history(null, "2026-10-01' OR 1=1 --")['ok']);
    assert_true(!report_service($pdo)->history(null, '2026-10-20', '2026-10-01')['ok']);
});
