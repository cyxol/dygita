<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>

        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
            
            <h3><?php dygita_e('最新文章'); ?></h3>
            <ul>
                <?php $this->widget('Widget_Contents_Post_Recent')->to($posts); ?>
                <?php while ($posts->next()): ?>
                    <li><a href="<?php $posts->permalink(); ?>" title="<?php $posts->title(); ?>" target="_blank"><?php $posts->title(); ?></a></li>
                <?php endwhile; ?>
            </ul>
            
            <h3><?php dygita_e('分类浏览'); ?></h3>
            <ul>
                <?php $this->widget('Widget_Metas_Category_List')->to($categories); ?>
                <?php while ($categories->next()): ?>
                    <li><a href="<?php $categories->permalink(); ?>" title="<?php $categories->name(); ?>"><?php $categories->name(); ?></a></li>
                <?php endwhile; ?>
            </ul>
            
            <h3><?php dygita_e('页面'); ?></h3>
            <ul>
                <?php $this->widget('Widget_Contents_Page_List')->to($sitemapPages); ?>
                <?php while ($sitemapPages->next()): ?>
                    <li><a href="<?php $sitemapPages->permalink(); ?>" title="<?php $sitemapPages->title(); ?>"><?php $sitemapPages->title(); ?></a></li>
                <?php endwhile; ?>
            </ul>
        </div>
        
        <?php $this->need('views/components/comments.php'); ?>

<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
