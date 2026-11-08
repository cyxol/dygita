<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function dygita_resolve_thumbnail_url($thumbValue, $contentHtml, $options) {
    if ($thumbValue && preg_match('/^https?:\/\//i', $thumbValue)) {
        return htmlspecialchars($thumbValue, ENT_QUOTES, 'UTF-8');
    }
    if ($contentHtml && preg_match('/<img.+?src=["\']([^"\']+)["\']/', $contentHtml, $match)
        && preg_match('/^https?:\/\//i', $match[1])) {
        return htmlspecialchars($match[1], ENT_QUOTES, 'UTF-8');
    }
    return dygita_get_random_placeholder_url($options);
}

function dygita_get_thumbnail($widget)
{
    // 优先：自定义字段 thumb → 附件图片 → 正文第一图
    $thumbValue = '';
    if ($widget->fields->thumb) {
        $thumbValue = $widget->fields->thumb;
    } elseif (($attach = $widget->attachments(1)->attachment) && $attach && $attach->isImage) {
        $thumbValue = $attach->url;
    }
    return dygita_resolve_thumbnail_url($thumbValue, $widget->content, $widget->widget('Widget_Options'));
}

/**
 * 选项读取兼容：优先读 dygita_*，无则回退到 git_*（便于从 git_* 迁移到 dygita_*）
 * @param mixed $options
 * @param string $newKey 新选项名，如 dygita_skin_b
 * @param string|null $oldKey 旧选项名，如 git_skin_b，null 则只读 newKey
 * @return mixed
 */
function dygita_opt($options, $newKey, $oldKey = null) {
    $v = isset($options->$newKey) && $options->$newKey !== '' && $options->$newKey !== null ? $options->$newKey : null;
    if ($v !== null) {
        return $v;
    }
    if ($oldKey !== null && isset($options->$oldKey) && $options->$oldKey !== '' && $options->$oldKey !== null) {
        return $options->$oldKey;
    }
    return null;
}










/**
 * 获取随机占位图 URL（统一路径，便于修改）
 * @param \Widget_Options $options
 * @return string
 */
function dygita_get_random_placeholder_url($options) {
    $random = mt_rand(1, 12);
    return rtrim($options->themeUrl, '/') . '/img/pic/' . $random . '.jpg';
}

/**
 * 主题色 key → 颜色映射（集中维护）
 * @param string $skinKey 如 git_skin_b 的值
 * @return array ['nom' => '#xxx', 'hover' => '#xxx']
 */
function dygita_get_skin_colors($skinKey) {
    $map = array(
        'git_blue_b'   => array('nom' => '#003399', 'hover' => '#002266'),
        'git_black_b'  => array('nom' => '#616161', 'hover' => '#474747'),
        'git_purple_b' => array('nom' => '#9932CC', 'hover' => '#7B28A4'),
        'git_yellow_b' => array('nom' => '#f5e011', 'hover' => '#C9B508'),
        'git_light_b'  => array('nom' => '#03A9F4', 'hover' => '#2196F3'),
        'git_green_b'  => array('nom' => '#4CAF50', 'hover' => '#388E3C'),
    );
    $default = array('nom' => '#E74C3C', 'hover' => '#D52D1A');
    return isset($map[$skinKey]) ? $map[$skinKey] : $default;
}

/**
 * 输出主题色 CSS 变量声明（供 header :root 内联使用）
 * 选择器规则在 css/base/skin.css 中，通过 var() 引用这两个变量。
 * @param string $skinKey 如 $this->options->dygita_skin_b
 * @return string 两行 CSS 变量声明
 */
function dygita_get_theme_skin_css($skinKey) {
    $c = dygita_get_skin_colors($skinKey);
    return "--dygita-skin-color:{$c['nom']};--dygita-skin-hover:{$c['hover']};";
}

/**
 * 获取保存的主题偏好（用于 data-theme / 顶栏色等）
 * @return array ['theme' => string, 'headerColor' => string]
 */
