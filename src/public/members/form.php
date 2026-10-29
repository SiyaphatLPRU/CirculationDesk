<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$service = new MemberService(db());

$id = input_int('id');
$member = $id !== null ? $service->find($id) : null;
if ($id !== null && $member === null) {
    flash('error', 'ไม่พบสมาชิก');
    redirect('members/index.php');
}

$values = $member ?? ['member_code' => '', 'full_name' => '', 'phone' => '', 'email' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    foreach (['member_code', 'full_name', 'phone', 'email'] as $field) {
        $values[$field] = input($field);
    }
    $result = $id !== null ? $service->update($id, $values) : $service->create($values);
    if ($result['ok']) {
        flash('success', $id !== null ? 'บันทึกการแก้ไขแล้ว' : 'เพิ่มสมาชิกแล้ว');
        redirect('members/index.php');
    }
    $errors = $result['errors'];
}

$title = $id !== null ? 'แก้ไขสมาชิก' : 'เพิ่มสมาชิก';
render('layout/header', ['title' => $title]);
?>
<h1><?= e($title) ?></h1>
<?php if (isset($errors['_'])): ?>
  <div class="alert alert-error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('members/form.php', ['id' => $id])) ?>" class="card">
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>รหัสสมาชิก *
      <input type="text" name="member_code" value="<?= e($values['member_code']) ?>" maxlength="20" required>
      <?php if (isset($errors['member_code'])): ?><span class="field-error"><?= e($errors['member_code']) ?></span><?php endif; ?>
    </label>
    <label>ชื่อ-นามสกุล *
      <input type="text" name="full_name" value="<?= e($values['full_name']) ?>" maxlength="100" required>
      <?php if (isset($errors['full_name'])): ?><span class="field-error"><?= e($errors['full_name']) ?></span><?php endif; ?>
    </label>
    <label>โทรศัพท์
      <input type="text" name="phone" value="<?= e($values['phone'] ?? '') ?>" maxlength="20">
      <?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?>
    </label>
    <label>อีเมล
      <input type="email" name="email" value="<?= e($values['email'] ?? '') ?>" maxlength="100">
      <?php if (isset($errors['email'])): ?><span class="field-error"><?= e($errors['email']) ?></span><?php endif; ?>
    </label>
  </div>
  <?php if ($member !== null): ?>
    <p class="muted">สถานะปัจจุบัน: <?= e(MemberService::STATUSES[$member['status']]) ?> (เปลี่ยนได้จากหน้ารายการสมาชิก)</p>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary">บันทึก</button>
  <a class="btn" href="<?= e(url('members/index.php')) ?>">ยกเลิก</a>
</form>
<?php render('layout/footer'); ?>
