<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
    </div>
</div>

<aside class="sidebar sidebar-right">
    <button class="sidebar-toggle right" aria-label="折叠右侧栏" title="折叠右侧栏">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"></polyline></svg>
    </button>

    <!-- 博主简介 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('关于博主'); ?></h2></div>
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
                            <span class="profile-stats-label"><?php dygita_e('日志'); ?></span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['categories']; ?></span>
                            <span class="profile-stats-label"><?php dygita_e('分类'); ?></span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['tags']; ?></span>
                            <span class="profile-stats-label"><?php dygita_e('标签'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 分类 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowCategory', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php dygita_e('分类'); ?></h2></div>
        <div class="widget-content">
            <div class="category-list">
                <?php $this->widget('Widget_Metas_Category_List')->to($categories); ?>
                <?php if($categories->have()): ?>
                    <?php while ($categories->next()): ?>
                        <div class="category-list-item">
                            <a href="<?php $categories->permalink(); ?>" class="category-list-link">
                                <?php $categories->name(); ?>
                            </a>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?php dygita_e('没有任何分类'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 标签云 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('标签云'); ?></h2></div>
        <div class="widget-content">
            <div class="tag-cloud">
                <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=mid&ignoreZeroCount=1&desc=0&limit=30')->to($tags); ?>
                <?php if($tags->have()): ?>
                    <?php while ($tags->next()): ?>
                        <a href="<?php $tags->permalink(); ?>" title="<?php $tags->count(); ?> 个话题"><?php $tags->name(); ?></a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?php dygita_e('没有任何标签'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>
</div>
