<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
<div class="pagewrapper clearfix">
    <div class="article-content">
        <?php $this->content(); ?>
    </div>
    
    <?php $this->need('views/components/comments.php'); ?>
</div>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
