<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php $hasPosts = false; ?>
<?php while ($this->next()): ?>
    <?php $hasPosts = true; ?>
    <?php $this->need('views/components/post-card.php'); ?>
<?php endwhile; ?>

<?php if (!$hasPosts): ?>
    <article class="excerpt">
        <h2 class="post-title"><?php _e('没有找到内容'); ?></h2>
    </article>
<?php endif; ?>

<?php
try {
    $this->pageNav('&laquo; ' . _t('前一页'), _t('后一页') . ' &raquo;');
} catch (Throwable $e) {
    // Skip broken pagination rendering to avoid full-page 500.
}
?>

