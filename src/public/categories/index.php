<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$service = new CategoryService(db());

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    $id = input_int('id') ?? 0;
    $action = input('action');
    $result = match ($action) {
        'create' => $service->create(input('name')),
        'rename' => $service->rename($id, input('name')),
        'delete' => $service->delete($id),
        default  => ['ok' => false, 'error' => 'คำสั่งไม่ถูกต้อง'],
    };
    if ($result['ok']) {
        flash('success', $action === 'delete' ? 'ลบหมวดหมู่แล้ว' : 'บันทึกหมวดหมู่แล้ว');
    } else {
        flash('error', $result['error']);
    }
    redirect('categories/index.php');
}

$categories = $service->all();
render('layout/header', ['title' => 'หมวดหมู่']);
?>
<h1>หมวดหมู่หนังสือ</h1>

<form method="post" action="<?= e(url('categories/index.php')) ?>" class="card toolbar">
  <?= csrf_field() ?>
  <input type="hidden" name="action" value="create">
  <label>ชื่อหมวดหมู่ใหม่
    <input type="text" name="name" maxlength="100" required>
  </label>
  <button type="submit" class="btn btn-primary">+ เพิ่มหมวดหมู่</button>
</form>

<div class="table-wrap">
<table class="table">
  <thead><tr><th>ชื่อหมวดหมู่ (แก้ไขแล้วกดบันทึก)</th><th class="num">จำนวนหนังสือ</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($categories as $c): ?>
    <tr>
      <td>
        <form method="post" action="<?= e(url('categories/index.php')) ?>" class="toolbar" style="margin:0">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="rename">
          <input type="hidden" name="id" value="<?= e($c['category_id']) ?>">
          <label><input type="text" name="name" value="<?= e($c['name']) ?>" maxlength="100" required aria-label="ชื่อหมวดหมู่"></label>
          <button type="submit" class="btn btn-small">บันทึก</button>
        </form>
      </td>
      <td class="num"><?= e($c['book_count']) ?></td>
      <td class="actions">
        <form method="post" action="<?= e(url('categories/index.php')) ?>" onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?')">
          <?= csrf_field() ?>
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= e($c['category_id']) ?>">
          <button type="submit" class="btn btn-small btn-danger">ลบ</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php render('layout/footer'); ?>
