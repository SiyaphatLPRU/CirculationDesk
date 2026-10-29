<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
require_post();

$result = (new BookService(db()))->delete(input_int('id') ?? 0);
if ($result['ok']) {
    flash('success', 'ลบหนังสือแล้ว');
} else {
    flash('error', $result['error']);
}
redirect('books/index.php');
