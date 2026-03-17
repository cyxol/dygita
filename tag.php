<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><?php $this->title(); ?></h1>
        </header>

        <style type="text/css">
            .tag-clouds a {
                width: 44%;
                opacity: .70;
                filter: alpha(opacity=80);
                color: #fff;
                display: inline-block;
                margin: 0 5px 5px 0;
                padding: 2px 6px;
                line-height: 180%;
                font-weight: bold;
            }

            .tag-clouds a:hover {
                opacity: 1;
                filter: alpha(opacity=100)
            }
        </style>

        <ul class="tag-clouds">
            <?php
            // 使用 Typecho 的 Widget 获取所有标签
            $tags = Typecho\Widget::widget('Widget\Metas\Tag\Cloud', array(
                'sort' => 'count',
                'ignoreZeroCount' => true,
                'desc' => true
            ));

            if ($tags->have()) {
                while ($tags->next()) {
                    $randColor = rand(1, 14);
                    $pl = htmlspecialchars($tags->permalink, ENT_QUOTES, 'UTF-8');
                    $name = htmlspecialchars($tags->name, ENT_QUOTES, 'UTF-8');
                    $count = (int) $tags->count;
                    echo '<li><a class="btn btn-primary sitecolor_' . $randColor . '" href="' . $pl . '">' . $name . '</a><strong>x ' . $count . '</strong><br>';
                    echo '</li>';
                }
            } else {
                echo '<li>暂无标签</li>';
            }
            ?>
        </ul>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

