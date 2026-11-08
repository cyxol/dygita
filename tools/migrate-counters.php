<?php
if (PHP_SAPI !== 'cli') exit;
if (getenv('DYGITA_TEST_DB')) {
    require __DIR__ . '/test-bootstrap.php';
} else {
    define('__TYPECHO_ROOT_URL__', 'http://localhost');
    require dirname(__DIR__, 4) . '/config.inc.php';
}
$db = \Typecho\Db::get(); $prefix = $db->getPrefix();
// Legacy templates displayed str_value. Preserve that value before making int_value authoritative.
$db->query("UPDATE `{$prefix}fields` SET int_value=CASE
    WHEN str_value REGEXP '^[0-9]+$' THEN LEAST(CAST(str_value AS UNSIGNED),2147483646)
    ELSE GREATEST(int_value,0) END, str_value=CAST(int_value AS CHAR), type='int'
    WHERE name IN ('likes','views') AND type<>'int'", \Typecho\Db::WRITE);
foreach (['contents'=>['dygita_public_created','type,status,created'],
    'fields'=>['dygita_name_counter','name,int_value,cid'],
    'relationships'=>['dygita_meta_content','mid,cid']] as $table=>$index) {
    $indexes = $db->fetchAll("SHOW INDEX FROM `{$prefix}{$table}`");
    if (!in_array($index[0], array_column($indexes, 'Key_name'), true)) {
        $db->query("ALTER TABLE `{$prefix}{$table}` ADD INDEX `{$index[0]}` ({$index[1]})", \Typecho\Db::WRITE);
    }
}
echo "Counters normalized; supporting indexes installed. Safe to rerun.\n";
