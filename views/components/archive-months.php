<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<article class="archives">
    <?php
    $posts = dygita_get_archive_posts();

    if (!empty($posts)) {
        $currentYear = '';
        $currentMonth = '';
        $ulOpen = false;

        foreach ($posts as $post) {
            $year = date('Y', $post['created']);
            $month = date('m', $post['created']);
            $monthName = $year . '年' . $month . '月';

            if ($year != $currentYear || $month != $currentMonth) {
                if ($ulOpen) {
                    echo '</ul></div>';
                }
                $safeMonthName = htmlspecialchars($monthName, ENT_QUOTES, 'UTF-8');
                echo '<div class="xControl">';
                echo '<a href="javascript:void(0)" class="collapseButton xButton" role="button">';
                echo '<div class="item"><h3>' . $safeMonthName . '</h3></div>';
                echo '</a>';
                echo '<ul class="archives-list">';
                $ulOpen = true;
                $currentYear = $year;
                $currentMonth = $month;
            }

            $day = date('j', $post['created']);
            $permalink = Typecho\Router::url('post', $post, Typecho\Widget::widget('Widget_Options')->index);
            $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');

            echo '<li>';
            echo '<time>' . $day . '日</time> ';
            echo '<a href="' . $permalink . '">' . $title . '</a>';
            echo '</li>';
        }

        if ($ulOpen) {
            echo '</ul></div>';
        }
    } else {
        echo '<p>' . dygita_t('暂无文章') . '</p>';
    }
    ?>
</article>

<script defer src="<?php $this->options->themeUrl('js/archives-page.js'); ?>"></script>

