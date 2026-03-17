<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$catalog = ArticleCatalog::instance();
$parsedContent = $catalog->renderHtml($this->content);
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>

        <header class="article-header">
            <h1 class="article-title"><a href="<?php $this->permalink() ?>"><?php $this->title() ?></a></h1>
            <div class="meta">
                <span class="muted"><i class="fa fa-user"></i> <a href="<?php $this->author->permalink(); ?>"><?php $this->author(); ?></a></span>
                <span class="muted"><i class="fa fa-clock-o"></i> <?php $this->date('Y-m-d G:i:s'); ?></span>
                <span class="muted"><i class="fa fa-eye"></i> <?php _e('浏览'); ?>(<?php getPostView($this); ?>)</span>
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
        ob_start(); $this->permalink(); $shareUrl = ob_get_clean();
        ob_start(); $this->title(); $shareTitle = ob_get_clean();
        ob_start(); $this->excerpt(150); $shareExcerpt = ob_get_clean();
        $shareData = array(
            'url' => $shareUrl,
            'title' => $shareTitle,
            'excerpt' => strip_tags($shareExcerpt),
            'pic' => getThumbnail($this)
        );
        ?>
        <script>
            var shareData = <?php echo json_encode($shareData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG); ?>;
        </script>
        <script defer src="<?php $this->options->themeUrl('js/post-share.js'); ?>"></script>

        <nav class="article-nav">
            <span class="article-nav-prev"><?php $this->thePrev('%s', _t('没有了')); ?></span>
            <span class="article-nav-next"><?php $this->theNext('%s', _t('没有了')); ?></span>
        </nav>

        <?php $this->need('views/components/related.php'); ?>
        <?php $this->need('views/components/comments.php'); ?>

<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

