<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$q = input('q');
$categoryId = input_int('category_id');
$result = (new BookService(db()))->search($q, $categoryId, input_int('page') ?? 1, config('app')['per_page']);
$categories = (new CategoryService(db()))->all();

render('layout/header', ['title' => 'หนังสือ']);
?>
<h1>หนังสือ</h1>

<form method="get" action="<?= e(url('books/index.php')) ?>" class="toolbar">
  <label>ค้นหา (ชื่อ ผู้แต่ง ISBN)
    <input type="search" name="q" value="<?= e($q) ?>">
  </label>
  <label>หมวดหมู่
    <select name="category_id">
      <option value="">ทุกหมวดหมู่</option>
      <?php foreach ($categories as $c): ?>
        <option value="<?= e($c['category_id']) ?>"<?= $categoryId === (int) $c['category_id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit" class="btn">ค้นหา</button>
  <a class="btn btn-primary" href="<?= e(url('books/form.php')) ?>">+ เพิ่มหนังสือ</a>
</form>

<p class="muted">พบ <?= e($result['total']) ?> รายการ</p>

<div class="table-wrap">
<table class="table">
  <thead>
    <tr><th>ชื่อหนังสือ</th><th>ผู้แต่ง</th><th>ISBN</th><th>หมวดหมู่</th><th class="num">คงเหลือ/ทั้งหมด</th><th>สถานะ</th><th></th></tr>
  </thead>
  <tbody>
  <?php foreach ($result['rows'] as $b): ?>
    <tr>
      <td><?= e($b['title']) ?></td>
      <td><?= e($b['author']) ?></td>
      <td><?= e($b['isbn'] ?? '-') ?></td>
      <td><?= e($b['category_name']) ?></td>
      <td class="num"><?= e($b['available']) ?>/<?= e($b['copies_total']) ?></td>
      <td>
        <?php if ((int) $b['available'] > 0): ?>
          <span class="badge badge-ok">มีให้ยืม</span>
        <?php else: ?>
          <span class="badge badge-no">หมดชั่วคราว</span>
        <?php endif; ?>
      </td>
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
  <?php if (!$result['rows']): ?>
    <tr><td colspan="7" class="muted">ไม่พบหนังสือ</td></tr>
  <?php endif; ?>
  </tbody>
</table>
</div>

<?php render('partials/pagination', [
    'path'  => 'books/index.php',
    'query' => ['q' => $q, 'category_id' => $categoryId],
    'page'  => $result['page'],
    'pages' => $result['pages'],
]); ?>
<?php render('layout/footer'); ?>
