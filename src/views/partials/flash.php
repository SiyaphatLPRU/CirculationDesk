<?php
declare(strict_types=1);

foreach (take_flashes() as $f): ?>
  <div class="alert alert-<?= e($f['type']) ?>"><?= e($f['message']) ?></div>
<?php endforeach;
