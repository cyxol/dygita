<?php
if (!defined('__TYPECHO_ROOT_DIR__'))
    exit;

/**
 * Dygita Theme
 *
 * @package Dygita
 * @author Yacine Tsai
 * @version 1.1.0
 * @link http://caiya.xin
 */

// 主题版本
define('DYGITA_THEME_VERSION', '1.1.0');
require_once __DIR__ . '/lib/Assets.php';
require_once __DIR__ . '/lib/Repository.php';
require_once __DIR__ . '/lib/Search.php';
require_once __DIR__ . '/lib/Preference.php';
require_once __DIR__ . '/lib/Submission.php';

/**
 * 注册主题自定义路由（统一入口，避免重复定义）
 */
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

/**
 * 主题激活时注册路由与动作（避免常驻全局作用域每次请求执行）
 */
function themeActivate() {
    dygita_register_routes();
    dygita_register_actions();
    dygita_register_search_action();
    \Utils\Helper::addAction('dygita-preference', 'Dygita_Action_Preference');
}

/**
 * 运行期兜底注册（仅首次执行），兼容老站点未重新激活主题的场景
 */
function dygita_bootstrap_runtime() {
    // Initialization is installed by tools/install-runtime.php, never during reads.
}

/**
 * 点赞 Action — 通过 /action/dygita-like 路由处理 AJAX 点赞请求
 */
require_once __DIR__ . '/lib/Like.php';

/**
 * 注册点赞 Action（仅在未注册时写库，避免每次请求都写 DB）
 */
function dygita_register_actions() {
    $actionTable = \Typecho\Widget::widget('Widget_Options')->actionTable;
    if (empty($actionTable['dygita-like'])) {
        \Utils\Helper::addAction('dygita-like', 'Dygita_Action_Like');
    }
}

/**
 * 文章目录功能
 */
require_once __DIR__ . '/lib/Catalog.php';

