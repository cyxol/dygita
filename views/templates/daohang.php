<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<link rel="stylesheet" href="<?php $this->options->themeUrl('css/links.css'); ?>">
<?php $this->need('views/components/sidebar-left.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <article class="post-block">
            <div class="post-body">
                <?php $this->content(); ?>
            </div>
        </article>

        <div class="mgr-10 of-hide cate-content">
            <?php
            $links = dygita_parse_links($this->options->links);

            if (!empty($links)) {
                echo '<ul class="clearfix">';
                foreach ($links as $link) {
                    $safeName = htmlspecialchars($link['name'], ENT_QUOTES, 'UTF-8');
                    $safeUrl = htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8');
                    $safeDesc = htmlspecialchars($link['description'], ENT_QUOTES, 'UTF-8');
                    $safeNotes = htmlspecialchars($link['notes'], ENT_QUOTES, 'UTF-8');
                    $ico = 'https://api.byi.pw/favicon/?url=' . urlencode($link['url']);
                    $randColor = rand(1, 14);
                    echo '<li class="col-md-4 mt-15 mb-15 pd-10">
                    <div class="pd-0 h-100 borderr-main-4 tra">
                        <div class="clearfix pd-20 bg-lvs' . $randColor . ' link-1">
                            <div class="col-md-12 pd-0 of-hide">
                                <strong><a title="' . $safeDesc . '" href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer" class="w-100 f14 color-fff link-name">' . $safeName . '</a></strong>
                                    <p class="f12 color-fff text-overflow">' . $safeUrl . '</p>
                            </div>
                        </div>
                        <div class="pd-20 pt-10 pb-10 color-primary clearfix link-2">
                        <p class="color-aaa text-overflow">' . $safeDesc . '</p>
                        </div>
                        <div class="pd-20 pt-10 pb-20 color-primary clearfix link-3 ">
                            <span class="pull-left color-aaa link_notes"><i class="fa fa-pencil-square-o" ></i>  ' . $safeNotes . '</span>
                                <span class="pull-right"><a title="' . $safeDesc . '" href="' . $safeUrl . '" target="_blank" rel="noopener noreferrer" class="f14 color-aaa"><img class="favicon avatar" src="' . $ico . '" alt="' . $safeName . '"></a></span>
                        </div>
                    <div class="clearfix"></div>
                    </div>
                    </li>';
                }
                echo '</ul>';
            } else {
                echo '<p>' . _t('暂无导航链接，请在主题设置中添加。') . '</p>';
            }
            ?>
        </div>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
