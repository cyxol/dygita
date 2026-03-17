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

// 自动注册主题自定义路由（写入数据库，仅在路由缺失时执行一次）
(function () {
    $rt = \Widget\Options::alloc()->routingTable;
    $exists = isset($rt['tags_cloud']) || (isset($rt[0]) && isset($rt[0]['tags_cloud']));
    if (!$exists) {
        \Utils\Helper::addRoute('tags_cloud', '/tags/', '\Widget\Archive', 'render');
    }
})();

/**
 * 主题激活时初始化默认设置
 */
function dygita_theme_activate() {
    $options = Typecho\Widget::widget('Widget_Options');
    $db = Typecho\Db::get();
    $optionsTable = $db->getPrefix() . 'options';

    // 设置默认主题色（新名优先，兼容旧名）
    $skinVal = dygita_opt($options, 'dygita_skin_b', 'git_skin_b');
    if ($skinVal === null || $skinVal === '') {
        $db->query($db->insert($optionsTable)->rows(array(
            'name' => 'dygita_skin_b',
            'value' => 'git_red_b',
            'user' => 0
        )));
    }

    // 设置默认幻灯片配置
    if (!isset($options->swiperEnabled) || empty($options->swiperEnabled)) {
        $db->query($db->insert($optionsTable)->rows(array(
            'name' => 'swiperEnabled',
            'value' => '1',
            'user' => 0
        )));
    }
}

/**
 * 主题禁用时清理临时数据
 */
function dygita_theme_deactivate() {
    // 清理主题相关的 Cookie
    Typecho\Cookie::delete('extend_contents_views');
    Typecho\Cookie::delete('extend_contents_likes');
}

/**
 * 主题设置重置函数 - 可以在主题设置页面调用
 */
function dygita_reset_options() {
    $optionsToReset = array(
        'dygita_skin_b' => 'git_red_b',
        'git_skin_b' => 'git_red_b',
        'colorSchema' => NULL,
        'swiperEnabled' => '1',
        'swiperAutoplay' => '1',
        'swiperSpeed' => '1000',
        'swiperDelay' => '3000',
        'navLinksEnabled' => '1',
        'enableStatistics' => '0'
    );

    $db = Typecho\Db::get();
    $optionsTable = $db->getPrefix() . 'options';

    foreach ($optionsToReset as $name => $value) {
        $existing = $db->fetchRow($db->select()->from($optionsTable)
            ->where('name = ?', $name));

        if (!$existing) {
            $db->query($db->insert($optionsTable)->rows(array(
                'name' => $name,
                'value' => is_null($value) ? '' : $value,
                'user' => 0
            )));
        }
    }
}

/**
 * 文章目录功能
 */
class ArticleCatalog {
    /**
     * 索引ID
     */
    public $id = 1;

    /**
     * 目录树
     */
    public $tree = array();

    /**
     * @var string 描点
     */
    public $anchor = '<span id="article_menu_index_{menu_id}" class="title-anchor"></span>';

    /**
     * @var string 目录
     */
    public $catalog_item = '<a data-scroll href="#article_menu_index_{menu_id}" title="{title}">{title}</a>';