function dygita_get_saved_theme_prefs() {
    $options = Typecho\Widget::widget('Widget_Options');
    $theme = '';
    $headerColor = '';

    if (isset($options->dygita_theme) && $options->dygita_theme !== null && $options->dygita_theme !== '') {
        $theme = trim((string) $options->dygita_theme);
    }
    if (isset($options->dygita_headerColor) && $options->dygita_headerColor !== null && $options->dygita_headerColor !== '') {
        $headerColor = trim((string) $options->dygita_headerColor);
    }

    // 访客兜底：从 Cookie 读取，避免仅 localStorage 导致首屏主题闪烁
    if (isset($_COOKIE['dygita_theme_pref'])) {
        $cookieTheme = trim((string) $_COOKIE['dygita_theme_pref']);
        if ($cookieTheme === 'light' || $cookieTheme === 'dark') {
            $theme = $cookieTheme;
        }
    }
    if (isset($_COOKIE['dygita_header_color'])) {
        $cookieHeaderColor = trim((string) $_COOKIE['dygita_header_color']);
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $cookieHeaderColor)) {
            $headerColor = $cookieHeaderColor;
        }
    }

    return array('theme' => $theme, 'headerColor' => $headerColor);
}

/**
 * 获取前端 CONFIG 对象（供 header 内联脚本输出）
 * @param \Widget_Options $options
 * @return array
 */
function dygita_get_config_array($options) {
    $hostname = $options->siteUrl;
    return array(
        'hostname' => $hostname,
        'preferenceUrl' => \Typecho\Router::url('do', ['action' => 'dygita-preference'], $options->index),
        'preferenceToken' => \Widget\Security::alloc()->getToken('dygita-preference'),
        'canSavePreference' => \Widget\User::alloc()->pass('administrator', true),
        'likeUrl' => \Typecho\Router::url('do', array('action' => 'dygita-like'), $options->index),
        'root' => '/',
        'exturl' => false,
        'cdn' => array(
            'provider' => dygita_opt($options, 'dygita_cdn_provider', 'git_cdn_provider') ?: 'jsdelivr'
        ),
        'sidebar' => array(
            'position' => 'right',
            'width' => 360,
            'display' => 'post',
            'padding' => 18,
            'offset' => 12,
            'onmobile' => false
        ),
        'back2top' => array(
            'enable' => true,
            'sidebar' => false,
            'scrollpercent' => true
        ),
        'copycode' => array(
            'enable' => true,
            'show_result' => true,
            'style' => 'mac'
        ),
        'localsearch' => array(
            'enable' => true,
            'trigger' => 'auto',
            'top_n_per_article' => 10,
            'unescape' => false,
            'preload' => false
        ),
        'motion' => array(
            'enable' => false,
            'async' => false,
            'transition' => array(
                'post_block' => 'bounceDownIn',
                'post_header' => 'slideDownIn',
                'post_body' => 'slideDownIn',
                'coll_header' => 'slideLeftIn',
                'sidebar' => 'slideUpIn'
            )
        )
    );
}






/**
 * 缓存导航链接配置（请求级静态缓存）
 * 解决 header.php 中每次请求都 json_decode navLinks 的性能问题
 * @param string|null $navLinksJson navLinks 字段的原始 JSON 字符串，传 null 时自动获取
 * @return array 解析后的配置数组 ['links' => [...]]
 */
function dygita_get_nav_links_cached($navLinksJson = null) {
    static $parsedCache = null;
    static $sourceHash = null;

    if ($navLinksJson === null) {
        $options = \Widget\Options::alloc();
        $navLinksJson = isset($options->navLinks) ? (string) $options->navLinks : '';
    }

    $currentHash = md5($navLinksJson);
    if ($parsedCache === null || $sourceHash !== $currentHash) {
        $parsedCache = json_decode($navLinksJson, true);
        if (!is_array($parsedCache)) {
            $parsedCache = ['links' => []];
        }
        if (!isset($parsedCache['links']) || !is_array($parsedCache['links'])) {
            $parsedCache['links'] = [];
        }
        $sourceHash = $currentHash;
    }

    return $parsedCache;
}




