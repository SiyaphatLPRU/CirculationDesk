<?php
declare(strict_types=1);

/**
 * @var string $path   หน้าปลายทาง เช่น 'books/index.php'
 * @var array  $query  พารามิเตอร์ค้นหาที่ต้องคงไว้
 * @var int    $page
 * @var int    $pages
 */
if ($pages <= 1) {
    return;
}
?>
<nav class="pagination" aria-label="แบ่งหน้า">
  <?php if ($page > 1): ?>
    <a href="<?= e(url($path, $query + ['page' => $page - 1])) ?>">‹ ก่อนหน้า</a>
  <?php endif; ?>
  <?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?>
      <span class="current"><?= $i ?></span>
    <?php else: ?>
      <a href="<?= e(url($path, $query + ['page' => $i])) ?>"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
  <?php if ($page < $pages): ?>
    <a href="<?= e(url($path, $query + ['page' => $page + 1])) ?>">ถัดไป ›</a>
  <?php endif; ?>
</nav>
