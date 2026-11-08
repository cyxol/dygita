<?php
define('__TYPECHO_ROOT_DIR__', dirname(__DIR__, 4));
require dirname(__DIR__) . '/lib/Catalog.php';
function check($ok, $message) { if (!$ok) throw new RuntimeException($message); }
$article = (object) ['cid'=>1, 'content'=>'<h2>Public heading</h2>' . str_repeat('body ', 20000), 'hidden'=>false];
$result = Dygita_Catalog_Cache::render($article);
check(strlen($result['parsed']) > 65535, 'Long article truncated');
check(str_contains($result['catalog'], 'Public heading'), 'Missing heading');
$article->hidden = true; $article->content = '<form>Password required</form>';
$protected = Dygita_Catalog_Cache::render($article);
check(!str_contains($protected['parsed'], 'Public heading') && $protected['catalog'] === '', 'Protected content leaked');
$article->hidden = false; $article->content = '<h2>Updated heading</h2>';
check(str_contains(Dygita_Catalog_Cache::render($article)['catalog'], 'Updated heading'), 'Stale content reused');
echo "PASS: long content, visibility change, updated content\n";
