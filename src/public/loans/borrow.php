<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

$staff = require_login();
$memberId = input_int('member_id');
$bookId = input_int('book_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $loans = new LoanService(db(), config('loan'));
    $result = $loans->borrow($memberId ?? 0, $bookId ?? 0, $staff['staff_id'], today());
    if ($result['ok']) {
        flash('success', 'ยืมสำเร็จ: ' . $result['title'] . ' · กำหนดคืน ' . thai_date($result['due_date']));
        redirect('loans/borrow.php', ['member_id' => $memberId]);
    }
    flash('error', $result['error']);
    redirect('loans/borrow.php', ['member_id' => $memberId, 'book_id' => $bookId]);
}

$members = (new MemberService(db()))->search();
$books = (new BookService(db()))->options();
$rules = config('loan');

render('layout/header', ['title' => 'ยืมหนังสือ']);
?>
<h1>ยืมหนังสือ</h1>
<p class="muted">
  ยืมได้ <?= e($rules['days']) ?> วัน · ยืมค้างได้ไม่เกิน <?= e($rules['max_active']) ?> เล่มต่อคน ·
  ค่าปรับ <?= e($rules['fine_per_day']) ?> บาท/วัน/เล่ม · สมาชิกที่ถูกระงับยืมไม่ได้
</p>

<form method="post" action="<?= e(url('loans/borrow.php')) ?>" class="card">
  <?= csrf_field() ?>
  <div class="form-grid">
    <label>สมาชิก
      <select name="member_id" required>
        <option value="">-- เลือกสมาชิก --</option>
        <?php foreach ($members as $m): ?>
          <option value="<?= e($m['member_id']) ?>"<?= $memberId === (int) $m['member_id'] ? ' selected' : '' ?>>
            <?= e($m['member_code'] . ' ' . $m['full_name']) ?> (ยืมค้าง <?= e($m['active_loans']) ?><?= $m['status'] !== 'active' ? ', ถูกระงับ' : '' ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label>หนังสือ
      <select name="book_id" required>
        <option value="">-- เลือกหนังสือ --</option>
        <?php foreach ($books as $b): ?>
          <option value="<?= e($b['book_id']) ?>"<?= $bookId === (int) $b['book_id'] ? ' selected' : '' ?>>
            <?= e($b['title']) ?> (คงเหลือ <?= e($b['copies_available']) ?>/<?= e($b['copies_total']) ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
  </div>
  <button type="submit" class="btn btn-primary">บันทึกการยืม</button>
</form>
<?php render('layout/footer'); ?>
