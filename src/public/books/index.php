<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$books = (new BookService(db()))->all();

render('layout/header', ['title' => 'หนังสือ']);
?>
<h1>หนังสือ</h1>
<p><a class="btn btn-primary" href="<?= e(url('books/form.php')) ?>">+ เพิ่มหนังสือ</a></p>

<div class="table-wrap">
<table class="table">
  <thead>
    <tr><th>ชื่อหนังสือ</th><th>ผู้แต่ง</th><th>ISBN</th><th>หมวดหมู่</th><th class="num">คงเหลือ/ทั้งหมด</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($books as $b): ?>
    <tr>
      <td><?= e($b['title']) ?></td>
      <td><?= e($b['author']) ?></td>
      <td><?= e($b['isbn'] ?? '-') ?></td>
      <td><?= e($b['category_name']) ?></td>
      <td class="num"><?= e($b['copies_available']) ?>/<?= e($b['copies_total']) ?></td>
      <td class="actions">
        <a class="btn btn-small" href="<?= e(url('books/form.php', ['id' => $b['book_id']])) ?>">แก้ไข</a>
        <form method="post" action="<?= e(url('books/delete.php')) ?>" onsubmit="return confirm('ยืนยันการลบหนังสือเล่มนี้?')">
          <?= csrf_field() ?>
          <input type="hidden" name="id" value="<?= e($b['book_id']) ?>">
          <button type="submit" class="btn btn-small btn-danger">ลบ</button>
        </form>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if (!$books): ?>
    <tr><td colspan="6" class="muted">ยังไม่มีหนังสือ</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>
<?php render('layout/footer'); ?>