    /**
     * 解析
     *
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public function parseCallback( $match ) {
        $parent = &$this->tree;

        $h = $match[0];
        $n = $match[1];
        $menu = array(
            'num' => $n,
            'title' => trim( strip_tags( $h ) ),
            'id' => $this->id,
            'sub' => array()
        );
        $current = array();
        if( $parent ) {
            $current = &$parent[ count( $parent ) - 1 ];
        }
        // 根
        if( ! $parent || ( isset( $current['num'] ) && $n <= $current['num'] ) ) {
            $parent[] = $menu;
        } else {
            while( is_array( $current[ 'sub' ] ) ) {
                // 父子关系
                if( $current['num'] == $n - 1 ) {
                    $current[ 'sub' ][] = $menu;
                    break;
                }
                // 后代关系，并存在子菜单
                elseif( $current['num'] < $n && $current[ 'sub' ] ) {
                    $current = &$current['sub'][ count( $current['sub'] ) - 1 ];
                }
                // 后代关系，不存在子菜单
                else {
                    for( $i = 0; $i < $n - $current['num']; $i++ ) {
                        $current['sub'][] = array(
                            'num' => $current['num'] + 1,
                            'sub' => array()
                        );
                        $current = &$current['sub'][0];
                    }
                    $current['sub'][] = $menu;
                    break;
                }
            }
        }
        $this->id++;
        return str_replace('{menu_id}', $menu['id'], $this->anchor) . $h;
    }

    public function renderHtml($html, $anchor='') {
        if ($anchor) {
            $this->anchor = $anchor;
        }
        $html = preg_replace_callback( '/<h([1-6])[^>]*>.*?<\/h\1>/s', array( $this, 'parseCallback' ), $html );
        return $html;
    }

    public function renderCatalogHtml($li = '') {
        if ($li) {
            $this->catalog_item = $li;
        }
        return $this->buildCatalogHtml($this->tree);
    }

    /**
     * 构建目录树，生成索引
     *
     * @access public
     * @return string
     */
    public function buildCatalogHtml( $tree, $include = true ) {
        $menuHtml = '';
        foreach( $tree as $menu ) {
            if( ! isset( $menu['id'] ) && $menu['sub'] ) {
                $menuHtml .= $this->buildCatalogHtml( $menu['sub'], false );
            } else {
                $title = htmlspecialchars($menu['title'], ENT_QUOTES);
                $li = "<li>";
                $li .= str_replace(array('{menu_id}', '{title}'), array($menu['id'], $title), $this->catalog_item);
                if ($menu['sub']) {
                    $li .= $this->buildCatalogHtml( $menu['sub'] );
                }
                $li .= "</li>";
                $menuHtml .= $li;
            }
        }
        if( $include ) {
            $menuHtml = '<ul>' . $menuHtml . '</ul>';
        }
        return $menuHtml;
    }

    // 单例
    public static function instance() {
        static $instance = null;
        if ($instance === null) {
            $instance = new static();
        }
        return $instance;
    }
}

