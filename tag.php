<?php
/**
 * 标签云页面
 *
 * @package custom
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;

$this->need('views/components/header.php');
$this->need('views/components/sidebar-left.php');
?>
            <header class="article-header">
                <h1 class="article-title"><?php dygita_e('标签云'); ?></h1>
            </header>

            <article class="article-content">
                <div class="tag-cloud-page">
                    <div class="tag-cloud-canvas-wrapper">
                        <canvas height="500" width="700" id="tag-cloud-tags">
                            <p><?php dygita_e('标签云'); ?></p>
                            <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&ignoreZeroCount=1&desc=1&limit=50')->to($tags); ?>
                            <?php while ($tags->next()): ?>
                                <a href="<?php $tags->permalink(); ?>" class="tag"><?php $tags->name(); ?></a>
                            <?php endwhile; ?>
                        </canvas>
                    </div>

                    <!-- 备用标签列表（当 canvas 不支持时显示） -->
                    <div class="tag-cloud-fallback">
                        <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=count&ignoreZeroCount=1&desc=1&limit=50')->to($tagsFallback); ?>
                        <?php while ($tagsFallback->next()): ?>
                            <a href="<?php $tagsFallback->permalink(); ?>" class="tag" title="<?php $tagsFallback->count(); ?> 篇文章"><?php $tagsFallback->name(); ?></a>
                        <?php endwhile; ?>
                    </div>
                </div>
            </article>

            <script defer src="<?php $this->options->themeUrl('js/tag-cloud-page.js'); ?>"></script>
            <?php $this->need('views/components/sidebar-right.php');
            $this->need('views/components/footer.php'); ?>