function themeConfig($form)
{
    // 获取全局配置（注意：在函数上下文中不能使用 $this->options）
    $options = Typecho\Widget::widget('Widget_Options');
    // 侧边栏显示控制
    $sidebarBlock = new Typecho\Widget\Helper\Form\Element\Checkbox('sidebarBlock',
        array(
        'ShowCategory' => _t('显示分类'),
        ),
        array('ShowCategory'), _t('侧边栏显示'));

    $form->addInput($sidebarBlock->multiMode());

    // 主题色 dygita_skin_b（兼容 git_skin_b）
    $skinCur = dygita_opt($options, 'dygita_skin_b', 'git_skin_b') ?: 'git_red_b';
    $dygita_skin_b = new Typecho\Widget\Helper\Form\Element\Radio('dygita_skin_b',
        array(
        'git_red_b' => _t('红色'),
        'git_blue_b' => _t('蓝色'),
        'git_black_b' => _t('黑色'),
        'git_purple_b' => _t('紫色'),
        'git_yellow_b' => _t('黄色'),
        'git_light_b' => _t('浅蓝'),
        'git_green_b' => _t('绿色'),
    ),
        $skinCur, _t('主题配色'), _t('选择主题的配色方案')
        );
    $form->addInput($dygita_skin_b);

    // 深色模式支持
    $colorSchema = new Typecho\Widget\Helper\Form\Element\Select(
        'colorSchema',
        array(
            null => _t('自动'),
            'light' => _t('浅色'),
            'dark' => _t('深色'),
        ),
        null,
        _t('外观风格'),
        _t('如果选择了自动，主题将根据系统设置自动切换浅色/深色模式')
    );
    $form->addInput($colorSchema);

    // Swiper.js 幻灯片配置
    $swiperEnabled = new Typecho\Widget\Helper\Form\Element\Radio('swiperEnabled',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('开启Swiper幻灯片'), _t('是否开启首页Swiper幻灯片')
        );
    $form->addInput($swiperEnabled);
    
    $swiperSlides = new Typecho\Widget\Helper\Form\Element\Textarea('swiperSlides', NULL, '{"slides":[{"image":"img/pic/1.jpg","title":"欢迎访问","link":""},{"image":"img/pic/2.jpg","title":"现代设计","link":""},{"image":"img/pic/3.jpg","title":"响应式布局","link":""},{"image":"img/pic/4.jpg","title":"技术创新","link":""},{"image":"img/pic/5.jpg","title":"创意设计","link":""}]}', _t('幻灯片配置'), _t('JSON格式，包含图片路径、标题和链接，例如：{"slides":[{"image":"img/pic/1.jpg","title":"标题1","link":"链接1"}]}'));
    $form->addInput($swiperSlides);
    
    $swiperAutoplay = new Typecho\Widget\Helper\Form\Element\Radio('swiperAutoplay',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('自动播放'), _t('是否开启幻灯片自动播放')
        );
    $form->addInput($swiperAutoplay);
    
    $swiperSpeed = new Typecho\Widget\Helper\Form\Element\Text('swiperSpeed', NULL, '1000', _t('切换速度'), _t('幻灯片切换速度，单位毫秒，默认1000'));
    $form->addInput($swiperSpeed);
    
    $swiperDelay = new Typecho\Widget\Helper\Form\Element\Text('swiperDelay', NULL, '3000', _t('自动播放延迟'), _t('幻灯片自动播放延迟时间，单位毫秒，默认3000'));
    $form->addInput($swiperDelay);
    
    // 导航栏配置
    $navLinksEnabled = new Typecho\Widget\Helper\Form\Element\Radio('navLinksEnabled',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('开启自定义导航链接'), _t('是否开启自定义导航链接')
        );
    $form->addInput($navLinksEnabled);
    
    $navLinks = new Typecho\Widget\Helper\Form\Element\Textarea('navLinks', NULL, '{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"},{"name":"标签云","url":"tag","target":"_self"}]}', _t('导航链接配置'), _t('JSON格式，包含导航链接名称、URL和目标。特殊URL：@author 自动检测作者页面。例如：{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"}]}'));
    $form->addInput($navLinks);
    
    // 友情链接设置
    $links = new Typecho\Widget\Helper\Form\Element\Textarea('links', NULL, NULL, _t('友情链接'), _t('格式：名称|链接|描述（可选），每行一个'));
    $form->addInput($links);
    
    // 评论系统设置
    $commentSystem = new Typecho\Widget\Helper\Form\Element\Select(
        'commentSystem',
        array(
            'default' => _t('默认评论系统'),
            'gitalk' => _t('Gitalk'),
            'valine' => _t('Valine'),
            'disqus' => _t('Disqus')
        ),
        'default',
        _t('评论系统'),
        _t('选择网站使用的评论系统')
    );
    $form->addInput($commentSystem);
    
    // Gitalk 配置
    $gitalkClientID = new Typecho\Widget\Helper\Form\Element\Text('gitalkClientID', NULL, NULL, _t('Gitalk Client ID'), _t('GitHub OAuth Application Client ID'));
    $form->addInput($gitalkClientID);
    
    $gitalkClientSecret = new Typecho\Widget\Helper\Form\Element\Text('gitalkClientSecret', NULL, NULL, _t('Gitalk Client Secret'), _t('GitHub OAuth Application Client Secret'));
    $form->addInput($gitalkClientSecret);
    
    $gitalkRepo = new Typecho\Widget\Helper\Form\Element\Text('gitalkRepo', NULL, NULL, _t('Gitalk Repo'), _t('GitHub 仓库名'));
    $form->addInput($gitalkRepo);
    
    $gitalkOwner = new Typecho\Widget\Helper\Form\Element\Text('gitalkOwner', NULL, NULL, _t('Gitalk Owner'), _t('GitHub 用户名'));
    $form->addInput($gitalkOwner);
    
    // Valine 配置
    $valineAppId = new Typecho\Widget\Helper\Form\Element\Text('valineAppId', NULL, NULL, _t('Valine App ID'), _t('LeanCloud Application ID'));
    $form->addInput($valineAppId);
    
    $valineAppKey = new Typecho\Widget\Helper\Form\Element\Text('valineAppKey', NULL, NULL, _t('Valine App Key'), _t('LeanCloud Application Key'));
    $form->addInput($valineAppKey);
    
    $valineAvatar = new Typecho\Widget\Helper\Form\Element\Text('valineAvatar', NULL, 'mp', _t('Valine Avatar'), _t('评论者头像样式，可选值：mp, identicon, monsterid, wavatar, robohash, retro, hide'));
    $form->addInput($valineAvatar);
    
    // Disqus 配置
    $disqusShortname = new Typecho\Widget\Helper\Form\Element\Text('disqusShortname', NULL, NULL, _t('Disqus Shortname'), _t('Disqus 站点标识符'));
    $form->addInput($disqusShortname);
    
    // 站点统计配置
    $baiduAnalytics = new Typecho\Widget\Helper\Form\Element\Text('baiduAnalytics', NULL, NULL, _t('百度统计代码'), _t('百度统计的站点ID，例如：8a7b6c5d4e3f2g1h'));
    $form->addInput($baiduAnalytics);
    
    $googleAnalytics = new Typecho\Widget\Helper\Form\Element\Text('googleAnalytics', NULL, NULL, _t('Google Analytics 代码'), _t('Google Analytics 的跟踪ID，例如：UA-12345678-9'));
    $form->addInput($googleAnalytics);
    
    $enableStatistics = new Typecho\Widget\Helper\Form\Element\Radio('enableStatistics',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '0', _t('开启站点统计'), _t('是否开启站点访问统计功能')
    );
    $form->addInput($enableStatistics);

    // 页脚版权（留空则显示：© 年份 站点标题）
    $copyright = new Typecho\Widget\Helper\Form\Element\Textarea('copyright', NULL, NULL, _t('页脚版权信息'), _t('留空则显示默认：© 年份 + 站点标题。可填自定义版权文字（纯文本）'));
    $form->addInput($copyright);

    // 博主信息配置
    $authorAvatar = new Typecho\Widget\Helper\Form\Element\Text('authorAvatar', NULL, NULL, _t('博主头像URL'), _t('博主头像图片地址，留空使用主题默认头像 img/caiya.xin.jpg'));
    $form->addInput($authorAvatar);

    $authorName = new Typecho\Widget\Helper\Form\Element\Text('authorName', NULL, 'Yacine Tsai', _t('博主昵称'), _t('显示在侧边栏和关于页面的昵称，留空使用站点标题'));
    $form->addInput($authorName);

    $authorTitle = new Typecho\Widget\Helper\Form\Element\Text('authorTitle', NULL, '大数据产品经理', _t('博主职位/标语'), _t('显示在侧边栏简介中的一行描述'));
    $form->addInput($authorTitle);

    $authorBio = new Typecho\Widget\Helper\Form\Element\Textarea('authorBio', NULL, "大数据AI产品经理\n来自河南，现居南京，就职奥派\n爱生活、爱音乐、爱打羽毛球\n爱爬山、爱台球、爱世间万物\n热爱可抵岁月漫长\n乘风破浪奔赴山海\n俯首高调细心做事\n昂首低调宽心做人\n心怀猛虎 细嗅蔷薇", _t('博主个人简介'), _t('每行一条，显示在关于页面'));
    $form->addInput($authorBio);

    $gravatarProfile = new Typecho\Widget\Helper\Form\Element\Text('gravatarProfile', NULL, 'https://gravatar.com/exuberant3c83335dc7', _t('Gravatar 个人主页'), _t('Gravatar rel=me 链接，留空则不输出'));
    $form->addInput($gravatarProfile);

    $resourceLinks = new Typecho\Widget\Helper\Form\Element\Textarea('resourceLinks', NULL, "🤗 Hugging Face|https://huggingface.co/|AI 模型/数据集平台\n📊 Kaggle|https://www.kaggle.com/|数据科学竞赛与数据集\n📄 Papers With Code|https://paperswithcode.com/|AI 论文与代码\n✍️ Towards Data Science|https://towardsdatascience.com/|数据科学博客\n🎓 fast.ai|https://www.fast.ai/|深度学习课程\n🧠 OpenAI|https://openai.com/|ChatGPT / GPT-4\n✦ Anthropic|https://www.anthropic.com/|Claude AI\n🔍 Google AI|https://ai.google/|Gemini / DeepMind\n🏕️ DataCamp|https://www.datacamp.com/|数据科学在线学习\n📈 Analytics Vidhya|https://www.analyticsvidhya.com/|数据分析社区", _t('侧边栏资源链接'), _t('左侧栏链接分享区域，格式：名称|链接|描述，每行一个'));
    $form->addInput($resourceLinks);
    
    // 投稿功能设置（dygita_* 兼容 git_*）
    $tougaoCur = dygita_opt($options, 'dygita_tougao_b', 'git_tougao_b') ?: '0';
    $dygita_tougao_b = new Typecho\Widget\Helper\Form\Element\Radio('dygita_tougao_b',
        array('0' => _t('关闭'), '1' => _t('开启')),
        $tougaoCur, _t('开启投稿功能'), _t('是否允许访客通过投稿页面提交文章（需创建投稿页面并选择投稿模板）')
    );
    $form->addInput($dygita_tougao_b);
    
    $mailtoCur = dygita_opt($options, 'dygita_tougao_mailto', 'git_tougao_mailto');
    $dygita_tougao_mailto = new Typecho\Widget\Helper\Form\Element\Text('dygita_tougao_mailto', NULL, $mailtoCur, _t('投稿通知邮箱'), _t('有新投稿时发送通知到此邮箱，留空则不发送'));
    $form->addInput($dygita_tougao_mailto);
    
    // 下载页设置
    $dlCur = dygita_opt($options, 'dygita_dlpage_dl', 'git_dlpage_dl');
    $dygita_dlpage_dl = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_dlpage_dl', NULL, $dlCur !== null ? $dlCur : _t('请确认您已知悉下载须知后再进行下载。'), _t('下载说明'), _t('在下载页面显示的下载说明文字'));
    $form->addInput($dygita_dlpage_dl);
    
    $mzCur = dygita_opt($options, 'dygita_dlpage_mz', 'git_dlpage_mz');
    $dygita_dlpage_mz = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_dlpage_mz', NULL, $mzCur !== null ? $mzCur : _t('本站资源仅供学习交流，请于下载后24小时内删除。'), _t('免责声明'), _t('在下载页面显示的免责声明文字'));
    $form->addInput($dygita_dlpage_mz);
    
    $ad1Cur = dygita_opt($options, 'dygita_downloadad1', 'git_downloadad1');
    $dygita_downloadad1 = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_downloadad1', NULL, $ad1Cur, _t('下载页顶部广告'), _t('在下载页面文章上方显示的广告代码'));
    $form->addInput($dygita_downloadad1);
    
    $ad2Cur = dygita_opt($options, 'dygita_downloadad2', 'git_downloadad2');
    $dygita_downloadad2 = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_downloadad2', NULL, $ad2Cur, _t('下载页底部广告'), _t('在下载页面底部显示的广告代码'));
    $form->addInput($dygita_downloadad2);
    
    // 第三方 CDN 配置（解决 jsdelivr/unpkg 国内访问问题）
    $cdnProviderCur = dygita_opt($options, 'dygita_cdn_provider', 'git_cdn_provider') ?: 'jsdelivr';
    $dygita_cdn_provider = new Typecho\Widget\Helper\Form\Element\Select(
        'dygita_cdn_provider',
        array(
            'jsdelivr' => _t('jsdelivr + unpkg 回退'),
            'staticfile' => _t('七牛云 Staticfile CDN'),
            'bootcdn' => _t('BootCDN'),
            'cdnjs' => _t('Cloudflare CDNJS'),
            'local' => _t('本地资源（需手动下载到主题目录）')
        ),
        $cdnProviderCur,
        _t('第三方 CDN 提供商'),
        _t('选择 Swiper、PrismJS、Gitalk、Valine 等第三方库的 CDN 来源。如选择本地资源，需手动下载文件到主题 /vendor/ 目录')
    );
    $form->addInput($dygita_cdn_provider);
}


