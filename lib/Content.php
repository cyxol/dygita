<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function dygita_get_table($table) {
    $db = Typecho\Db::get();
    return $db->getPrefix() . $table;
}

function dygita_get_archives_url($options) {
    return rtrim($options->siteUrl, '/') . '/archives/';
}

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

function dygita_get_archive_posts($page = 1, $pageSize = 50) {
    return \Typecho\Db::get()->fetchAll(Dygita_Repository::publicPosts()
        ->select('cid','title','slug','created','type','status','password')
        ->order('created', \Typecho\Db::SORT_DESC)->order('cid', \Typecho\Db::SORT_DESC)
        ->page(max(1, (int) $page), min(100, max(1, (int) $pageSize))));
}

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

function dygita_get_stat() {
    $stat = \Typecho\Widget::widget('Widget\Stat');
    return array(
        'posts'      => $stat->publishedPostsNum,
        'comments'   => $stat->publishedCommentsNum,
        'categories' => $stat->categoriesNum,
        'tags'       => $stat->tagsNum,
    );
}
