<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$loans = new LoanService(db(), config('loan'));
$memberId = input_int('member_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $result = $loans->return(input_int('loan_id') ?? 0, today());
    if ($result['ok']) {
        // เก็บผลไว้แสดงค่าปรับหลัง redirect
        $_SESSION['last_return'] = $result;
    } else {
        flash('error', $result['error']);
    }
    redirect('loans/return.php', ['member_id' => $memberId]);
}

$lastReturn = $_SESSION['last_return'] ?? null;
unset($_SESSION['last_return']);

$members = (new MemberService(db()))->search();
$member = $memberId !== null ? (new MemberService(db()))->find($memberId) : null;
$active = $member !== null ? $loans->activeLoansForMember($memberId) : [];
$today = today();

render('layout/header', ['title' => 'คืนหนังสือ']);
?>
<h1>คืนหนังสือ</h1>

<?php if ($lastReturn !== null): ?>
  <div class="card alert-<?= $lastReturn['fine'] > 0 ? 'error' : 'success' ?>">
    <strong>คืนแล้ว: <?= e($lastReturn['title']) ?></strong><br>
    กำหนดคืน <?= e(thai_date($lastReturn['due_date'])) ?> · คืนวันที่ <?= e(thai_date($lastReturn['returned_at'])) ?>
    <?php if ($lastReturn['days_late'] > 0): ?> · เกินกำหนด <?= e($lastReturn['days_late']) ?> วัน<?php endif; ?>
    <div class="result-fine">ค่าปรับ <?= e(money($lastReturn['fine'])) ?> บาท</div>
  </div>
<?php endif; ?>

<form method="get" action="<?= e(url('loans/return.php')) ?>" class="toolbar">
  <label>สมาชิก
    <select name="member_id" required>
      <option value="">-- เลือกสมาชิก --</option>
      <?php foreach ($members as $m): ?>
        <option value="<?= e($m['member_id']) ?>"<?= $memberId === (int) $m['member_id'] ? ' selected' : '' ?>>
          <?= e($m['member_code'] . ' ' . $m['full_name']) ?> (ยืมค้าง <?= e($m['active_loans']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn">แสดงรายการยืมค้าง</button>
</form>

<?php if ($member !== null): ?>
  <h2>รายการยืมค้างของ <?= e($member['member_code'] . ' ' . $member['full_name']) ?></h2>
  <div class="table-wrap">
  <table class="table">
    <thead><tr><th>ชื่อหนังสือ</th><th>วันที่ยืม</th><th>กำหนดคืน</th><th class="num">ค่าปรับถ้าคืนวันนี้</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($active as $loan): ?>
      <?php $late = $loans->daysLate($loan['due_date'], $today); ?>
      <tr>
        <td><?= e($loan['title']) ?></td>
        <td><?= e(thai_date($loan['borrowed_at'])) ?></td>
        <td>
          <?= e(thai_date($loan['due_date'])) ?>
          <?php if ($late > 0): ?><span class="badge badge-no">เกิน <?= e($late) ?> วัน</span><?php endif; ?>
        </td>
        <td class="num"><?= e(money($loans->calculateFine($loan['due_date'], $today))) ?></td>
        <td class="actions">
          <form method="post" action="<?= e(url('loans/return.php')) ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="loan_id" value="<?= e($loan['loan_id']) ?>">
            <input type="hidden" name="member_id" value="<?= e($memberId) ?>">
            <button type="submit" class="btn btn-small btn-primary">คืน</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$active): ?>
      <tr><td colspan="5" class="muted">ไม่มีรายการยืมค้าง</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
<?php endif; ?>
<?php render('layout/footer'); ?>
