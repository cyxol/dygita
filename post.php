<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$GLOBALS['dygita_content_class'] = 'posts-expand'; ?>
<?php
$catalog = Dygita_ArticleCatalog::instance();
$catalogCache = Dygita_Catalog_Cache::getCache($this->cid);
if ($catalogCache !== null) {
    $parsedContent = $catalogCache['parsed'];
} else {
    $parsedContent = $catalog->renderHtml($this->content);
}
?>
<?php $this->need('views/components/header.php'); dygita_record_post_view($this); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

        <header class="article-header">
            <h1 class="article-title"><a href="<?php $this->permalink() ?>"><?php $this->title() ?></a></h1>
            <div class="meta">
                <span class="muted"><i class="fa fa-clock-o"></i> <?php $this->date('Y-m-d G:i:s'); ?></span>
                <span class="muted"><i class="fa fa-eye"></i> <?php _e('浏览'); ?>(<?php echo dygita_get_post_view($this); ?>)</span>
                <span class="muted"><i class="fa fa-comments-o"></i> <a href="<?php $this->permalink() ?>#comments"><?php $this->commentsNum('0个评论', '1个评论', '%d个评论'); ?></a></span>
            </div>
        </header>

        <article class="article-content">
            <?php echo $parsedContent; ?>
        </article>

        <footer class="article-footer">
            <div class="article-tags">
                <i class="fa fa-tags"></i> <?php $this->tags(' ', true, '暂无标签'); ?>
            </div>
            
            <div class="article-share">
                <h4><i class="fa fa-share-alt"></i> <?php _e('分享到'); ?></h4>
                <div class="share-buttons">
                    <a href="#" class="share-btn wechat" title="<?php _e('微信分享'); ?>" onclick="return window.DygitaShare && window.DygitaShare.shareToWechat();">
                        <i class="fa fa-weixin"></i>
                        <span><?php _e('微信'); ?></span>
                    </a>
                    <a href="#" class="share-btn weibo" title="<?php _e('微博分享'); ?>" onclick="return window.DygitaShare && window.DygitaShare.shareToWeibo();">
                        <i class="fa fa-weibo"></i>
                        <span><?php _e('微博'); ?></span>
                    </a>
                    <a href="#" class="share-btn qq" title="<?php _e('QQ分享'); ?>" onclick="return window.DygitaShare && window.DygitaShare.shareToQQ();">
                        <i class="fa fa-qq"></i>
                        <span>QQ</span>
                    </a>
                    <a href="#" class="share-btn copy" title="<?php _e('复制链接'); ?>" onclick="return window.DygitaShare && window.DygitaShare.copyLink();">
                        <i class="fa fa-link"></i>
                        <span><?php _e('复制'); ?></span>
                    </a>
                </div>
            </div>
        </footer>
        
        <?php
        $shareUrl = $this->permalink;
        $shareTitle = $this->title;
        ob_start(); $this->excerpt(150); $shareExcerpt = ob_get_clean();
        $shareData = array(
            'url' => $shareUrl,
            'title' => $shareTitle,
            'excerpt' => strip_tags($shareExcerpt),
            'pic' => dygita_get_thumbnail($this)
        );
        ?>
        <script>
            window.DYGITA = window.DYGITA || {};
            window.DYGITA.shareData = <?php echo json_encode($shareData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
        </script>
        <script defer src="<?php $this->options->themeUrl('js/post-share.js'); ?>"></script>

        <nav class="article-nav">
            <span class="article-nav-prev"><?php $this->thePrev('%s', _t('没有了')); ?></span>
            <span class="article-nav-next"><?php $this->theNext('%s', _t('没有了')); ?></span>
        </nav>

        <div class="related_posts">
            <h3 class="related-title"><i class="fa fa-heart"></i> <?php _e('猜你喜欢'); ?></h3>

            <div class="related-content">
                <?php
                $related = dygita_get_related_posts($this->cid);
                if ($related['use_hot']):
                ?>
                    <div class="no-related">
                        <h4><?php _e('热门文章'); ?></h4>
                        <ul class="hot-posts"><?php dygita_get_hot_posts(6); ?></ul>
                    </div>
                <?php else: ?>
                    <ul class="related-list">
                        <?php while ($related['posts']->next()):
                            $permalink = $related['posts']->permalink;
                            $title = htmlspecialchars((string) $related['posts']->title, ENT_QUOTES, 'UTF-8');
                            $thumb = dygita_get_related_post_thumbnail($related['posts']);
                        ?>
                        <li class="related-item">
                            <a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>" target="_blank" rel="noopener noreferrer">
                                <img class="related-thumb" src="<?php echo $thumb; ?>" alt="<?php echo $title; ?>" loading="lazy" />
                                <span class="related-item-title"><?php echo $title; ?></span>
                            </a>
                        </li>
                        <?php endwhile; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
        <?php $this->need('views/components/comments.php'); ?>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

