<?php
namespace TypechoPlugin\DygitaRuntime;
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * Loads Dygita services before frontend actions and admin requests.
 * @package DygitaRuntime
 * @version 1.0.0
 */
class Plugin implements \Typecho\Plugin\PluginInterface {
    public static function activate() {
        \Typecho\Plugin::factory('index.php')->begin = [self::class, 'boot'];
        \Typecho\Plugin::factory('admin/common.php')->begin = [self::class, 'boot'];
    }
    public static function deactivate() {}
    public static function config(\Typecho\Widget\Helper\Form $form) {}
    public static function personalConfig(\Typecho\Widget\Helper\Form $form) {}
    public static function boot() {
        if (\Widget\Options::alloc()->theme !== 'dygita') return;
        require_once __TYPECHO_ROOT_DIR__ . '/usr/themes/dygita/functions.php';
    }
}
