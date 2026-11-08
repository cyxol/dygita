<?php
require __DIR__ . '/test-bootstrap.php';
$db = \Typecho\Db::get();
$needle = 'DygitaReview' . bin2hex(random_bytes(6));
$cid = null;
try {
    $cid = \Widget\Base\Contents::alloc()->insert(['title'=>$needle, 'text'=>str_repeat('body ', 16000) . $needle,
        'type'=>'post', 'status'=>'publish', 'authorId'=>1]);
    check(count(Dygita_Repository::search($needle)) === 1, 'Published post missing');
    foreach ([['password'=>'secret'],['password'=>'','status'=>'private'],['status'=>'publish','type'=>'post_draft']] as $changes) {
        $db->query($db->update('table.contents')->rows($changes)->where('cid = ?', $cid));
        check(Dygita_Repository::search($needle) === [], 'Nonpublic article leaked');
    }
    $db->query($db->update('table.contents')->rows(['type'=>'post','title'=>'Renamed','text'=>'New' . $needle])->where('cid = ?', $cid));
    check(count(Dygita_Repository::search('New' . $needle)) === 1, 'New content missing');
    $db->query($db->delete('table.contents')->where('cid = ?', $cid));
    check(Dygita_Repository::search('New' . $needle) === [], 'Deleted post remained');
    check(Dygita_Repository::search('') === [], 'Empty search must be bounded');
    echo "PASS: publish, password/private/draft, update, delete, long content, empty query\n";
} finally { if ($cid) $db->query($db->delete('table.contents')->where('cid = ?', $cid)); }
