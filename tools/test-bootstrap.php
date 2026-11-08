<?php
// Integration tests must explicitly target the disposable database, never the blog.
if (PHP_SAPI !== 'cli' || getenv('DYGITA_TEST_DB') !== 'typecho_review_20261108') {
    throw new RuntimeException('Set DYGITA_TEST_DB to the disposable test database');
}
define('__TYPECHO_ROOT_URL__', 'http://localhost');
require dirname(__DIR__, 4) . '/config.inc.php';
$original = \Typecho\Db::get();
$config = clone $original->getConfig(\Typecho\Db::WRITE);
$config->database = getenv('DYGITA_TEST_DB');
$testDb = new \Typecho\Db($original->getAdapterName(), $original->getPrefix());
$testDb->addConfig($config, \Typecho\Db::READ | \Typecho\Db::WRITE);
\Typecho\Db::set($testDb);
\Widget\Init::alloc();
set_exception_handler(function ($error) {
    fwrite(STDERR, get_class($error) . ': ' . $error->getMessage() . "\n" . $error->getTraceAsString() . "\n");
    exit(1);
});
require dirname(__DIR__) . '/functions.php';
function check($condition, $message) { if (!$condition) throw new RuntimeException($message); }
