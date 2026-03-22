<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div class="main-container">
<aside class="sidebar sidebar-left">
    <button class="sidebar-toggle left" aria-label="折叠左侧栏" title="折叠左侧栏">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <!-- 博主简介 -->
    <div class="widget">
        <div class="title"><h2><?php _e('关于博主'); ?></h2></div>
        <div class="widget-content">
            <div class="widget-profile">
                <div class="profile-avatar">
                    <img src="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>" alt="Yacine Tsai">
                </div>
                <div class="profile-info">
                    <p>Yacine Tsai</p>
                    <p>大数据产品经理</p>
                    <p>Vibe Coding</p>
                    <?php $profileStat = dygita_get_stat(); ?>
                    <div class="profile-stats" aria-label="站点统计">
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['posts']; ?></span>
                            <span class="profile-stats-label">日志</span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['categories']; ?></span>
                            <span class="profile-stats-label">分类</span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['tags']; ?></span>
                            <span class="profile-stats-label">标签</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 热门文章 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentPosts', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php _e('热门文章'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php dygita_get_hot_posts(); ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- 猜你喜欢 -->
    <div class="widget">
        <div class="title"><h2><?php _e('猜你喜欢'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php dygita_get_random_posts(); ?>
            </ul>
        </div>
    </div>

    <!-- 最新评论 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowRecentComments', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php _e('最新评论'); ?></h2></div>
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
        <div class="title"><h2><?php _e('网站统计'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php $stat = dygita_get_stat(); ?>
                <li><?php _e('文章总数'); ?>: <?php echo $stat['posts']; ?></li>
                <li><?php _e('评论总数'); ?>: <?php echo $stat['comments']; ?></li>
                <li><?php _e('分类总数'); ?>: <?php echo $stat['categories']; ?></li>
                <li><?php _e('标签总数'); ?>: <?php echo $stat['tags']; ?></li>
            </ul>
        </div>
    </div>

    <!-- 文章目录 -->
    <?php if ($this->is('post') || $this->is('page')): ?>
    <div class="widget widget-catalog">
        <div class="title">
            <h2><i class="fa fa-list"></i> <?php _e('文章目录'); ?></h2>
        </div>
        <div class="widget-content">
            <?php
                $catalogCache = Dygita_Catalog_Cache::getCache($this->cid);
                if ($catalogCache !== null) {
                    $catalogHtml = $catalogCache['catalog'];
                } else {
                    $catalog = Dygita_ArticleCatalog::instance();
                    $catalogHtml = $catalog->renderCatalogHtml();
                }
                if (!empty($catalogHtml)) {
                    echo '<div class="catalog-content">' . $catalogHtml . '</div>';
                } else {
                    echo '<p class="no-catalog">' . _t('本文没有目录') . '</p>';
                }
            ?>
        </div>
    </div>
    <?php endif; ?>
</aside>

<div class="content-wrap" role="main">
    <div class="content<?php echo isset($GLOBALS['dygita_content_class']) ? ' ' . htmlspecialchars($GLOBALS['dygita_content_class'], ENT_QUOTES, 'UTF-8') : ''; ?>">
