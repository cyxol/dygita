<?php
if (!defined("__TYPECHO_ROOT_DIR__")) exit;
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


final class Dygita_Catalog_Cache {
    private static array $rendered = [];
    public static function render($archive): array {
        // Typecho has already applied password checks and content filters here.
        $html = (string) $archive->content;
        $key = (int) $archive->cid . ':' . hash('sha256', $html);
        if (!isset(self::$rendered[$key])) {
            $catalog = new Dygita_ArticleCatalog();
            self::$rendered[$key] = ['parsed' => $catalog->renderHtml($html),
                'catalog' => $archive->hidden ? '' : $catalog->renderCatalogHtml()];
        }
        return self::$rendered[$key];
    }
}
