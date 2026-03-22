<?php
/**
 * 标签云页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

if (function_exists('dygita_content_class')) {
    dygita_content_class('index posts-expand');
} else {
    $GLOBALS['dygita_content_class'] = 'index posts-expand';
}

$this->need('views/components/header.php');
$this->need('views/components/sidebar-left.php');

$tagPathInfo = trim((string) $this->request->getPathInfo(), '/');
$tagRouteType = '';
if (isset($this->parameter) && is_array($this->parameter) && isset($this->parameter['type'])) {
    $tagRouteType = (string) $this->parameter['type'];
}

$isTagCloudPage = in_array($tagRouteType, ['tags_cloud', 'tags_cloud_page'], true)
    || $tagPathInfo === 'tags'
    || $tagPathInfo === 'page-tag-cloud.html';
?>
<?php if ($isTagCloudPage): ?>
            <header class="article-header">
                <h1 class="article-title"><?php _e('标签云'); ?></h1>
            </header>

            <article class="article-content">
                <div class="tag-cloud-page">
                    <div class="tag-cloud tag-cloud-3col">
                        <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&ignoreZeroCount=1&desc=1&limit=50')->to($tags); ?>
                        <?php while ($tags->next()): ?>
                            <a href="<?php $tags->permalink(); ?>" title="<?php $tags->count(); ?> 篇文章"><?php $tags->name(); ?></a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </article>
<?php else: ?>
            <?php $this->need('views/components/archive-title.php'); ?>

            <?php $this->need('views/components/archive-loop.php'); ?>
<?php endif; ?>

<?php
$this->need('views/components/sidebar-right.php');
$this->need('views/components/footer.php');
?>

