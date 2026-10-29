<?php
declare(strict_types=1);

/** @var string $title */
$staff = current_staff();
$nav = [
    'index.php' => 'แดชบอร์ด',
];
$current = substr($_SERVER['SCRIPT_NAME'] ?? '', strlen(base_path()) + 1);
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? '') ?> · <?= e(config('app')['name']) ?></title>
  <link rel="stylesheet" href="<?= e(url('assets/style.css')) ?>">
</head>
<body>
<?php if ($staff !== null): ?>
<header class="topbar">
  <a class="brand" href="<?= e(url('index.php')) ?>">📚 <?= e(config('app')['name']) ?></a>
  <nav class="nav">
    <?php foreach ($nav as $path => $label): ?>
      <?php $active = strtok($current, '/') === strtok($path, '/'); ?>
      <a href="<?= e(url($path)) ?>"<?= $active ? ' class="active"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
  </nav>
  <form method="post" action="<?= e(url('logout.php')) ?>" class="logout">
    <?= csrf_field() ?>
    <span class="who"><?= e($staff['full_name']) ?></span>
    <button type="submit" class="btn btn-small">ออกจากระบบ</button>
  </form>
</header>
<?php endif; ?>
<main class="container">
<?php render('partials/flash'); ?>
