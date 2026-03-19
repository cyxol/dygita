<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div class="main-container">
<aside class="sidebar sidebar-left">
    <!-- 博主简介 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('关于博主'); ?></h2></div>
        <div class="widget-content">
            <div class="widget-profile">
                <div class="profile-avatar">
                    <?php if ($this->options->logoUrl): ?>
                    <img src="<?php $this->options->logoUrl(); ?>" alt="Avatar">
                    <?php else: ?>
                    <img src="<?php $this->options->themeUrl('img/authorpic.jpg'); ?>" alt="Avatar">
                    <?php endif; ?>
                </div>
                <div class="profile-info">
                    <p><?php $this->options->description(); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- 热门文章 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentPosts', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php dygita_e('热门文章'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php getHotPosts(); ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- 猜你喜欢 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('猜你喜欢'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php getRandomPosts(); ?>
            </ul>
        </div>
    </div>

    <!-- 最新评论 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentComments', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php dygita_e('最新评论'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php $this->widget('Widget_Comments_Recent')->to($comments); ?>
                <?php while($comments->next()): ?>
                    <li><a href="<?php $comments->permalink(); ?>" title="Comment by <?php echo htmlspecialchars($comments->author, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($comments->author, ENT_QUOTES, 'UTF-8'); ?></a>: <?php $comments->excerpt(35, '...'); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- 网站统计 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('网站统计'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php $stat = getStat(); ?>
                <li><?php dygita_e('文章总数'); ?>: <?php echo $stat['posts']; ?></li>
                <li><?php dygita_e('评论总数'); ?>: <?php echo $stat['comments']; ?></li>
                <li><?php dygita_e('分类总数'); ?>: <?php echo $stat['categories']; ?></li>
                <li><?php dygita_e('标签总数'); ?>: <?php echo $stat['tags']; ?></li>
            </ul>
        </div>
    </div>

    <!-- 文章目录 -->
    <?php if ($this->is('post') || $this->is('page')): ?>
    <div class="widget widget-catalog">
        <div class="title">
            <h2><i class="fa fa-list"></i> <?php dygita_e('文章目录'); ?></h2>
        </div>
        <div class="widget-content">
            <?php 
                $catalog = ArticleCatalog::instance();
                $catalogHtml = $catalog->renderCatalogHtml();
                if (!empty($catalogHtml)) {
                    echo '<div class="catalog-content">' . $catalogHtml . '</div>';
                } else {
                    echo '<p class="no-catalog">' . dygita_t('本文没有目录') . '</p>';
                }
            ?>
        </div>
    </div>
    <?php endif; ?>
</aside>

<div class="content-wrap" role="main">
    <div class="content<?php echo isset($GLOBALS['dygita_content_class']) ? ' ' . htmlspecialchars($GLOBALS['dygita_content_class'], ENT_QUOTES, 'UTF-8') : ''; ?>">
