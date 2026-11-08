<?php
if (PHP_SAPI !== 'cli') exit;
define('__TYPECHO_ROOT_URL__', 'http://localhost');
require dirname(__DIR__, 4) . '/config.inc.php';
\Widget\Init::alloc();
require dirname(__DIR__) . '/functions.php';
$destination = __TYPECHO_ROOT_DIR__ . '/usr/plugins/DygitaRuntime';
if (!is_dir($destination) && !mkdir($destination, 0755, true)) throw new RuntimeException('Cannot create plugin directory');
if (!copy(dirname(__DIR__) . '/integration/DygitaRuntime/Plugin.php', $destination . '/Plugin.php')) throw new RuntimeException('Cannot install plugin');
$plugins = \Typecho\Plugin::export();
if (!isset($plugins['activated']['DygitaRuntime'])) {
    \TypechoPlugin\DygitaRuntime\Plugin::activate();
    \Typecho\Plugin::activate('DygitaRuntime');
    \Utils\Helper::setOption('plugins', \Typecho\Plugin::export());
}
themeActivate();
echo "Dygita runtime installed; routes and actions registered.\n";
