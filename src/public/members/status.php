<?php
declare(strict_types=1);

require __DIR__ . '/../../lib/bootstrap.php';

require_login();
require_post();

$status = input('status');
$result = (new MemberService(db()))->setStatus(input_int('id') ?? 0, $status);
if ($result['ok']) {
    flash('success', $status === 'suspended' ? 'ระงับสมาชิกแล้ว' : 'เปิดใช้งานสมาชิกแล้ว');
} else {
    flash('error', $result['error']);
}
redirect('members/index.php');
