<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

                <header class="archive-header">
                        <h1><?php echo htmlspecialchars((string) dygita_t('文章列表'), ENT_QUOTES, 'UTF-8'); ?></h1>
                </header>

                <article class="archives">
                        <?php
                        $posts = dygita_get_archive_posts();

                        if (!empty($posts)) {
                                $currentYear = '';
                                $ulOpen = false;
                                $options = Typecho\Widget::widget('Widget_Options');

                                foreach ($posts as $post) {
                                        $year = date('Y', $post['created']);

                                        if ($year !== $currentYear) {
                                                if ($ulOpen) {
                                                        echo '</ul></div>';
                                                }

                                                echo '<div class="xControl">';
                                                echo '<a href="javascript:void(0)" class="collapseButton xButton" role="button">';
                                                echo '<div class="item"><h3>' . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . '</h3></div>';
                                                echo '</a>';
                                                echo '<ul class="archives-list" style="display:block;">';

                                                $ulOpen = true;
                                                $currentYear = $year;
                                        }

                                        $permalink = Typecho\Router::url('post', $post, $options->index);
                                        $title = htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8');

                                        echo '<li><a href="' . $permalink . '">' . $title . '</a></li>';
                                }

                                if ($ulOpen) {
                                        echo '</ul></div>';
                                }
                        } else {
                                echo '<p>' . htmlspecialchars((string) dygita_t('暂无文章'), ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        ?>
                </article>

                <script defer src="<?php $this->options->themeUrl('js/archives-page.js'); ?>"></script>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

