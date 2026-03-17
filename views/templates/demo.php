<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
            
            <?php 
            $pid = $this->cid;
            $title = $this->title;
            $values = $this->metadata['git_demo'];
            empty($values) ? $theCode = '' : $theCode = $values;
            ?>
            
            <div class="demo-header">
                <a class="demo-name" href="<?php $this->permalink(); ?>">&laquo; <?php echo $title ?></a>
            </div>
            <div class="demo-container demo"><?php echo $theCode; ?></div>
        </div>
        
        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
