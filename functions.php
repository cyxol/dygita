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

/**
 * 注册主题自定义路由（统一入口，避免重复定义）
 */
function dygita_register_routes() {
    $rt = \Widget\Options::alloc()->routingTable;
    \Utils\Helper::addRoute('tags_cloud', '/tags/', '\Widget\Archive', 'render');
    \Utils\Helper::addRoute('tags_cloud_tag', '/tags/', '\Widget\Archive', 'render');
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
}

/**
 * 运行期兜底注册（仅首次执行），兼容老站点未重新激活主题的场景
 */
function dygita_bootstrap_runtime() {
    static $bootstrapped = false;
    if ($bootstrapped) return;
    dygita_register_routes();
    dygita_register_actions();

    // 注册文章目录缓存钩子：文章发布/更新时自动生成 TOC 缓存
    $catalogPlugin = \Typecho\Plugin::factory('Widget_Abstract_Contents');
    $catalogPlugin->finishPublish = [Dygita_Catalog_Cache::class, 'onFinishPublish'];

    $bootstrapped = true;
}

/**
 * 点赞 Action — 通过 /action/dygita-like 路由处理 AJAX 点赞请求
 */
class Dygita_Action_Like extends \Typecho\Widget implements \Widget\ActionInterface
{
    public function execute() {}

