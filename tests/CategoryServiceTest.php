<?php
declare(strict_types=1);

// R-02 จัดการหมวดหมู่

test('AT-09 R-02 เพิ่มและแก้ชื่อหมวดหมู่ ชื่อซ้ำถูกปฏิเสธ', function (PDO $pdo) {
    $svc = new CategoryService($pdo);
    $r = $svc->create('นิยาย');
    assert_true($r['ok']);
    assert_true(!$svc->create('ฐานข้อมูล')['ok'], 'ชื่อซ้ำต้องถูกปฏิเสธ');
    assert_true(!$svc->create('  ')['ok'], 'ชื่อว่างต้องถูกปฏิเสธ');
    assert_true($svc->rename($r['id'], 'วรรณกรรม')['ok']);
    assert_same('วรรณกรรม', $svc->find($r['id'])['name']);
    assert_true(!$svc->rename($r['id'], 'ฐานข้อมูล')['ok'], 'เปลี่ยนเป็นชื่อที่ซ้ำต้องถูกปฏิเสธ');
});

test('AT-10 R-02 ลบหมวดหมู่ที่ยังมีหนังสือถูกปฏิเสธ ลบหมวดว่างได้', function (PDO $pdo) {
    $svc = new CategoryService($pdo);
    assert_true(!$svc->delete(1)['ok'], 'หมวด 1 มีหนังสือ ต้องลบไม่ได้');
    $id = $svc->create('หมวดว่าง')['id'];
    assert_true($svc->delete($id)['ok']);
    assert_same(null, $svc->find($id));
});
