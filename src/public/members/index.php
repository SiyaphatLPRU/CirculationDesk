<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$q = input('q');
$members = (new MemberService(db()))->search($q);

render('layout/header', ['title' => 'สมาชิก']);
?>
<h1>สมาชิก</h1>

<form method="get" action="<?= e(url('members/index.php')) ?>" class="toolbar">
  <label>ค้นหารหัสหรือชื่อ
    <input type="search" name="q" value="<?= e($q) ?>">
  </label>
  <button type="submit" class="btn">ค้นหา</button>
  <a class="btn btn-primary" href="<?= e(url('members/form.php')) ?>">+ เพิ่มสมาชิก</a>
</form>

<div class="table-wrap">
<table class="table">
  <thead>
    <tr><th>รหัส</th><th>ชื่อ-นามสกุล</th><th>โทรศัพท์</th><th>อีเมล</th><th class="num">ยืมค้าง</th><th>สถานะ</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($members as $m): ?>
    <?php $isActive = $m['status'] === 'active'; ?>
    <tr>
      <td><?= e($m['member_code']) ?></td>
      <td><?= e($m['full_name']) ?></td>
      <td><?= e($m['phone'] ?? '-') ?></td>
      <td><?= e($m['email'] ?? '-') ?></td>
      <td class="num"><?= e($m['active_loans']) ?></td>
      <td><span class="badge <?= $isActive ? 'badge-ok' : 'badge-no' ?>"><?= e(MemberService::STATUSES[$m['status']]) ?></span></td>
      <td class="actions">
        <a class="btn btn-small" href="<?= e(url('members/form.php', ['id' => $m['member_id']])) ?>">แก้ไข</a>
        <form method="post" action="<?= e(url('members/status.php')) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e($m['member_id']) ?>">
          <input type="hidden" name="status" value="<?= $isActive ? 'suspended' : 'active' ?>">
          <button type="submit" class="btn btn-small<?= $isActive ? ' btn-danger' : '' ?>"><?= $isActive ? 'ระงับ' : 'เปิดใช้งาน' ?></button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$members): ?>
    <tr><td colspan="7" class="muted">ไม่พบสมาชิก</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php render('layout/footer'); ?>