    public function action()
    {
        if (!$this->request->isPost()) {
            $this->response->setStatus(405);
            exit;
        }

        $cid = intval($this->request->get('cid'));
        if (!$cid) {
            $this->response->setStatus(400);
            echo 'invalid';
            exit;
        }

        $db = \Typecho\Db::get();
        $fieldsTable = dygita_get_table('fields');

        $likes = \Typecho\Cookie::get('extend_contents_likes');
        $likes = $likes ? explode(',', $likes) : array();
        $cidStr = (string)$cid;

        if (!in_array($cidStr, $likes)) {
            $row = $db->fetchRow($db->select('str_value')->from($fieldsTable)
                ->where('cid = ?', $cid)
                ->where('name = ?', 'likes'));

            if (!$row) {
                $db->query($db->insert($fieldsTable)->rows(array(
                    'cid'         => $cid,
                    'name'        => 'likes',
                    'type'        => 'str',
                    'str_value'   => 1,
                    'int_value'   => 1,
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

            $likes[] = $cidStr;
            if (count($likes) > 100) {
                $likes = array_slice($likes, -100);
            }
            \Typecho\Cookie::set('extend_contents_likes', implode(',', $likes));
            echo $count;
        } else {
            echo 'already_liked';
        }
        exit;
    }
}

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
class Dygita_ArticleCatalog {
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

    private function appendMenuItem($n, $title) {
        $parent = &$this->tree;
        $menu = array(
            'num' => (int) $n,
            'title' => trim((string) $title),
            'id' => $this->id,
            'sub' => array()
        );
        $current = array();
        if (!empty($parent)) {
            $current = &$parent[count($parent) - 1];
        }
        if (!$parent || (isset($current['num']) && $n <= $current['num'])) {
            $parent[] = $menu;
        } else {
            while (is_array($current['sub'])) {
                if ($current['num'] == $n - 1) {
                    $current['sub'][] = $menu;
                    break;
                } elseif ($current['num'] < $n && $current['sub']) {
                    $current = &$current['sub'][count($current['sub']) - 1];
                } else {
                    for ($i = 0; $i < $n - $current['num']; $i++) {
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
        return $menu['id'];
    }

    /**
     * 解析
     *
     * @access public
     * @param array $matches 解析值
     * @return string
     */
    public function parseCallback( $match ) {
        $h = $match[0];
        $n = $match[1];
        $menuId = $this->appendMenuItem((int) $n, trim(strip_tags($h)));
        return str_replace('{menu_id}', $menuId, $this->anchor) . $h;
    }

    public function renderHtml($html, $anchor='') {
        if ($anchor) {
            $this->anchor = $anchor;
        }
        $this->id = 1;
        $this->tree = array();

        if (class_exists('DOMDocument') && class_exists('DOMXPath')) {
            $prevUseErrors = libxml_use_internal_errors(true);
            $dom = new DOMDocument();
            $wrapped = '<div id="dygita-catalog-root">' . $html . '</div>';
            $flags = 0;
            if (defined('LIBXML_HTML_NOIMPLIED')) $flags |= LIBXML_HTML_NOIMPLIED;
            if (defined('LIBXML_HTML_NODEFDTD')) $flags |= LIBXML_HTML_NODEFDTD;
            $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, $flags);
            if ($loaded) {
                $xpath = new DOMXPath($dom);
                $root = $xpath->query('//*[@id="dygita-catalog-root"]')->item(0);
                if ($root) {
                    $headings = $xpath->query('.//h1|.//h2|.//h3|.//h4|.//h5|.//h6', $root);
                    if ($headings && $headings->length > 0) {
                        foreach ($headings as $heading) {
                            $tagName = strtolower($heading->nodeName);
                            $level = (int) substr($tagName, 1);
                            $title = trim((string) $heading->textContent);
                            $menuId = $this->appendMenuItem($level, $title);

                            $fragment = $dom->createDocumentFragment();
                            $fragment->appendXML(str_replace('{menu_id}', $menuId, $this->anchor));
                            $heading->parentNode->insertBefore($fragment, $heading);
                        }
                        $output = '';
                        foreach ($root->childNodes as $childNode) {
                            $output .= $dom->saveHTML($childNode);
                        }
                        libxml_clear_errors();
                        libxml_use_internal_errors($prevUseErrors);
                        return $output;
                    }
                }
            }
            libxml_clear_errors();
            libxml_use_internal_errors($prevUseErrors);
        }

        $html = preg_replace_callback('/<h([1-6])[^>]*>.*?<\/h\1>/s', array($this, 'parseCallback'), $html);
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

/**
 * 文章目录缓存
 *
 * 在文章保存/发布时预生成目录和带锚点的HTML，存入 custom fields。
 * 前端渲染时直接读取缓存，避免每次请求都进行 DOM 解析或正则替换。
 *
 * 缓存策略：
 * - 写时：通过 finishPublish 钩子，在文章发布/更新时生成缓存
 * - 读时：优先从 custom fields 读取，无缓存时回退到实时解析（兼容性保障）
 * - 请求级静态缓存：同一请求内不会重复查询数据库
 */
class Dygita_Catalog_Cache {
    /** custom fields 字段名：带锚点的文章 HTML */
    const FIELD_PARSED = 'dygita_parsed_content';
    /** custom fields 字段名：目录 HTML */
    const FIELD_CATALOG = 'dygita_catalog_html';

    /**
     * 请求级缓存（同一 PHP 请求内复用，避免重复查库）
     * @var array<int, array{parsed: string, catalog: string}|null>
     */
    private static $requestCache = [];

    /**
     * 文章发布/更新钩子回调
     *
     * 在 Markdown→HTML 转换后，调用 ArticleCatalog 生成带锚点的 HTML
     * 和目录 HTML，一并写入 custom fields。
     *
     * @param array $contents 已保存的文章数据（text 字段可能含 <!--markdown--> 前缀）
     * @param \Widget\Contents\Post\Edit|\Widget\Contents\Page\Edit $widget
     */
    public static function onFinishPublish($contents, $widget)
    {
        try {
            $cid = isset($widget->cid) ? (int) $widget->cid : 0;
            $text = isset($contents['text']) ? (string) $contents['text'] : '';

            if ($cid <= 0 || $text === '') {
                return;
            }

            // 去掉 <!--markdown--> 前缀后进行 Markdown→HTML 转换
            $isMarkdown = (strpos($text, '<!--markdown-->') === 0);
            $rawText = $isMarkdown ? substr($text, 15) : $text;

            $html = $isMarkdown
                ? \Utils\Markdown::convert($rawText)
                : $rawText;

            // 生成目录（需要全新实例，避免与前端单例冲突）
            $catalog = new Dygita_ArticleCatalog();
            $parsedHtml = $catalog->renderHtml($html);
            $catalogHtml = $catalog->renderCatalogHtml();

            self::saveCache($cid, $parsedHtml, $catalogHtml);
        } catch (\Throwable $e) {
            // 静默失败，绝不影响文章保存流程
        }
    }

    /**
     * 获取缓存的目录数据
     *
     * @param int $cid 文章 ID
     * @return array{parsed: string, catalog: string}|null
     */
    public static function getCache($cid)
    {
        $cid = (int) $cid;
        if ($cid <= 0) {
            return null;
        }

        // 请求级缓存
        if (array_key_exists($cid, self::$requestCache)) {
            return self::$requestCache[$cid];
        }

        try {
            $db = \Typecho\Db::get();
            $fieldsTable = dygita_get_table('fields');

            $rows = $db->fetchAll(
                $db->select('name', 'str_value')
                    ->from($fieldsTable)
                    ->where('cid = ?', $cid)
                    ->where('name IN ?', [self::FIELD_PARSED, self::FIELD_CATALOG])
            );

            $parsed = '';
            $catalog = '';
            foreach ($rows as $row) {
                if ($row['name'] === self::FIELD_PARSED) {
                    $parsed = $row['str_value'];
                }
                if ($row['name'] === self::FIELD_CATALOG) {
                    $catalog = $row['str_value'];
                }
            }

            $result = ($parsed !== '' && $catalog !== '')
                ? ['parsed' => $parsed, 'catalog' => $catalog]
                : null;
        } catch (\Throwable $e) {
            $result = null;
        }

        self::$requestCache[$cid] = $result;
        return $result;
    }

    /**
     * 写入缓存到 custom fields
     */
    private static function saveCache($cid, $parsedHtml, $catalogHtml)
    {
        $db = \Typecho\Db::get();
        $fieldsTable = dygita_get_table('fields');

        self::upsertField($db, $fieldsTable, $cid, self::FIELD_PARSED, $parsedHtml);
        self::upsertField($db, $fieldsTable, $cid, self::FIELD_CATALOG, $catalogHtml);

        // 刷新请求级缓存
        self::$requestCache[$cid] = [
            'parsed' => $parsedHtml,
            'catalog' => $catalogHtml
        ];
    }

    /**
     * Upsert 单个 custom field
     */
    private static function upsertField($db, $table, $cid, $name, $value)
    {
        $exists = $db->fetchRow(
            $db->select('cid')->from($table)
                ->where('cid = ?', $cid)
                ->where('name = ?', $name)
        );

        if ($exists) {
            $db->query(
                $db->update($table)
                    ->rows(['str_value' => $value])
                    ->where('cid = ?', $cid)
                    ->where('name = ?', $name)
            );
        } else {
            $db->query(
                $db->insert($table)->rows([
                    'cid'         => $cid,
                    'name'        => $name,
                    'type'        => 'str',
                    'str_value'   => $value,
                    'int_value'   => 0,
                    'float_value' => 0
                ])
            );
        }
    }

    /**
     * 清除指定文章的目录缓存（文章删除或内容变更时调用）
     */
    public static function clearCache($cid)
    {
        $cid = (int) $cid;
        unset(self::$requestCache[$cid]);

        try {
            $db = \Typecho\Db::get();
            $fieldsTable = dygita_get_table('fields');
            $db->query(
                $db->delete($fieldsTable)
                    ->where('cid = ?', $cid)
                    ->where('name IN ?', [self::FIELD_PARSED, self::FIELD_CATALOG])
            );
        } catch (\Throwable $e) {
            // 静默
        }
    }
}

function themeConfig($form)
{
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
    $post = $db->fetchRow($db->select()->from(dygita_get_table('contents'))
        ->where('cid = ?', intval($pid))->where('type = ?', 'post')->where('status = ?', 'publish'));
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

function dygita_get_archive_posts() {
    $db = Typecho\Db::get();
    $table = dygita_get_table('contents');
    return $db->fetchAll($db->select()->from($table)
        ->where('type = ?', 'post')
        ->where('status = ?', 'publish')
        ->order('created', Typecho\Db::SORT_DESC));
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
    if ($theme === '' && isset($_COOKIE['dygita_theme_pref'])) {
        $cookieTheme = trim((string) $_COOKIE['dygita_theme_pref']);
        if ($cookieTheme === 'light' || $cookieTheme === 'dark') {
            $theme = $cookieTheme;
        }
    }
    if ($headerColor === '' && isset($_COOKIE['dygita_header_color'])) {
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
        'likeUrl' => \Typecho\Router::url('do', array('action' => 'dygita-like'), $options->index),
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
    if (preg_match('/^https?:\/\//i', $url) || (strlen($url) > 0 && $url[0] === '/')) {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
    return '';
}

/**
 * 按 slug 高效获取单个页面（请求级静态缓存）
 * 解决 header.php 中全量遍历所有页面查找特定 slug 的性能瓶颈
 * @param string $slug 页面缩略名
 * @return array|null ['title' => '', 'permalink' => ''] 或 null
 */
function dygita_get_page_by_slug($slug) {
    static $cache = [];
    $slug = trim((string) $slug);
    if ($slug === '') return null;

    if (isset($cache[$slug])) {
        return $cache[$slug];
    }

    $db = \Typecho\Db::get();
    $row = $db->fetchRow($db->select(['title', 'permalink'])
        ->from('table.contents')
        ->where('slug = ?', $slug)
        ->where('type = ?', 'page')
        ->where('status = ?', 'publish')
        ->limit(1));

    if ($row) {
        $cache[$slug] = ['title' => $row['title'], 'permalink' => $row['permalink']];
    } else {
        $cache[$slug] = null;
    }

    return $cache[$slug];
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

/**
 * 获取所有页面列表（请求级静态缓存）
 * 解决 header.php 中每次请求都遍历所有页面的性能问题
 * @param bool $refresh 强制刷新缓存
 * @return array [['slug' => '', 'title' => '', 'permalink' => ''], ...]
 */
function dygita_get_all_pages_cached($refresh = false) {
    static $cache = null;

    if ($cache !== null && !$refresh) {
        return $cache;
    }

    $cache = [];
    $db = \Typecho\Db::get();
    $rows = $db->fetchAll($db->select(['slug', 'title', 'permalink'])
        ->from('table.contents')
        ->where('type = ?', 'page')
        ->where('status = ?', 'publish')
        ->order('order', \Typecho\Db::SORT_ASC));

    foreach ($rows as $row) {
        $cache[] = [
            'slug' => $row['slug'],
            'title' => $row['title'],
            'permalink' => $row['permalink']
        ];
    }

    return $cache;
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
    $content = is_array($post) ? (string)($post['text'] ?? '') : (string)($post->text ?? '');
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

    return dygita_resolve_thumbnail_url($thumbValue, $content, $options);
}

/* 增加: 浏览量统计 */
function dygita_get_post_view($archive)
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
function dygita_agree_num($cid) {
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
function dygita_get_hot_posts($limit = 5) {
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

/* 增加: 随机文章 - 优化版本，避免 ORDER BY RAND() 全表扫描 */
function dygita_get_random_posts($limit = 5) {
    $db = Typecho\Db::get();
    $contentsTable = dygita_get_table('contents');
    
    // 性能优化：先获取所有已发布文章的 cid，在 PHP 层面随机选择
    // 避免使用 ORDER BY RAND() 导致的全表扫描和临时表创建
    $allCids = $db->fetchAll($db->select('cid')->from($contentsTable)
        ->where('status = ?', 'publish')
        ->where('type = ?', 'post'));
    
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
        ->where('status = ?', 'publish')
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
    dygita_bootstrap_runtime();
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