/* 增加: 缩略图获取 */
/**
 * 缩略图解析核心（两个公开函数的共享逻辑）：
 * 给定已提取的 thumb 字段值与文章内容 HTML，返回第一个可用的绝对 URL；
 * 若都没有则返回随机占位图。
 */
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

/* 增加: 获取带表前缀的表名 */
function dygita_get_table($table) {
    $db = Typecho\Db::get();
    return $db->getPrefix() . $table;
}

/**
 * 获取归档页 URL（集中管理，便于伪静态或路由变更）
 * @param \Widget_Options $options
 * @return string
 */
function dygita_get_archives_url($options) {
    return rtrim($options->siteUrl, '/') . '/archives/';
}

/**
 * 获取所有已发布文章，按发布时间倒序，用于归档页面
 * @return array
 */
/**
 * 获取下载页数据（文章信息 + 下载自定义字段）
 * @param int $pid 文章 cid
 * @return array|null 成功返回 ['post'=>..., 'name'=>..., 'size'=>..., 'links'=>..., 'permalink'=>...], 失败返回 null
 */
function dygita_get_download_data($pid) {
    $db = Typecho\Db::get();
    $post = Dygita_Repository::publicPost((int) $pid);
    if (!$post) return null;

    $fields = $db->fetchAll($db->select()->from(dygita_get_table('fields'))->where('cid = ?', intval($pid)));
    $fieldMap = array();
    foreach ($fields as $field) {
        $fieldMap[$field['name']] = $field['str_value'];
    }

    $name  = isset($fieldMap['git_download_name']) ? $fieldMap['git_download_name'] : '';
    $size  = isset($fieldMap['git_download_size']) ? $fieldMap['git_download_size'] : '';
    $links = isset($fieldMap['git_download_link']) ? $fieldMap['git_download_link'] : '';
    if (!$name || !$size || !$links) return null;

    $options = Typecho\Widget::widget('Widget_Options');
    return array(
        'post'      => $post,
        'name'      => $name,
        'size'      => $size,
        'links'     => $links,
        'permalink' => Typecho\Router::url('post', $post, $options->index),
    );
}

