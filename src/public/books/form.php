<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
$service = new BookService(db());
$categories = (new CategoryService(db()))->all();

$id = input_int('id');
$book = $id !== null ? $service->find($id) : null;
if ($id !== null && $book === null) {
    flash('error', 'ไม่พบหนังสือ');
    redirect('books/index.php');
}

$values = $book ?? ['isbn' => '', 'title' => '', 'author' => '', 'category_id' => '', 'copies_total' => 1];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_post();
    foreach (['isbn', 'title', 'author', 'category_id', 'copies_total'] as $field) {
        $values[$field] = input($field);
    }
    $result = $id !== null ? $service->update($id, $values) : $service->create($values);
    if ($result['ok']) {
        flash('success', $id !== null ? 'บันทึกการแก้ไขแล้ว' : 'เพิ่มหนังสือแล้ว');
        redirect('books/index.php');
    }
    $errors = $result['errors'];
}

$onLoan = $book !== null ? (int) $book['copies_total'] - (int) $book['copies_available'] : 0;
$title = $id !== null ? 'แก้ไขหนังสือ' : 'เพิ่มหนังสือ';
render('layout/header', ['title' => $title]);
?>
<h1><?= e($title) ?></h1>
<?php if (isset($errors['_'])): ?>
  <div class="alert alert-error"><?= e($errors['_']) ?></div>
<?php endif; ?>

<form method="post" action="<?= e(url('books/form.php', ['id' => $id])) ?>" class="card">
  <?= csrf_field() ?>
  <label>ชื่อหนังสือ *
    <input type="text" name="title" value="<?= e($values['title']) ?>" maxlength="255" required>
    <?php if (isset($errors['title'])): ?><span class="field-error"><?= e($errors['title']) ?></span><?php endif; ?>
  </label>
  <div class="form-grid">
    <label>ผู้แต่ง *
      <input type="text" name="author" value="<?= e($values['author']) ?>" maxlength="150" required>
      <?php if (isset($errors['author'])): ?><span class="field-error"><?= e($errors['author']) ?></span><?php endif; ?>
    </label>
    <label>ISBN (ไม่บังคับ)
      <input type="text" name="isbn" value="<?= e($values['isbn'] ?? '') ?>" maxlength="20">
      <?php if (isset($errors['isbn'])): ?><span class="field-error"><?= e($errors['isbn']) ?></span><?php endif; ?>
    </label>
    <label>หมวดหมู่ *
      <select name="category_id" required>
        <option value="">-- เลือก --</option>
        <?php foreach ($categories as $c): ?>
          <option value="<?= e($c['category_id']) ?>"<?= (string) $values['category_id'] === (string) $c['category_id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if (isset($errors['category_id'])): ?><span class="field-error"><?= e($errors['category_id']) ?></span><?php endif; ?>
    </label>
    <label>จำนวนทั้งหมด (เล่ม) *
      <input type="number" name="copies_total" value="<?= e($values['copies_total']) ?>" min="1" max="9999" required>
      <?php if (isset($errors['copies_total'])): ?><span class="field-error"><?= e($errors['copies_total']) ?></span><?php endif; ?>
    </label>
  </div>
  <?php if ($book !== null): ?>
    <p class="muted">ขณะนี้ถูกยืมอยู่ <?= e($onLoan) ?> เล่ม · คงเหลือ <?= e($book['copies_available']) ?> เล่ม</p>
  <?php endif; ?>
  <button type="submit" class="btn btn-primary">บันทึก</button>
  <a class="btn" href="<?= e(url('books/index.php')) ?>">ยกเลิก</a>
</form>
<?php render('layout/footer'); ?>
