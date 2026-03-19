<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

<?php $categoryPathInfo = trim((string) $this->request->getPathInfo(), '/'); ?>
<?php if ($categoryPathInfo === 'category' || $categoryPathInfo === 'categories'): ?>
<header class="archive-header">
        <h1><?php echo htmlspecialchars((string) dygita_t('文章分类'), ENT_QUOTES, 'UTF-8'); ?></h1>
</header>

<article class="article-content">
        <div class="category-list-page">
                <div class="category-grid">
                        <?php $this->widget('Widget_Metas_Category_List')->to($categories); ?>
                        <?php while ($categories->next()): ?>
                                <a href="<?php $categories->permalink(); ?>" class="category-item">
                                        <span class="category-name"><?php $categories->name(); ?></span>
                                        <span class="category-count"><?php $categories->count(); ?></span>
                                </a>
                        <?php endwhile; ?>
                </div>
        </div>
</article>
<?php else: ?>
                <?php $this->need('views/components/archive-title.php'); ?>

                <?php $this->need('views/components/archive-loop.php'); ?>
<?php endif; ?>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>


