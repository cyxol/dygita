<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
function dygita_asset_url(string $path): string {
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $url = \Typecho\Common::url($path, \Widget\Options::alloc()->themeUrl);
    return $url . '?v=' . (is_file($file) ? substr(hash_file('sha256', $file), 0, 16) : DYGITA_THEME_VERSION);
}
