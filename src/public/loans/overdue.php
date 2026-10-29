<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$today = today();
$rows = (new ReportService(db(), config('loan')))->overdue($today);
$totalFine = array_sum(array_column($rows, 'accrued_fine'));

render('layout/header', ['title' => 'เกินกำหนด']);
?>
<h1>รายการเกินกำหนด</h1>
<p class="muted">
  ณ วันที่ <?= e(thai_date($today->format('Y-m-d'))) ?> · ค่าปรับสะสมคำนวณถึงวันนี้ (บันทึกจริงเมื่อคืนหนังสือ)
</p>

<div class="table-wrap">
<table class="table">
  <thead>
    <tr><th>สมาชิก</th><th>โทรศัพท์</th><th>ชื่อหนังสือ</th><th>วันที่ยืม</th><th>กำหนดคืน</th><th class="num">เกิน (วัน)</th><th class="num">ค่าปรับสะสม (บาท)</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($rows as $r): ?>
    <tr>
      <td><?= e($r['member_code'] . ' ' . $r['full_name']) ?></td>
      <td><?= e($r['phone'] ?? '-') ?></td>
      <td><?= e($r['title']) ?></td>
      <td><?= e(thai_date($r['borrowed_at'])) ?></td>
      <td><?= e(thai_date($r['due_date'])) ?></td>
      <td class="num"><?= e($r['days_late']) ?></td>
      <td class="num"><?= e(money($r['accrued_fine'])) ?></td>
      <td class="actions"><a class="btn btn-small" href="<?= e(url('loans/return.php', ['member_id' => $r['member_id']])) ?>">ไปหน้าคืน</a></td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?>
    <tr><td colspan="8" class="muted">ไม่มีรายการเกินกำหนด</td></tr>
  <?php endif; ?>
  </tbody>
  <?php if ($rows): ?>
  <tfoot>
    <tr><td colspan="5">รวม <?= e(count($rows)) ?> รายการ</td><td></td><td class="num"><?= e(money($totalFine)) ?></td><td></td></tr>
  </tfoot>
  <?php endif; ?>
</table>
</div>
<?php render('layout/footer'); ?>