function themeConfig($form)
{
    // 注册自定义路由（访问设置页时即写入数据库）
    $rt = \Utils\Helper::options()->routingTable;
    if (!isset($rt['tags_cloud']) && !(isset($rt[0]) && isset($rt[0]['tags_cloud']))) {
        \Utils\Helper::addRoute('tags_cloud', '/tags/', '\Widget\Archive', 'render');
    }

    // 获取全局配置（注意：在函数上下文中不能使用 $this->options）
    $options = Typecho\Widget::widget('Widget_Options');
    // 站点Logo
    $logoUrl = new Typecho\Widget\Helper\Form\Element\Text('logoUrl', NULL, NULL, _t('站点LOGO地址'), _t('在这里填入一个图片URL地址, 以在网站标题前显示一个LOGO'));
    $form->addInput($logoUrl);

    // 侧边栏
    $sidebarBlock = new Typecho\Widget\Helper\Form\Element\Checkbox('sidebarBlock',
        array('ShowRecentPosts' => _t('显示最新文章'),
        'ShowRecentComments' => _t('显示最近回复'),
        'ShowCategory' => _t('显示分类'),
        'ShowArchive' => _t('显示归档'),
        'ShowLinks' => _t('显示友情链接'),
        'ShowOther' => _t('显示其它杂项')),
        array('ShowRecentPosts', 'ShowRecentComments', 'ShowCategory', 'ShowArchive', 'ShowLinks', 'ShowOther'), _t('侧边栏显示'));

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

    // 幻灯片 dygita_sticky_b（兼容 git_sticky_b）
    $stickyCur = dygita_opt($options, 'dygita_sticky_b', 'git_sticky_b') ?: '0';
    $dygita_sticky_b = new Typecho\Widget\Helper\Form\Element\Radio('dygita_sticky_b',
        array('0' => _t('关闭'), '1' => _t('开启')),
        $stickyCur, _t('开启置顶推荐'), _t('是否开启首页置顶推荐(需要配合幻灯片插件或手动修改代码)')
        );
    $form->addInput($dygita_sticky_b);
    
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
    
    $navLinks = new Typecho\Widget\Helper\Form\Element\Textarea('navLinks', NULL, '{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"},{"name":"标签云","url":"page-tag-cloud.html","target":"_self"}]}', _t('导航链接配置'), _t('JSON格式，包含导航链接名称、URL和目标。特殊URL：@author 自动检测作者页面。例如：{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"}]}'));
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
    
    // 联系方式设置
    $contactEmail = new Typecho\Widget\Helper\Form\Element\Text('contactEmail', NULL, 'admin@example.com', _t('联系邮箱'), _t('设置侧边栏显示的联系邮箱'));
    $form->addInput($contactEmail);
    
    $contactQQ = new Typecho\Widget\Helper\Form\Element\Text('contactQQ', NULL, '12345678', _t('联系QQ'), _t('设置侧边栏显示的联系QQ号'));
    $form->addInput($contactQQ);
    
    // 广告位设置
    $adImageUrl = new Typecho\Widget\Helper\Form\Element\Text('adImageUrl', NULL, NULL, _t('广告图片URL'), _t('设置侧边栏赞助商广告位的图片URL，留空则使用默认图片'));
    $form->addInput($adImageUrl);
    
    $adLinkUrl = new Typecho\Widget\Helper\Form\Element\Text('adLinkUrl', NULL, NULL, _t('广告链接URL'), _t('设置侧边栏赞助商广告位的链接URL，留空则不添加链接'));
    $form->addInput($adLinkUrl);
    
    // 页脚版权（留空则显示：© 年份 站点标题）
    $copyright = new Typecho\Widget\Helper\Form\Element\Textarea('copyright', NULL, NULL, _t('页脚版权信息'), _t('留空则显示默认：© 年份 + 站点标题。可填自定义版权文字（纯文本）'));
    $form->addInput($copyright);
    
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
}

function git_get_option($key, $default = null)
{
    $options = Typecho\Widget::widget('Widget_Options');
    // 尝试直接获取
    $value = $options->$key;
    if ($value === null) {
        // 尝试从personal获取 (reserved for plugin compatibility if needed)
        // return $default;

        // 映射一些 WP 的 option 到 Typecho 的 option
        switch ($key) {
            case 'git_skin_b':
            case 'dygita_skin_b':
                return dygita_opt($options, 'dygita_skin_b', 'git_skin_b') ?: 'git_red_b';
            case 'git_pichead_b':
                return false; // 暂时关闭图片头部
            default:
                return $default;
        }
    }
    return $value;
}

/* 增加: 缩略图获取 */
function getThumbnail($widget)
{
    $url = '';

    // 1. 自定义字段 'thumb'
    if ($widget->fields->thumb) {
        $url = $widget->fields->thumb;
    }
    // 2. 附件中的第一张图片
    elseif (($attach = $widget->attachments(1)->attachment) && $attach && $attach->isImage) {
        $url = $attach->url;
    }
    // 3. 文章内容中的第一张图片
    elseif (preg_match('/<img.+?src=["\']([^"\']+)["\']/', $widget->content, $match)) {
        $url = $match[1];
    }

    if ($url && preg_match('/^https?:\/\//i', $url)) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }

    // 4. 随机占位图
    return getRandomPlaceholderImageUrl($widget->widget('Widget_Options'));
}

/**
 * 选项读取兼容：优先读 dygita_*，无则回退到 git_*（便于从 git_* 迁移到 dygita_*）
 * @param \Widget_Options $options
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
    ob_start();
    $options->siteUrl();
    $base = rtrim(ob_get_clean(), '/');
    return $base . '/archives/';
}

/**
 * 获取随机占位图 URL（统一路径，便于修改）
 * @param \Widget_Options $options
 * @return string
 */
