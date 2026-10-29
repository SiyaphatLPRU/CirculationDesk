<?php
declare(strict_types=1);

// R-03 จัดการข้อมูลสมาชิก

test('AT-11 R-03 เพิ่มสมาชิกใหม่ สถานะเริ่มต้น active ช่องว่างเก็บเป็น NULL', function (PDO $pdo) {
    $svc = new MemberService($pdo);
    $r = $svc->create(['member_code' => 'M0100', 'full_name' => 'สมาชิก ใหม่', 'phone' => '', 'email' => ' ']);
    assert_true($r['ok'], 'เพิ่มไม่สำเร็จ');
    $m = $svc->find($r['id']);
    assert_same('active', $m['status']);
    assert_same(null, $m['phone']);
    assert_same(null, $m['email']);
});

test('AT-12 R-03 รหัสสมาชิกซ้ำถูกปฏิเสธทั้งตอนเพิ่มและแก้ไข', function (PDO $pdo) {
    $svc = new MemberService($pdo);
    $r = $svc->create(['member_code' => 'M0001', 'full_name' => 'ซ้ำ']);
    assert_true(!$r['ok'] && isset($r['errors']['member_code']));
    $r = $svc->update(2, ['member_code' => 'M0001', 'full_name' => 'สมหญิง รักเรียน']);
    assert_true(!$r['ok'] && isset($r['errors']['member_code']));
    $r = $svc->update(2, ['member_code' => 'M0002', 'full_name' => 'สมหญิง เปลี่ยนชื่อ', 'email' => 'new@example.com']);
    assert_true($r['ok'], 'แก้ไขโดยใช้รหัสเดิมของตัวเองต้องได้');
    assert_same('สมหญิง เปลี่ยนชื่อ', $svc->find(2)['full_name']);
});

test('AT-13 R-03 ตรวจข้อมูลจำเป็นและรูปแบบอีเมล', function (PDO $pdo) {
    $r = (new MemberService($pdo))->create(['member_code' => '', 'full_name' => '', 'email' => 'not-an-email']);
    assert_true(isset($r['errors']['member_code'], $r['errors']['full_name'], $r['errors']['email']));
});

test('AT-14 R-03 เปลี่ยนสถานะ active/suspended และปฏิเสธสถานะอื่น', function (PDO $pdo) {
    $svc = new MemberService($pdo);
    assert_true($svc->setStatus(1, 'suspended')['ok']);
    assert_same('suspended', $svc->find(1)['status']);
    assert_true($svc->setStatus(1, 'active')['ok']);
    assert_same('active', $svc->find(1)['status']);
    assert_true(!$svc->setStatus(1, 'deleted')['ok'], 'สถานะอื่นต้องถูกปฏิเสธ');
});
