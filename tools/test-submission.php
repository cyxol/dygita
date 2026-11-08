<?php
require __DIR__ . '/test-bootstrap.php';
$db=\Typecho\Db::get(); $ids=[];
try {
    $unsafe='<p onclick="void(0)" style="color:red">Safe<strong>format</strong></p><script>unsafe()</script><svg onload="void(0)"></svg>';
    $clean=Dygita_Submission::sanitize($unsafe);
    check(!preg_match('/onclick|onload|style=|script|svg|unsafe\(/i', $clean), 'Unsafe HTML retained');
    check(str_contains($clean, '<strong>format</strong>'), 'Safe formatting lost');
    for ($i=0;$i<2;$i++) $ids[]=Dygita_Submission::create('Same title', $unsafe . str_repeat('safe content ',20), '<sender>');
    $rows=$db->fetchAll($db->select()->from('table.contents')->where('cid IN ?', $ids));
    check(count($rows)===2 && $rows[0]['slug']!==$rows[1]['slug'], 'Duplicate titles cannot be submitted');
    foreach($rows as $row) {
        check($row['type']==='post_draft' && $row['status']==='waiting', 'Submission was published or not a core draft');
        check(!str_contains($row['text'], 'onclick') && str_contains($row['text'], '&lt;sender&gt;'), 'Stored submission is unsafe');
        check(!Dygita_Repository::publicPost((int)$row['cid']), 'Submission publicly visible');
    }
    echo "PASS: attribute/script sanitization, safe formatting, duplicate titles, draft moderation, public exclusion\n";
} finally {
    foreach($ids as $cid) $db->query($db->delete('table.contents')->where('cid = ?', $cid));
}
