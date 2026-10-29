<?php
declare(strict_types=1);

require __DIR__ . '/../lib/bootstrap.php';

require_post();
logout();
redirect('login.php');
