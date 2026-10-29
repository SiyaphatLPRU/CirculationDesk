<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$memberId = input_int('member_id');
$from = input('from');
$to = input('to');

$report = (new ReportService(db(), config('loan')))->history(
    $memberId,
    $from !== '' ? $from : null,
    $to !== '' ? $to : null
);
$members = (new MemberService(db()))->search();

render('layout/header', ['title' => 'ประวัติการยืม-คืน']);
?>
<h1>รายงานประวัติการยืม-คืน</h1>

<form method="get" action="<?= e(url('reports/history.php')) ?>" class="toolbar">
  <label>สมาชิก
    <select name="member_id">
      <option value="">ทุกคน</option>
      <?php foreach ($members as $m): ?>
        <option value="<?= e($m['member_id']) ?>"<?= $memberId === (int) $m['member_id'] ? ' selected' : '' ?>><?= e($m['member_code'] . ' ' . $m['full_name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>ยืมตั้งแต่วันที่
    <input type="date" name="from" value="<?= e($from) ?>">
  </label>
  <label>ถึงวันที่
    <input type="date" name="to" value="<?= e($to) ?>">
  </label>
  <button type="submit" class="btn">แสดงรายงาน</button>
  <a class="btn" href="<?= e(url('reports/history.php')) ?>">ล้างตัวกรอง</a>
</form>

<?php if (!$report['ok']): ?>
  <div class="alert alert-error"><?= e($report['error']) ?></div>
<?php else: ?>
  <div class="stats">
    <div class="stat"><span class="value"><?= e($report['count']) ?></span><span class="label">จำนวนรายการ</span></div>
    <div class="stat"><span class="value"><?= e(money($report['total_fine'])) ?></span><span class="label">ค่าปรับรวม (บาท)</span></div>
  </div>

  <div class="table-wrap">
  <table class="table">
    <thead>
      <tr><th>สมาชิก</th><th>ชื่อหนังสือ</th><th>วันที่ยืม</th><th>กำหนดคืน</th><th>วันที่คืน</th><th class="num">ค่าปรับ (บาท)</th><th>สถานะ</th></tr>
    </thead>
    <tbody>
    <?php foreach ($report['rows'] as $r): ?>
      <tr>
        <td><?= e($r['member_code'] . ' ' . $r['full_name']) ?></td>
        <td><?= e($r['title']) ?></td>
        <td><?= e(thai_date($r['borrowed_at'])) ?></td>
        <td><?= e(thai_date($r['due_date'])) ?></td>
        <td><?= e(thai_date($r['returned_at'])) ?></td>
        <td class="num"><?= e(money($r['fine_amount'])) ?></td>
        <td>
          <?php if ($r['status'] === 'returned'): ?>
            <span class="badge badge-muted">คืนแล้ว</span>
          <?php else: ?>
            <span class="badge badge-ok">กำลังยืม</span>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$report['rows']): ?>
      <tr><td colspan="7" class="muted">ไม่พบรายการ</td></tr>
    <?php endif; ?>
    </tbody>
    <?php if ($report['rows']): ?>
    <tfoot>
      <tr><td colspan="5">รวม <?= e($report['count']) ?> รายการ</td><td class="num"><?= e(money($report['total_fine'])) ?></td><td></td></tr>
    </tfoot>
    <?php endif; ?>
  </table>
  </div>
<?php endif; ?>
<?php render('layout/footer'); ?>
