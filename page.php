<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$GLOBALS['dygita_content_class'] = 'posts-expand'; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

        <header class="article-header">
            <h1 class="article-title">
                <a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a>
            </h1>
        </header>

        <article class="article-content">
            <?php $this->content(); ?>
        </article>

        <?php $this->need('views/components/comments.php'); ?>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

