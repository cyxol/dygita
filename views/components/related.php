<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
<div class="related_posts">
    <h3 class="related-title"><i class="fa fa-heart"></i> <?php dygita_e('猜你喜欢'); ?></h3>
    
    <div class="related-content">
        <?php
        $hasRelated = false;
        $currentCid = $this->cid;
        $db = Typecho\Db::get();
        
        // 方法1: 基于标签获取相关文章
        $tags = $db->fetchAll($db->select('mid')->from('table.relationships')
            ->where('cid = ?', $currentCid));
        
        if (!empty($tags)) {
            $tagMids = array_column($tags, 'mid');
            
            // 获取这些标签下的其他文章
            $relatedCids = $db->fetchAll($db->select('DISTINCT cid')->from('table.relationships')
                ->where('mid IN ?', $tagMids)
                ->where('cid != ?', $currentCid)
                ->limit(6));
            
            if (!empty($relatedCids)) {
                $cids = array_column($relatedCids, 'cid');
                
                $relatedPosts = $db->fetchAll($db->select()->from('table.contents')
                    ->where('cid IN ?', $cids)
                    ->where('status = ?', 'publish')
                    ->where('type = ?', 'post')
                    ->order('created', Typecho\Db::SORT_DESC)
                    ->limit(6));
                
                if (!empty($relatedPosts)) {
                    $hasRelated = true;
                    echo '<ul class="related-list">';
                    foreach ($relatedPosts as $post) {
                        $permalink = Typecho\Router::url('post', $post, Typecho\Widget::widget('Widget_Options')->index);
                        $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
                        
                        // 获取缩略图
                        $thumb = getRelatedPostThumbnail($post);
                        
                        echo '<li class="related-item">';
                        echo '<a href="' . $permalink . '" title="' . $title . '" target="_blank">';
                        echo '<img class="related-thumb" src="' . $thumb . '" alt="' . $title . '" loading="lazy" />';
                        echo '<span class="related-item-title">' . $title . '</span>';
                        echo '</a>';
                        echo '</li>';
                    }
                    echo '</ul>';
                }
            }
        }
        
        // 方法2: 如果没有基于标签的相关文章，获取同分类文章
        if (!$hasRelated) {
            $categories = $db->fetchAll($db->select('table.metas.mid')->from('table.metas')
                ->join('table.relationships', 'table.metas.mid = table.relationships.mid')
                ->where('table.relationships.cid = ?', $currentCid)
                ->where('table.metas.type = ?', 'category'));
            
            if (!empty($categories)) {
                $catMids = array_column($categories, 'mid');
                
                $relatedCids = $db->fetchAll($db->select('DISTINCT cid')->from('table.relationships')
                    ->where('mid IN ?', $catMids)
                    ->where('cid != ?', $currentCid)
                    ->limit(6));
                
                if (!empty($relatedCids)) {
                    $cids = array_column($relatedCids, 'cid');
                    
                    $relatedPosts = $db->fetchAll($db->select()->from('table.contents')
                        ->where('cid IN ?', $cids)
                        ->where('status = ?', 'publish')
                        ->where('type = ?', 'post')
                        ->order('created', Typecho\Db::SORT_DESC)
                        ->limit(6));
                    
                    if (!empty($relatedPosts)) {
                        $hasRelated = true;
                        echo '<ul class="related-list">';
                        foreach ($relatedPosts as $post) {
                            $permalink = Typecho\Router::url('post', $post, Typecho\Widget::widget('Widget_Options')->index);
                            $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
                            $thumb = getRelatedPostThumbnail($post);
                            
                            echo '<li class="related-item">';
                            echo '<a href="' . $permalink . '" title="' . $title . '" target="_blank">';
                            echo '<img class="related-thumb" src="' . $thumb . '" alt="' . $title . '" loading="lazy" />';
                            echo '<span class="related-item-title">' . $title . '</span>';
                            echo '</a>';
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                }
            }
        }
        
        // 方法3: 如果仍然没有相关文章，显示热门文章
        if (!$hasRelated) {
            echo '<div class="no-related">';
            echo '<h4>' . dygita_t('热门文章') . '</h4>';
            echo '<ul class="hot-posts">';
            getHotPosts(6);
            echo '</ul>';
            echo '</div>';
        }
        ?>
    </div>
</div>