/**
 * 获取所有已发布文章，按发布时间倒序，用于归档页面（支持分页）
 * @param int $page 当前页码
 * @param int $pageSize 每页数量
 * @return array
 */
function dygita_get_archive_posts($page = 1, $pageSize = 50) {
    return \Typecho\Db::get()->fetchAll(Dygita_Repository::publicPosts()
        ->select('cid','title','slug','created','type','status','password')
        ->order('created', \Typecho\Db::SORT_DESC)->order('cid', \Typecho\Db::SORT_DESC)
        ->page(max(1, (int) $page), min(100, max(1, (int) $pageSize))));
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
 * XSS 防护辅助函数 - 对输出进行转义
 * @param string $string 要转义的字符串
 * @return string 转义后的字符串
 */


/**
 * 安全输出链接
 * @param string $url 链接地址
 * @return string 安全转义后的链接
 */



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


/**
 * 获取相关文章（三级 fallback：标签+分类 → 仅分类 → 热门文章）
 * 返回 Typecho 标准 Contents Widget，便于模板用 next()/字段属性遍历。
 * @param int $cid 当前文章 ID
 * @param int $limit 最多返回条数
 * @return array ['posts' => \Widget\Contents\Related|null, 'use_hot' => bool]
 */
function dygita_get_related_posts($cid, $limit = 6) {
    $db = \Typecho\Db::get();
    $cid = (int) $cid;
    $limit = max(1, (int) $limit);

    $buildRelatedWidget = function (array $mids) use ($cid, $limit) {
        $mids = array_values(array_unique(array_map('intval', $mids)));
        if (empty($mids)) return null;
        $tagRows = array();
        foreach ($mids as $mid) {
            $tagRows[] = array('mid' => $mid);
        }
        $widget = \Widget\Contents\Related::alloc(array(
            'cid' => $cid,
            'type' => 'post',
            'tags' => $tagRows,
            'limit' => $limit
        ));
        return $widget->have() ? $widget : null;
    };

    // 方法1：共享 meta（标签+分类）
    $mids = $db->fetchAll($db->select('mid')->from('table.relationships')->where('cid = ?', $cid));
    if (!empty($mids)) {
        $widget = $buildRelatedWidget(array_column($mids, 'mid'));
        if ($widget) return array('posts' => $widget, 'use_hot' => false);
    }

    // 方法2：仅分类
    $catRows = $db->fetchAll(
        $db->select('table.metas.mid')
        ->from('table.metas')
        ->join('table.relationships', 'table.metas.mid = table.relationships.mid')
        ->where('table.relationships.cid = ?', $cid)
        ->where('table.metas.type = ?', 'category')
    );
    if (!empty($catRows)) {
        $widget = $buildRelatedWidget(array_column($catRows, 'mid'));
        if ($widget) return array('posts' => $widget, 'use_hot' => false);
    }

    // 方法3：回退到热门文章
    return array('posts' => null, 'use_hot' => true);
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

/* 增加: 浏览量统计 */
function dygita_get_post_view($archive) {
    $cid = (int) $archive->cid;
    $views = Dygita_Repository::counter($cid, 'views');
    if ($archive->is('single') && !$archive->hidden) {
        $seen = array_filter(explode(',', (string) \Typecho\Cookie::get('extend_contents_views')));
        if (!in_array((string) $cid, $seen, true)) {
            $views = Dygita_Repository::increment($cid, 'views');
            $seen[] = (string) $cid;
            \Typecho\Cookie::set('extend_contents_views', implode(',', array_slice($seen, -100)));
        }
    }
    echo $views;
}
function dygita_agree_num($cid) { return Dygita_Repository::counter((int) $cid, 'likes'); }

/* 增加: 处理点赞请求和自定义路由 */
// 此函数已移至文件末尾，与标签云页面处理逻辑合并

/* 增加: 热门文章 */
function dygita_get_hot_posts($limit = 5) {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    $fieldsTable = dygita_get_table('fields');
    $result = $db->fetchAll($db->select($contentsTable . '.*')->from($contentsTable)
        ->where($contentsTable . '.status = ?', 'publish')->where($contentsTable . ".password IS NULL OR " . $contentsTable . ".password = ''")->where($contentsTable . '.created <= ?', time())
        ->where($contentsTable . '.type = ?', 'post')
        ->join($fieldsTable, $contentsTable . '.cid = ' . $fieldsTable . ".cid AND " . $fieldsTable . ".name = 'views'", Typecho\Db::LEFT_JOIN)
        ->order($fieldsTable . '.int_value', Typecho\Db::SORT_DESC)
        ->limit($limit));

    if ($result) {
        foreach ($result as $val) {
            $permalink = Typecho\Router::url('post', $val, Typecho\Widget::widget('Widget_Options')->index);
            $title = htmlspecialchars($val['title'], ENT_QUOTES, 'UTF-8');
            echo '<li><a href="' . $permalink . '" title="' . $title . '">' . $title . '</a></li>';
        }
    }
}

/* 增加: 随机文章 - 优化版本，避免 ORDER BY RAND() 全表扫描和内存溢出 */
function dygita_get_random_posts($limit = 5) {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    
    // 内存优化：限制随机池大小为最近 1000 篇文章，避免大型站点内存溢出
    // 同时避免使用 ORDER BY RAND() 导致的全表扫描和临时表创建
    $poolSize = 1000;
    $allCids = $db->fetchAll($db->select('cid')->from($contentsTable)
        ->where('status = ?', 'publish')->where("password IS NULL OR password = ''")->where('created <= ?', time())
        ->where('type = ?', 'post')
        ->order('created', Typecho\Db::SORT_DESC)
        ->limit($poolSize));
    
    if (empty($allCids)) {
        return;
    }
    
    // 提取 cid 数组
    $cidArray = array_column($allCids, 'cid');
    $totalCount = count($cidArray);
    
    // 如果文章总数少于需要的数量，直接使用全部
    if ($totalCount <= $limit) {
        $selectedCids = $cidArray;
    } else {
        // 在 PHP 层面随机选择指定数量的 cid
        $randomKeys = array_rand($cidArray, $limit);
        $selectedCids = array();
        if (is_array($randomKeys)) {
            foreach ($randomKeys as $key) {
                $selectedCids[] = $cidArray[$key];
            }
        } else {
            // array_rand 在只选择1个时返回单个值而非数组
            $selectedCids[] = $cidArray[$randomKeys];
        }
    }
    
    // 使用 IN 查询获取选中文章的详细信息
    $result = $db->fetchAll($db->select()->from($contentsTable)
        ->where('cid IN ?', $selectedCids)
        ->where('status = ?', 'publish')->where("password IS NULL OR password = ''")->where('created <= ?', time())
        ->where('type = ?', 'post')
        ->order('created', Typecho\Db::SORT_DESC));
    
    if ($result) {
        foreach ($result as $val) {
            $permalink = Typecho\Router::url('post', $val, Typecho\Widget::widget('Widget_Options')->index);
            $title = htmlspecialchars($val['title'], ENT_QUOTES, 'UTF-8');
            echo '<li><a href="' . $permalink . '" title="' . $title . '">' . $title . '</a></li>';
        }
    }
}

/* 增加: 站点统计 */
function dygita_get_stat() {
    $stat = \Typecho\Widget::widget('Widget\Stat');
    return array(
        'posts'      => $stat->publishedPostsNum,
        'comments'   => $stat->publishedCommentsNum,
        'categories' => $stat->categoriesNum,
        'tags'       => $stat->tagsNum,
    );
}

/* 增加: 支持自定义字段 */
function themeFields($layout) {
    $thumb = new \Typecho\Widget\Helper\Form\Element\Text(
        'thumb',
        null,
        null,
        _t('文章缩略图'),
        _t('在这里填入一个图片URL地址, 作为文章的缩略图')
    );
    $layout->addItem($thumb);

    $excerpt = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'excerpt',
        null,
        null,
        _t('文章摘要'),
        _t('在这里填入文章摘要, 会显示在文章列表页')
    );
    $layout->addItem($excerpt);
}

/* 增加: 友情链接 */

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

/**
 * 标签云页面说明：
 * 要使用标签云页面，请在 Typecho 后台：
 * 1. 创建一个新页面（管理 -> 新增页面）
 * 2. 在页面编辑界面右侧，选择"自定义模板"为"标签云"
 * 3. 发布页面
 * 
 * 页面模板文件：page-tag-cloud.php
 */

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
function themeInit($archive) {
    dygita_bootstrap_runtime();

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

/**
 * 全站搜索索引生成与管理
 */
/**
 * 注册搜索索引 Action
 */
function dygita_register_search_action() {
    $actionTable = \Typecho\Widget::widget('Widget_Options')->actionTable;
    if (empty($actionTable['dygita-search-index'])) {
        \Utils\Helper::addAction('dygita-search-index', 'Dygita_Action_Search_Index');
    }
}

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

/**
 * 主题停用时清理事件和路由
 */
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
