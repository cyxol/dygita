<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php if ($this->is('post') || $this->is('page')): ?>
<div class="widget widget-catalog">
    <div class="title">
        <h2><i class="fa fa-list"></i> <?php _e('文章目录'); ?></h2>
    </div>
    <div class="widget-content">
        <?php 
            $catalog = ArticleCatalog::instance();
            $catalogHtml = $catalog->renderCatalogHtml();
            if (!empty($catalogHtml)) {
                echo '<div class="catalog-content">' . $catalogHtml . '</div>';
            } else {
                echo '<p class="no-catalog">' . _t('本文没有目录') . '</p>';
            }
        ?>
    </div>
</div>
<?php endif; ?>
