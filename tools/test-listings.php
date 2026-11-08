<?php
require __DIR__ . '/test-bootstrap.php';
$db=\Typecho\Db::get(); $ids=[];
try {
    $base=Dygita_Repository::countPublic();
    for($i=0;$i<105;$i++) {
        $ids[]=\Widget\Base\Contents::alloc()->insert(['title'=>'Archive test ' . $i,'type'=>'post','status'=>'publish','authorId'=>1]);
    }
    check(Dygita_Repository::countPublic()===$base+105, 'Archive total incorrect');
    $first=dygita_get_archive_posts(1,50);$second=dygita_get_archive_posts(2,50);
    check(count($first)===50 && count($second)===50, 'Archive page size incorrect');
    check(!array_intersect(array_column($first,'cid'),array_column($second,'cid')), 'Pages overlap');
    $cid=$ids[0];
    $db->query($db->update('table.contents')->rows(['password'=>'secret'])->where('cid = ?', $cid));
    check(Dygita_Repository::countPublic()===$base+104, 'Protected post counted');
    check(dygita_get_download_data($cid)===null, 'Protected downloads exposed');
    echo "PASS: paginated listings, totals, nonoverlapping pages, protected download exclusion\n";
} finally {
    if($ids) $db->query($db->delete('table.contents')->where('cid IN ?', $ids));
}
