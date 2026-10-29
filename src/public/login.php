<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';

if (current_staff() !== null) {
    redirect('index.php');
}

$error = '';
$username = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $username = input('username');
    $password = $_POST['password'] ?? '';
    if (is_string($password) && $username !== '' && login($username, $password)) {
        redirect('index.php');
    }
    $error = 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง';
}

render('layout/header', ['title' => 'เข้าสู่ระบบ']);
?>
<div class="login-box card">
  <h1>เข้าสู่ระบบเจ้าหน้าที่</h1>
  <?php if ($error !== ''): ?>
    <div class="alert alert-error"><?= e($error) ?></div>
  <?php endif; ?>
  <form method="post" action="<?= e(url('login.php')) ?>">
    <?= csrf_field() ?>
    <label>ชื่อผู้ใช้
      <input type="text" name="username" value="<?= e($username) ?>" required autofocus autocomplete="username">
    </label>
    <label>รหัสผ่าน
      <input type="password" name="password" required autocomplete="current-password">
    </label>
    <button type="submit" class="btn btn-primary btn-block">เข้าสู่ระบบ</button>
  </form>
</div>
<?php render('layout/footer'); ?>