/* 增加: 相关文章缩略图获取（兼容数组与 Widget 对象） */
function dygita_get_related_post_thumbnail($post)
{
    $db = Typecho\Db::get();
    $options = Typecho\Widget::widget('Widget_Options');
    $cid = is_array($post) ? intval($post['cid'] ?? 0) : intval($post->cid ?? 0);
    $rawText = is_array($post) ? (string)($post['text'] ?? '') : (string)($post->text ?? '');
    if ($cid <= 0) {
        return dygita_get_random_placeholder_url($options);
    }

    $thumbValue = '';
    $fieldsTable = dygita_get_table('fields');
    $thumb = $db->fetchRow($db->select('str_value')->from($fieldsTable)
        ->where('cid = ?', $cid)
        ->where('name = ?', 'thumb'));
    if ($thumb && !empty($thumb['str_value'])) {
        $thumbValue = $thumb['str_value'];
    }

    // 将 Markdown 源文本转换为 HTML，以便正则能匹配 <img> 标签
    $contentHtml = $rawText;
    if (strpos($rawText, '<!--markdown-->') === 0) {
        $contentHtml = \Utils\Markdown::convert(substr($rawText, 15));
    }

    return dygita_resolve_thumbnail_url($thumbValue, $contentHtml, $options);
}



/* 增加: 处理点赞请求和自定义路由 */
// 此函数已移至文件末尾，与标签云页面处理逻辑合并











/**
 * 解析友情链接文本，返回结构化数组
 * 每行格式：名称|URL|描述|备注
 * @param string $linksText
 * @return array
 */
function dygita_parse_links($linksText) {
    $result = array();
    if (!$linksText) return $result;
    foreach (preg_split('/\r?\n/', $linksText) as $line) {
        $parts = explode('|', trim($line));
        if (count($parts) >= 2) {
            $result[] = array(
                'name'        => trim($parts[0]),
                'url'         => trim($parts[1]),
                'description' => isset($parts[2]) ? trim($parts[2]) : '',
                'notes'       => isset($parts[3]) ? trim($parts[3]) : '',
            );
        }
    }
    return $result;
}

