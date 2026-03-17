<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<header class="archive-header">
    <h1>
        <?php $this->archiveTitle(array(
            'category'  => dygita_t('分类 %s 下的文章'),
            'search'    => dygita_t('包含关键字 %s 的文章'),
            'tag'       => dygita_t('标签 %s 下的文章'),
            'author'    => dygita_t('%s 发布的文章')
        ), '', ''); ?>
    </h1>
</header>

