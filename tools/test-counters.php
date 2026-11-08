<?php
require __DIR__ . '/test-bootstrap.php';
$db = \Typecho\Db::get();
$cid = \Widget\Base\Contents::alloc()->insert(['title'=>'Counter concurrency test','type'=>'post','status'=>'publish','authorId'=>1]);
try {
    $workers = [];
    for ($i=0; $i<8; $i++) {
        $process = proc_open([PHP_BINARY, __DIR__ . '/test-counter-worker.php', (string) $cid],
            [0=>['file','/dev/null','r'],1=>['file','/dev/null','w'],2=>['pipe','w']], $pipes);
        check(is_resource($process), 'Worker failed to start');
        $workers[] = [$process,$pipes[2]];
    }
    foreach ($workers as [$process,$pipe]) {
        $error = stream_get_contents($pipe); fclose($pipe);
        check(proc_close($process) === 0, 'Counter worker failed: ' . $error);
    }
    $row=$db->fetchRow($db->select()->from('table.fields')->where('cid = ?', $cid)->where('name = ?', 'likes'));
    check((int)$row['int_value'] === 200 && $row['str_value'] === '200', 'Lost update or inconsistent counter');
    echo "PASS: 8 concurrent workers, 200 increments, first-insert race, consistent fields\n";
} finally {
    $db->query($db->delete('table.fields')->where('cid = ?', $cid));
    $db->query($db->delete('table.contents')->where('cid = ?', $cid));
}
