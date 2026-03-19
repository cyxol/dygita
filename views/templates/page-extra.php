<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>
<div class="pagewrapper clearfix">
    <aside class="pagesidebar">
        <ul class="pagesider-menu">
            <?php $this->widget('Widget_Contents_Page_List')->to($pages); ?>
            <?php while ($pages->next()): ?>
                <li><a href="<?php $pages->permalink(); ?>" title="<?php $pages->title(); ?>"><?php $pages->title(); ?></a></li>
            <?php endwhile; ?>
        </ul>
    </aside>
    <div class="pagecontent">
        <header class="pageheader clearfix">
            <h1 class="pull-left">
                <a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
            </h1>
            <div class="pull-right"><!-- 分享功能 -->
            </div>
        </header>
        <div class="article-content">
            <?php $this->content(); ?>
        </div>
        
        <?php $this->need('views/components/comments.php'); ?>
    </div>
</div>
<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
