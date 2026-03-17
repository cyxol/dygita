<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
            
            <style type="text/css">.apollo_1 .sitename{padding-left:25px;}</style>
            
            <?php
            // 模拟网址导航功能 - 实际使用时可以通过插件或主题选项添加
            // 这里使用与 links.php 相同的模拟数据
            $links = array(
                array(
                    'name' => 'Typecho官网',
                    'url' => 'https://typecho.org',
                    'description' => '轻量级博客系统',
                    'notes' => '官方网站',
                    'image' => ''
                ),
                array(
                    'name' => 'GitHub',
                    'url' => 'https://github.com',
                    'description' => '代码托管平台',
                    'notes' => '开源社区',
                    'image' => ''
                ),
                array(
                    'name' => 'Google',
                    'url' => 'https://google.com',
                    'description' => '搜索引擎',
                    'notes' => '全球最大',
                    'image' => ''
                )
            );

            if (!empty($links)) {
                echo '<div class="apollo_1">';
                foreach ($links as $link) {
                    $ico = $link['image'] ? $link['image'] : 'https://api.byi.pw/favicon/?url=' . $link['url'] . '';
                    echo '<div class="sitename"><a href="' . $link['url'] . '" target="_blank" title="' . $link['description'] . '">' . $link['name'] . '</a></div>';
                }
                echo '</div>';
            }
            ?>
        </div>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
