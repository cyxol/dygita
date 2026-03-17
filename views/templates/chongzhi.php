<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
            
            <?php if ($this->user->hasLogin()): ?>
            <div class="alert alert-warning" role="alert">
                本站充值功能暂时不可用，请联系管理员
            </div>
            <?php else: ?>
            <div class="alert alert-error" role="alert">
                本页面需要您登录才可以操作，请先 <a href="<?php $this->options->loginUrl(); ?>">点击登录</a>
            </div>
            <?php endif; ?>
        </div>
        
        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
