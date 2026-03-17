<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
if (function_exists('dygita_content_class')) {
    dygita_content_class('index posts-expand');
} else {
    $GLOBALS['dygita_content_class'] = 'index posts-expand';
}
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>

        <header class="archive-header">
            <h1><?php echo htmlspecialchars((string) dygita_t('关于我'), ENT_QUOTES, 'UTF-8'); ?></h1>
        </header>

        <?php $this->need('views/components/author-about.php'); ?>

        <header class="archive-header section-title">
            <h2><?php echo htmlspecialchars((string) sprintf(dygita_t('%s 发布的文章'), $this->author->screenName), ENT_QUOTES, 'UTF-8'); ?></h2>
        </header>

        <?php while ($this->next()): ?>
            <?php $this->need('views/components/post-card.php'); ?>
        <?php endwhile; ?>

        <?php $this->pageNav('&laquo; ' . dygita_t('前一页'), dygita_t('后一页') . ' &raquo;'); ?>

<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

