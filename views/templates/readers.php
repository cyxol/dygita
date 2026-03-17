<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
// 读者墙函数
function readers_wall($outer = '1', $timer = '100', $limit = '60') {
    $db = Typecho\Db::get();
    $startTime = time() - ($timer * 30 * 24 * 3600); // 计算开始时间
    
    // 查询评论数据
    $query = $db->query(
        $db->select(
            'COUNT(cid) as cnt',
            'author',
            'url',
            'mail'
        )->from('table.comments')
        ->where('created > ?', $startTime)
        ->where('status = ?', 'approved')
        ->where('type = ?', 'comment')
        ->where('author != ?', $outer)
        ->group('author')
        ->order('cnt', Typecho\Db::SORT_DESC)
        ->limit($limit)
    );
    
    $type = '';
    while ($count = $query->fetch()) {
        $rawUrl = isset($count['url']) ? trim((string) $count['url']) : '';
        $c_url = '#';
        if ($rawUrl !== '' && filter_var($rawUrl, FILTER_VALIDATE_URL)) {
            $parsed = parse_url($rawUrl);
            $scheme = isset($parsed['scheme']) ? strtolower($parsed['scheme']) : '';
            if (in_array($scheme, array('http', 'https'), true)) {
                $c_url = $rawUrl;
            }
        }
        $author = isset($count['author']) ? htmlspecialchars((string) $count['author'], ENT_QUOTES, 'UTF-8') : '';
        $commentCount = isset($count['cnt']) ? (int) $count['cnt'] : 0;
        $avatar = Typecho\Common::gravatarUrl($count['mail'], 64, '', '', true);
        $type .= '<a id="duzhe" target="_blank" rel="noopener noreferrer nofollow" href="' . htmlspecialchars($c_url, ENT_QUOTES, 'UTF-8') . '" title="[' . $author . ']近期评论' . $commentCount . '次"><img src="' . htmlspecialchars($avatar, ENT_QUOTES, 'UTF-8') . '" alt="' . $author . '"><span>' . $author . '</span></a>';
    }
    
    echo $type;
}
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
        </div>
        
        <div class="readers">
            <?php readers_wall(); ?>
        </div>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
