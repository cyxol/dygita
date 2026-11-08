<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function dygita_register_routes() {
    $rt = \Widget\Options::alloc()->routingTable;
    \Utils\Helper::addRoute('tags_cloud', '/tags/', '\Widget\Archive', 'render');
    
    \Utils\Helper::addRoute('tags_cloud_tag_plain', '/tags', '\Widget\Archive', 'render');
    if (!isset($rt['tags_cloud_page']) && !(isset($rt[0]) && isset($rt[0]['tags_cloud_page']))) {
        \Utils\Helper::addRoute('tags_cloud_page', '/page-tag-cloud.html', '\Widget\Archive', 'render');
    }
    if (!isset($rt['archives_list']) && !(isset($rt[0]) && isset($rt[0]['archives_list']))) {
        \Utils\Helper::addRoute('archives_list', '/archives/', '\Widget\Archive', 'render');
    }
    \Utils\Helper::addRoute('categories_page', '/categories/', '\Widget\Archive', 'render');
}

function themeActivate() {
    dygita_register_routes();
    dygita_register_actions();
    dygita_register_search_action();
    \Utils\Helper::addAction('dygita-preference', 'Dygita_Action_Preference');
}

function dygita_register_actions() {
    $actionTable = \Typecho\Widget::widget('Widget_Options')->actionTable;
    if (empty($actionTable['dygita-like'])) {
        \Utils\Helper::addAction('dygita-like', 'Dygita_Action_Like');
    }
}

function dygita_register_search_action() {
    $actionTable = \Typecho\Widget::widget('Widget_Options')->actionTable;
    if (empty($actionTable['dygita-search-index'])) {
        \Utils\Helper::addAction('dygita-search-index', 'Dygita_Action_Search_Index');
    }
}

function themeInit($archive) {

    // 一键切换语言：?dygita_lang=zh_CN 或 ?dygita_lang=en_US → 写 cookie 并重定向到当前页（去掉参数）
    // 直接读 $_GET 而非 $archive->request->get()，避免 Typecho sandbox 路由注入导致 $_GET 被跳过
    $langParam = $_GET[DYGITA_LANG_COOKIE] ?? null;
    if ($langParam === 'zh_CN' || $langParam === 'en_US') {
        Typecho\Cookie::set(DYGITA_LANG_COOKIE, $langParam);
        $url = $archive->request->getRequestUrl();
        $url = preg_replace('#[?&]dygita_lang=(?:zh_CN|en_US)(?=&|$)#', '', $url);
        if (preg_match('#^([^?]*)\&(.+)$#', $url, $m)) {
            $url = $m[1] . '?' . $m[2];
        }
        $url = rtrim($url, '?');
        $archive->response->redirect($url);
        exit;
    }

    $routeType = '';
    if (isset($archive->parameter)) {
        if (is_object($archive->parameter) && isset($archive->parameter->type)) {
            $routeType = (string) $archive->parameter->type;
        } elseif (is_array($archive->parameter) && isset($archive->parameter['type'])) {
            $routeType = (string) $archive->parameter['type'];
        }
    }

    // 自定义路由模板：标签云页面
    if ($routeType === 'tags_cloud' || $routeType === 'tags_cloud_page') {
        $archive->setThemeFile('tag.php');
        return;
    }

    // 自定义路由模板：文章列表页面（/archives）
    if ($routeType === 'archives_list') {
        $archive->setThemeFile('archive.php');
        return;
    }

    // 自定义路由模板：分类目录页面（/category）
    if ($routeType === 'categories_page') {
        $archive->setThemeFile('category.php');
        return;
    }

    // 兜底：部分环境下 parameter type 不可用时按 path 匹配
    $pathInfo = trim((string) $archive->request->getPathInfo(), '/');
    if ($pathInfo === 'archives') {
        $archive->setThemeFile('archive.php');
        return;
    }
    if ($pathInfo === 'tags' || $pathInfo === 'page-tag-cloud.html') {
        $archive->setThemeFile('tag.php');
        return;
    }
    if ($pathInfo === 'category' || $pathInfo === 'categories') {
        $archive->setThemeFile('category.php');
        return;
    }


}

function themeDeactivate() {
    // 清理所有注册的 Action
    \Utils\Helper::removeAction('dygita-search-index');
    \Utils\Helper::removeAction('dygita-like');
    \Utils\Helper::removeAction('dygita-preference');

    // 清理所有注册的自定义路由
    \Utils\Helper::removeRoute('tags_cloud');
    \Utils\Helper::removeRoute('tags_cloud_tag_plain');
    \Utils\Helper::removeRoute('tags_cloud_page');
    \Utils\Helper::removeRoute('archives_list');
    \Utils\Helper::removeRoute('categories_page');
}
