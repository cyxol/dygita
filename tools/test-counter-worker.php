<?php
require __DIR__ . '/test-bootstrap.php';
$cid = (int) ($argv[1] ?? 0);
for ($i=0; $i<25; $i++) Dygita_Repository::increment($cid, 'likes');
