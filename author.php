<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
if (function_exists('dygita_content_class')) {
    dygita_content_class('index posts-expand');
} else {
    $GLOBALS['dygita_content_class'] = 'index posts-expand';
}
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>

        <header class="archive-header">
            <h1><?php echo htmlspecialchars((string) dygita_t('关于我'), ENT_QUOTES, 'UTF-8'); ?></h1>
        </header>

        <?php $this->need('views/components/author-about.php'); ?>

        <header class="archive-header section-title">
            <h2><?php echo htmlspecialchars((string) sprintf(dygita_t('%s 发布的文章'), $this->author->screenName), ENT_QUOTES, 'UTF-8'); ?></h2>
        </header>

        <?php while ($this->next()): ?>
            <article itemscope itemtype="http://schema.org/Article" class="post-block index">
                <link itemprop="mainEntityOfPage" href="<?php $this->permalink(); ?>">
                <span hidden itemprop="author" itemscope itemtype="http://schema.org/Person">
                    <meta itemprop="name" content="<?php $this->author(); ?>">
                </span>
                <span hidden itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
                    <meta itemprop="name" content="<?php $this->options->title(); ?>">
                </span>
                <header class="post-header">
                    <h2 class="post-title" itemprop="name headline">
                        <a href="<?php $this->permalink(); ?>" class="post-title-link" itemprop="url"><?php $this->title(); ?></a>
                    </h2>
                </header>
                <div class="post-body" itemprop="articleBody">
                    <div class="thumb">
                        <a target="_blank" href="<?php $this->permalink(); ?>">
                            <img itemprop="contentUrl" class="random lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?php echo getThumbnail($this); ?>" alt="<?php $this->title(); ?>" width="200" height="120" />
                        </a>
                    </div>
                    <div class="excerpt">
                        <p><?php $this->excerpt(140, '...'); ?></p>
                    </div>
                </div>
                <p class="auth-span">
                    <span class="muted"><i class="fa fa-user"></i> <a href="<?php $this->author->permalink(); ?>"><?php $this->author(); ?></a></span>
                    <span class="muted"><i class="fa fa-clock-o"></i> <?php $this->date('Y-m-d'); ?></span>
                    <span class="muted"><i class="fa fa-eye"></i> <?php dygita_e('浏览'); ?>(<?php getPostView($this); ?>)</span>
                    <span class="muted"><i class="fa fa-comments-o"></i> <a target="_blank" href="<?php $this->permalink(); ?>#comments"><?php $this->commentsNum(dygita_t('0评论'), dygita_t('1评论'), dygita_t('%d评论')); ?></a></span>
                    <span class="muted">
                        <a href="#" data-action="ding" data-id="<?php $this->cid(); ?>" class="Addlike action" role="button"><i class="fa fa-heart-o"></i><span class="count"><?php echo agreeNum($this->cid); ?></span><?php dygita_e('喜欢'); ?></a>
                    </span>
                </p>
            </article>
        <?php endwhile; ?>

        <?php $this->pageNav('&laquo; ' . dygita_t('前一页'), dygita_t('后一页') . ' &raquo;'); ?>

<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