function dygita_get_links() {
    $options = Typecho\Widget::widget('Widget_Options');
    foreach (dygita_parse_links($options->links) as $link) {
        $name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
        $url  = htmlspecialchars($link['url'],  ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($link['description'], ENT_QUOTES, 'UTF-8');
        echo '<li><a href="' . $url . '" title="' . $desc . '" target="_blank" rel="noopener">' . $name . '</a></li>';
    }
}

/**
 * 获取博主头像 URL（优先使用后台配置，回退到默认图片）
 * @param object $options
 * @return string
 */
function dygita_get_author_avatar($options) {
    $avatar = isset($options->authorAvatar) && $options->authorAvatar !== '' ? trim((string) $options->authorAvatar) : '';
    if ($avatar !== '' && preg_match('/^https?:\/\//i', $avatar)) {
        return htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8');
    }
    return rtrim($options->themeUrl, '/') . '/img/caiya.xin.jpg';
}

/**
 * 获取博主显示名称（优先使用后台配置，回退到站点标题）
 * @param object $options
 * @return string
 */
function dygita_get_author_name($options) {
    $name = isset($options->authorName) && $options->authorName !== '' ? trim((string) $options->authorName) : '';
    return $name !== '' ? $name : (string) $options->title;
}

/**
 * 获取侧边栏资源链接列表（从后台配置读取，未配置时使用默认值）
 */
function dygita_get_resource_links() {
    $options = Typecho\Widget::widget('Widget_Options');
    $text = isset($options->resourceLinks) ? (string) $options->resourceLinks : '';
    // 兜底：未配置时使用默认链接（主题已激活但未重新保存设置时）
    if ($text === '') {
        $text = "🤗 Hugging Face|https://huggingface.co/|AI 模型/数据集平台\n📊 Kaggle|https://www.kaggle.com/|数据科学竞赛与数据集\n📄 Papers With Code|https://paperswithcode.com/|AI 论文与代码\n🧠 OpenAI|https://openai.com/|ChatGPT / GPT-4\n✦ Anthropic|https://www.anthropic.com/|Claude AI\n🔍 Google AI|https://ai.google/|Gemini / DeepMind";
    }
    foreach (dygita_parse_links($text) as $link) {
        $name = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
        $url  = htmlspecialchars($link['url'],  ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($link['description'], ENT_QUOTES, 'UTF-8');
        echo '<li><a href="' . $url . '" target="_blank" rel="noopener" title="' . $desc . '">' . $name . '</a></li>';
    }
}

/* 增加: 主题版本控制和更新提示 */
function dygita_theme_version() {
    return '1.1.0';
}

function dygita_check_update() {
    // 这里可以添加更新检查逻辑
    // 例如：从远程服务器获取最新版本信息并与当前版本比较
    // 暂时返回 false，表示无更新
    return false;
}



/** 主题语言切换：允许值 zh_CN / en_US */
define('DYGITA_LANG_COOKIE', 'dygita_lang');

/**
 * 获取当前主题显示语言（cookie 优先，否则与后台一致）
 * @return string zh_CN | en_US
 */
function dygita_current_lang() {
    $lang = Typecho\Cookie::get(DYGITA_LANG_COOKIE);
    if ($lang === 'zh_CN' || $lang === 'en_US') {
        return $lang;
    }
    $options = Typecho\Widget::widget('Widget_Options');
    $optionsLang = $options->lang ?? 'zh_CN';
    return ($optionsLang === 'en_US') ? 'en_US' : 'zh_CN';
}

/**
 * 主题前端翻译：根据当前语言（cookie/选项）返回翻译字符串
 * @param string $key 原始字符串（中文键）
 * @return string 翻译结果，找不到时返回原键
 */
function dygita_t($key) {
    static $langData = null;
    if ($langData === null) {
        $lang = dygita_current_lang();
        $langFile = dirname(__FILE__) . '/views/languages/' . $lang . '.php';
        if (file_exists($langFile)) {
            $langData = include $langFile;
        }
        if (!is_array($langData)) {
            $langData = [];
        }
    }
    return isset($langData[$key]) ? (string) $langData[$key] : $key;
}

/**
 * 主题前端翻译并输出（已转义）
 * @param string $key 原始字符串
 */
function dygita_e($key) {
    echo htmlspecialchars(dygita_t($key), ENT_QUOTES, 'UTF-8');
}

// 主题初始化函数




/**
 * 统一获取库文件的 CDN URL
 * @param string $package 库名称 (如 swiper, prism)
 * @param string $version 库版本
 * @param string $path 文件路径
 * @return string
 */
function dygita_cdn_url($package, $version, $path) {
    $options = \Typecho\Widget::widget('Widget_Options');
    $provider = dygita_opt($options, 'dygita_cdn_provider', 'git_cdn_provider') ?: 'jsdelivr';

    switch ($provider) {
        case 'cdnjs':
            return "https://cdnjs.cloudflare.com/ajax/libs/{$package}/{$version}/{$path}";
        case 'bootcdn':
            return "https://cdn.bootcdn.net/ajax/libs/{$package}/{$version}/{$path}";
        case 'staticfile':
            return "https://cdn.staticfile.net/{$package}/{$version}/{$path}";
        case 'local':
            return $options->themeUrl . "/vendor/" . strtolower($package) . "/{$path}";
        case 'jsdelivr':
        default:
            // jsDelivr uses NPM package names, which sometimes differ from CDNJS
            $npmPackage = strtolower($package);
            $npmVersion = $version;
            if ($npmPackage === 'prism') {
                $npmPackage = 'prismjs';
                $npmVersion = '1';
            } elseif ($npmPackage === 'swiper') {
                $npmVersion = '8';
            }
            return "https://cdn.jsdelivr.net/npm/{$npmPackage}@{$npmVersion}/{$path}";
    }
}


