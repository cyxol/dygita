<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
<div class="related_posts">
    <h3 class="related-title"><i class="fa fa-heart"></i> <?php dygita_e('猜你喜欢'); ?></h3>
    
    <div class="related-content">
        <?php
        $related = dygita_get_related_posts($this->cid);
        if ($related['use_hot']):
        ?>
            <div class="no-related">
                <h4><?php dygita_e('热门文章'); ?></h4>
                <ul class="hot-posts"><?php getHotPosts(6); ?></ul>
            </div>
        <?php else: ?>
            <ul class="related-list">
                <?php foreach ($related['posts'] as $post):
                    $permalink = Typecho\Router::url('post', $post, Typecho\Widget::widget('Widget_Options')->index);
                    $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
                    $thumb = getRelatedPostThumbnail($post);
                ?>
                <li class="related-item">
                    <a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>" target="_blank">
                        <img class="related-thumb" src="<?php echo $thumb; ?>" alt="<?php echo $title; ?>" loading="lazy" />
                        <span class="related-item-title"><?php echo $title; ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
