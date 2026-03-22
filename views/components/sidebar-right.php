<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
    </div>
</div>

<aside class="sidebar sidebar-right">
    <button class="sidebar-toggle right" aria-label="折叠右侧栏" title="折叠右侧栏">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 6 15 12 9 18"></polyline></svg>
    </button>
    <!-- 分类 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowCategory', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php _e('分类'); ?></h2></div>
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
                    <?php _e('没有任何分类'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- 标签云 -->
    <div class="widget">
        <div class="title"><h2><?php _e('标签云'); ?></h2></div>
        <div class="widget-content">
            <div class="tag-cloud">
                <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=mid&ignoreZeroCount=1&desc=0&limit=30')->to($tags); ?>
                <?php if($tags->have()): ?>
                    <?php while ($tags->next()): ?>
                        <a href="<?php $tags->permalink(); ?>" title="<?php $tags->count(); ?> 个话题"><?php $tags->name(); ?></a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <?php _e('没有任何标签'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- 联系方式 -->
    <div class="widget">
        <div class="title"><h2><?php _e('联系方式'); ?></h2></div>
        <div class="widget-content">
            <div class="contact-info">
                <?php $contactEmail = $this->options->contactEmail ? dygita_escape($this->options->contactEmail) : ''; ?>
                <?php $contactQQ = $this->options->contactQQ ? dygita_escape($this->options->contactQQ) : ''; ?>
                <p><i class="fa fa-envelope"></i> <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a></p>
                <p><i class="fa fa-qq"></i> <?php echo $contactQQ; ?></p>
            </div>
        </div>
    </div>

    <!-- 友情链接 -->
    <?php if (!empty($this->options->sidebarBlock) && in_array('ShowLinks', $this->options->sidebarBlock)): ?>
    <div class="widget">
        <div class="title"><h2><?php _e('友情链接'); ?></h2></div>
        <div class="widget-content">
            <ul>
                <?php dygita_get_links(); ?>
            </ul>
        </div>
    </div>
    <?php endif; ?>

    <!-- 广告位 -->
    <div class="widget">
        <div class="title"><h2><?php _e('赞助商'); ?></h2></div>
        <div class="widget-content">
            <div class="ad-box">
                <?php
                $adLinkUrl = $this->options->adLinkUrl ? dygita_escape_url($this->options->adLinkUrl) : '';
                if ($this->options->adImageUrl) {
                    $adImageUrl = dygita_escape_url($this->options->adImageUrl);
                } else {
                    $adImageUrl = htmlspecialchars($this->options->themeUrl . '/img/default.png', ENT_QUOTES, 'UTF-8');
                }
                ?>
                <?php if ($adLinkUrl): ?>
                    <a href="<?php echo $adLinkUrl; ?>" target="_blank" rel="noopener nofollow">
                <?php endif; ?>
                    <img src="<?php echo $adImageUrl; ?>" alt="AD" loading="lazy" width="250" height="150">
                <?php if ($adLinkUrl): ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</aside>
</div>