function getRandomPlaceholderImageUrl($options) {
    $random = mt_rand(1, 12);
    ob_start();
    $options->themeUrl();
    $base = rtrim(ob_get_clean(), '/');
    return $base . '/img/pic/' . $random . '.jpg';
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
 * 输出主题色相关 CSS 规则（供 header 内联样式使用）
 * @param string $skinKey 如 $this->options->git_skin_b
 * @return string CSS 规则文本
 */
function dygita_get_theme_skin_css($skinKey) {
    $c = dygita_get_skin_colors($skinKey);
    $skin_nom = $c['nom'];
    $skin_hover = $c['hover'];
    return ".navbar .nav li:hover a, .navbar .nav li.current-menu-item a, .navbar .nav li.current-menu-parent a, .navbar .nav li.current_page_item a, .navbar .nav li.current-post-ancestor a,.toggle-search ,#submit ,.pagination ul>.active>a,.pagination ul>.active>span,.bdcs-container .bdcs-search-form-submit,.metacat a{background: {$skin_nom};}.footer,.title h2,.card-item .cardpricebtn{color: {$skin_nom};}.bdcs-container .bdcs-search-form-submit ,.bdcs-container .bdcs-search {border-color: {$skin_nom};}.pagination ul>li>a:hover,.navbar .nav li a:focus, .navbar .nav li a:hover,.toggle-search:hover,#submit:hover,.cardpricebtn .cardbuy {background-color: {$skin_hover};}.tooltip-inner{background-color:{$skin_hover};}.tooltip.top .tooltip-arrow{border-top-color:{$skin_hover};}.tooltip.right .tooltip-arrow{border-right-color:{$skin_hover};}.tooltip.left .tooltip-arrow{border-left-color:{$skin_hover};}.tooltip.bottom .tooltip-arrow{border-bottom-color:{$skin_hover};}";
}

/**
 * 获取保存的主题偏好（用于 data-theme / 顶栏色等）
 * @return array ['theme' => string, 'headerColor' => string]
 */
function dygita_get_saved_theme_prefs() {
    $theme = '';
    $headerColor = '';
    try {
        $db = Typecho\Db::get();
        $prefix = $db->getPrefix();
        $row = $db->fetchRow($db->select()->from($prefix . 'options')->where('name = ?', 'dygita_theme'));
        if ($row) {
            $theme = $row['value'];
        }
        $row = $db->fetchRow($db->select()->from($prefix . 'options')->where('name = ?', 'dygita_headerColor'));
        if ($row) {
            $headerColor = $row['value'];
        }
    } catch (Exception $e) {
    }
    return array('theme' => $theme, 'headerColor' => $headerColor);
}

/**
 * 获取前端 CONFIG 对象（供 header 内联脚本输出）
 * @param \Widget_Options $options
 * @return array
 */
function dygita_get_config_array($options) {
    ob_start();
    $options->siteUrl();
    $hostname = ob_get_clean();
    return array(
        'hostname' => $hostname,
        'root' => '/',
        'exturl' => false,
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
function dygita_escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * 安全输出链接
 * @param string $url 链接地址
 * @return string 安全转义后的链接
 */
function dygita_escape_url($url) {
    $url = trim($url ?? '');
    if (empty($url) || $url === '#') {
        return $url;
    }
    if (preg_match('/^https?:\/\//i', $url) || $url[0] === '/') {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    return '';
}

/* 增加: 相关文章缩略图获取（用于数组形式的文章数据） */
function getRelatedPostThumbnail($post)
{
    $db = Typecho\Db::get();
    $options = Typecho\Widget::widget('Widget_Options');

    // 1. 自定义字段 'thumb'
    $fieldsTable = dygita_get_table('fields');
    $thumb = $db->fetchRow($db->select('str_value')->from($fieldsTable)
        ->where('cid = ?', $post['cid'])
        ->where('name = ?', 'thumb'));
    if ($thumb && !empty($thumb['str_value'])) {
        return $thumb['str_value'];
    }

    // 2. 文章内容中的第一张图片
    if (isset($post['text']) && preg_match('/<img.+?src=["\']([^"\']+)["\']/', $post['text'], $match)) {
        return $match[1];
    }

    // 3. 随机占位图
    return getRandomPlaceholderImageUrl($options);
}

/* 增加: 浏览量统计 */
function getPostView($archive)
{
    $cid    = $archive->cid;
    $db     = Typecho\Db::get();
    $fieldsTable = dygita_get_table('fields');

    $row = $db->fetchRow($db->select('str_value')->from($fieldsTable)
        ->where('cid = ?', $cid)
        ->where('name = ?', 'views'));

    if (!$row) {
        $db->query($db->insert($fieldsTable)->rows(array(
            'cid' => $cid,
            'name' => 'views',
            'type' => 'str',
            'str_value' => 0,
            'int_value' => 0,
            'float_value' => 0
        )));
        $views = 0;
    } else {
        $views = intval($row['str_value']);
    }

    if ($archive->is('single')) {
         $viewed = Typecho\Cookie::get('extend_contents_views');
         $viewed = $viewed ? explode(',', $viewed) : array();

         if (!in_array($cid, $viewed)) {
             $views++;
             $db->query($db->update($fieldsTable)
                ->rows(array('str_value' => $views, 'int_value' => $views))
                ->where('cid = ?', $cid)
                ->where('name = ?', 'views'));

             $viewed[] = $cid;
             if (count($viewed) > 100) {
                 $viewed = array_slice($viewed, -100);
             }
             Typecho\Cookie::set('extend_contents_views', implode(',', $viewed));
         }
    }
    echo $views;
}

/* 增加: 点赞数量获取 */
function agreeNum($cid) {
    $db = Typecho\Db::get();
    $fieldsTable = dygita_get_table('fields');
    $row = $db->fetchRow($db->select('str_value')->from($fieldsTable)
        ->where('cid = ?', $cid)
        ->where('name = ?', 'likes'));
    if (!$row) {
        return 0;
    }
    return intval($row['str_value']);
}

/* 增加: 处理点赞请求和自定义路由 */
// 此函数已移至文件末尾，与标签云页面处理逻辑合并

/* 增加: 热门文章 */
function getHotPosts($limit = 5) {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    $fieldsTable = dygita_get_table('fields');
    $result = $db->fetchAll($db->select()->from($contentsTable)
        ->where($contentsTable . '.status = ?', 'publish')
        ->where($contentsTable . '.type = ?', 'post')
        ->join($fieldsTable, $contentsTable . '.cid = ' . $fieldsTable . '.cid', Typecho\Db::LEFT_JOIN)
        ->where($fieldsTable . '.name = ?', 'views')
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

/* 增加: 随机文章 */
function getRandomPosts($limit = 5) {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    $adapterName = $db->getAdapterName();

    // 兼容 SQLite
    if ($adapterName == 'sqlite' || $adapterName == 'Pdo_SQLite' || $adapterName == 'SQLite') {
        $order_by = 'RANDOM()';
    } elseif ($adapterName == 'pgsql' || $adapterName == 'Pdo_Pgsql' || $adapterName == 'Pgsql') {
        $order_by = 'RANDOM()';
    } else {
        $order_by = 'RAND()';
    }

    $result = $db->fetchAll($db->select()->from($contentsTable)
        ->where('status = ?', 'publish')
        ->where('type = ?', 'post')
        ->order($order_by)
        ->limit($limit));

    if ($result) {
        foreach ($result as $val) {
            $permalink = Typecho\Router::url('post', $val, Typecho\Widget::widget('Widget_Options')->index);
            $title = htmlspecialchars($val['title'], ENT_QUOTES, 'UTF-8');
            echo '<li><a href="' . $permalink . '" title="' . $title . '">' . $title . '</a></li>';
        }
    }
}

/* 增加: 站点统计 */
function getStat() {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    $commentsTable = dygita_get_table('comments');
    $metasTable = dygita_get_table('metas');

    // 文章总数
    $count_posts = $db->fetchObject($db->select(array('COUNT(cid)' => 'num'))
        ->from($contentsTable)
        ->where('type = ?', 'post')
        ->where('status = ?', 'publish'))->num;
    // 评论总数
    $count_comments = $db->fetchObject($db->select(array('COUNT(coid)' => 'num'))
        ->from($commentsTable)
        ->where('status = ?', 'approved'))->num;
    // 分类总数
    $count_categories = $db->fetchObject($db->select(array('COUNT(mid)' => 'num'))
        ->from($metasTable)
        ->where('type = ?', 'category'))->num;
    // 标签总数
    $count_tags = $db->fetchObject($db->select(array('COUNT(mid)' => 'num'))
        ->from($metasTable)
        ->where('type = ?', 'tag'))->num;

    return array(
        'posts' => $count_posts,
        'comments' => $count_comments,
        'categories' => $count_categories,
        'tags' => $count_tags
    );
}

/* 增加: 统一处理文章元数据 */
function postMeta(
    \Widget\Archive $archive,
    string $metaType = 'archive'
) {
    $titleTag = $metaType == 'archive' ? 'h2' : 'h1';
?>
    <<?php echo $titleTag ?> class="post-title" itemprop="name headline">
        <a itemprop="url"
           href="<?php $archive->permalink() ?>"><?php $archive->title() ?></a>
    </<?php echo $titleTag ?>>
    <?php if ($metaType != 'page'): ?>
        <ul class="post-meta">
            <li itemprop="author" itemscope itemtype="http://schema.org/Person">
                <?php _e('作者'); ?>: <a itemprop="name"
                                       href="<?php $archive->author->permalink(); ?>">
                                       <?php $archive->author(); ?></a>
            </li>
            <li><?php _e('时间'); ?>:
                <time datetime="<?php $archive->date('c'); ?>" itemprop="datePublished">
                <?php $archive->date(); ?></time>
            </li>
            <li><?php dygita_e('分类'); ?>: <?php $archive->category(','); ?></li>
            <li><?php _e('浏览'); ?>(<?php getPostView($archive); ?>)</li>
            <?php if ($metaType == 'archive'): ?>
                <li itemprop="interactionCount">
                    <a itemprop="discussionUrl"
                       href="<?php $archive->permalink() ?>#comments">
                       <?php $archive->commentsNum(_t('暂无评论'), _t('1 条评论'), _t('%d 条评论')); ?></a>
                </li>
            <?php endif; ?>
        </ul>
    <?php endif; ?>
<?php
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
function getLinks() {
    $options = Typecho\Widget::widget('Widget_Options');
    $links = $options->links;
    
    if ($links) {
        // 修复：使用双引号使 \n 被正确解释为换行符，同时支持 \r\n
        $linkArray = preg_split('/\r?\n/', $links);
        foreach ($linkArray as $link) {
            $linkInfo = explode('|', trim($link));
            if (count($linkInfo) >= 2) {
                $name = htmlspecialchars(trim($linkInfo[0]), ENT_QUOTES, 'UTF-8');
                $url = htmlspecialchars(trim($linkInfo[1]), ENT_QUOTES, 'UTF-8');
                $desc = isset($linkInfo[2]) ? htmlspecialchars(trim($linkInfo[2]), ENT_QUOTES, 'UTF-8') : '';
                echo '<li><a href="' . $url . '" title="' . $desc . '" target="_blank" rel="noopener">' . $name . '</a></li>';
            }
        }
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
 * 主题内翻译：使用 themes/dygita/views/languages/*.php，一键中英文切换
 * @param string $key 原文（中文或英文均可，与语言包键一致即可）
 * @return string
 */
function dygita_t($key) {
    static $map = null;
    if ($map === null) {
        $lang = dygita_current_lang();
        $options = \Typecho\Widget::widget('Widget_Options');
        $path = $options->themeFile($options->theme, 'views/languages/' . $lang . '.php');
        $map = file_exists($path) ? (array) include $path : array();
    }
    return isset($map[$key]) ? $map[$key] : $key;
}

/** 输出主题翻译，便于模板中 echo 使用 */
function dygita_e($key) {
    echo dygita_t($key);
}

// 主题初始化函数
function themeInit($archive) {
    // 自定义路由模板：标签云页面
    if ($archive->parameter->type === 'tags_cloud') {
        $archive->setThemeFile('views/components/tags.php');
        return;
    }

    // 一键切换语言：?dygita_lang=zh_CN 或 ?dygita_lang=en_US → 写 cookie 并重定向到当前页（去掉参数）
    $langParam = $archive->request->get('dygita_lang');
    if ($langParam === 'zh_CN' || $langParam === 'en_US') {
        Typecho\Cookie::set(DYGITA_LANG_COOKIE, $langParam, 0, $archive->options->rootUrl);
        $url = $archive->request->getRequestUrl();
        $url = preg_replace('#[?&]dygita_lang=(?:zh_CN|en_US)(?=&|$)#', '', $url);
        if (preg_match('#^([^?]*)\&(.+)$#', $url, $m)) {
            $url = $m[1] . '?' . $m[2];
        }
        $url = rtrim($url, '?');
        $archive->response->redirect($url);
        exit;
    }

    // 处理点赞请求（仅依赖 Cookie 防刷，不要求 CSRF token 以兼容无表单页面）
    if ($archive->request->isPost() && $archive->request->get('action') == 'like') {
        $cid = $archive->request->get('cid');
        $db = Typecho\Db::get();
        $fieldsTable = dygita_get_table('fields');

        $likes = Typecho\Cookie::get('extend_contents_likes');
        $likes = $likes ? explode(',', $likes) : array();

        if (!in_array($cid, $likes)) {
            $row = $db->fetchRow($db->select('str_value')->from($fieldsTable)
                ->where('cid = ?', $cid)
                ->where('name = ?', 'likes'));

            if (!$row) {
                 $db->query($db->insert($fieldsTable)->rows(array(
                    'cid' => $cid,
                    'name' => 'likes',
                    'type' => 'str',
                    'str_value' => 1,
                    'int_value' => 1,
                    'float_value' => 0
                )));
                $count = 1;
            } else {
                $count = intval($row['str_value']) + 1;
                $db->query($db->update($fieldsTable)
                    ->rows(array('str_value' => $count, 'int_value' => $count))
                    ->where('cid = ?', $cid)
                    ->where('name = ?', 'likes'));
            }

            $likes[] = $cid;
            if (count($likes) > 100) {
                $likes = array_slice($likes, -100);
            }
            Typecho\Cookie::set('extend_contents_likes', implode(',', $likes));
            echo $count;
        } else {
            echo 'already_liked';
        }
        exit;
    }

    // 处理主题偏好保存请求（仅限管理员，低风险操作无需 CSRF）
    if ($archive->request->isAjax() && $archive->request->isPost() && $archive->request->get('action') == 'savePreference') {
        $type = $archive->request->get('type');
        $value = $archive->request->get('value');

        if (!in_array($type, ['theme', 'headerColor'])) {
            echo 'invalid_type';
            exit;
        }

        $user = Typecho\Widget::widget('Widget_User');
        if (!$user->hasLogin() || !$user->pass('administrator', true)) {
            echo 'client_only';
            exit;
        }

        $db = Typecho\Db::get();
        $optionsTable = dygita_get_table('options');

        $existing = $db->fetchRow($db->select()->from($optionsTable)
            ->where('name = ?', 'dygita_' . $type));

        if ($existing) {
            $db->query($db->update($optionsTable)
                ->rows(array('value' => $value))
                ->where('name = ?', 'dygita_' . $type));
        } else {
            $db->query($db->insert($optionsTable)->rows(array(
                'name' => 'dygita_' . $type,
                'value' => $value,
                'user' => 0
            )));
        }

        echo 'success';
        exit;
    }
};
