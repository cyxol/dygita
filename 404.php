<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
<?php $this->need('views/components/header.php'); ?>

<?php $this->need('views/components/sidebar-left.php'); ?>

        <div class="error-404">
            <h1 class="error-code">404</h1>
            <h2 class="error-message"><?php dygita_e('页面不存在'); ?></h2>
            <p class="error-description"><?php dygita_e('抱歉，您访问的页面可能已被删除、移动或输入了错误的地址。'); ?><br><?php dygita_e('不过别担心，您可以通过以下方式找到您需要的内容。'); ?></p>
            
            <!-- 搜索框 -->
            <div class="search-box">
                <form method="post" action="<?php $this->options->siteUrl(); ?>">
                    <input type="text" name="s" placeholder="<?php dygita_e('搜索您想找的内容...'); ?>" aria-label="<?php dygita_e('搜索'); ?>" />
                    <button type="submit"><i class="fa fa-search"></i></button>
                </form>
            </div>
            
            <!-- 操作按钮 -->
            <div class="error-actions">
                <a href="#" class="btn btn-secondary" onclick="history.back(); return false;"><i class="fa fa-arrow-left"></i> <?php dygita_e('返回上一页'); ?></a>
                <a href="<?php $this->options->siteUrl(); ?>" class="btn btn-primary"><i class="fa fa-home"></i> <?php dygita_e('返回首页'); ?></a>
            </div>
            
            <!-- 内容区域 -->
            <div class="content-section">
                <!-- 最近文章 -->
                <div class="content-block">
                    <h3><?php dygita_e('最近发布'); ?></h3>
                    <ul>
                        <?php $this->widget('Widget_Contents_Post_Recent')->to($recent); ?>
                        <?php if ($recent->have()): ?>
                            <?php while ($recent->next()): ?>
                                <li><a href="<?php $recent->permalink(); ?>"><i class="fa fa-file-text-o"></i> <?php $recent->title(); ?></a></li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li><?php dygita_e('暂无文章'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
                
                <!-- 热门标签 -->
                <div class="content-block">
                    <h3><?php dygita_e('热门标签'); ?></h3>
                    <div class="tags-cloud">
                        <?php $this->widget('Widget_Metas_Tag_Cloud', array('sort' => 'count', 'ignoreZeroCount' => true, 'limit' => 20))->to($tags); ?>
                        <?php if ($tags->have()): ?>
                            <?php while ($tags->next()): ?>
                                <a href="<?php $tags->permalink(); ?>" title="<?php $tags->count(); ?> 篇文章"><?php $tags->name(); ?></a>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p><?php dygita_e('暂无标签'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- 站点导航 -->
                <div class="content-block">
                    <h3><?php dygita_e('站点导航'); ?></h3>
                    <ul>
                        <li><a href="<?php $this->options->siteUrl(); ?>"><i class="fa fa-home"></i> <?php dygita_e('首页'); ?></a></li>
                        <?php $pages = $this->widget('Widget_Contents_Page_List')->to($pages); ?>
                        <?php while($pages->next()): ?>
                            <li><a href="<?php $pages->permalink(); ?>"><i class="fa fa-file-o"></i> <?php $pages->title(); ?></a></li>
                        <?php endwhile; ?>
                        <li><a href="<?php echo htmlspecialchars(dygita_get_archives_url($this->options), ENT_QUOTES, 'UTF-8'); ?>"><i class="fa fa-archive"></i> <?php dygita_e('归档'); ?></a></li>
                    </ul>
                </div>
                
                <!-- 分类浏览 -->
                <div class="content-block">
                    <h3><?php dygita_e('分类浏览'); ?></h3>
                    <ul>
                        <?php $categories = $this->widget('Widget_Metas_Category_List')->to($categories); ?>
                        <?php if ($categories->have()): ?>
                            <?php while ($categories->next()): ?>
                                <li><a href="<?php $categories->permalink(); ?>"><i class="fa fa-folder-o"></i> <?php $categories->name(); ?> (<?php $categories->count(); ?>)</a></li>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <li><?php dygita_e('暂无分类'); ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

