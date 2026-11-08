<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$GLOBALS['dygita_content_class'] = 'posts-expand'; ?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

                <header class="article-header">
                        <h1 class="article-title"><?php echo htmlspecialchars((string) _t('文章列表'), ENT_QUOTES, 'UTF-8'); ?></h1>
                </header>

                <article class="archives-timeline">
                        <?php
                        $archivePage = max(1, (int) $this->request->get('archive_page', 1));
                        $archiveTotal = Dygita_Repository::countPublic();
                        $archivePages = max(1, (int) ceil($archiveTotal / 50));
                        $archivePage = min($archivePage, $archivePages);
                        $posts = dygita_get_archive_posts($archivePage, 50);

                        if (!empty($posts)) {
                                $currentYear = '';
                                $options = Typecho\Widget::widget('Widget_Options');

                                foreach ($posts as $post) {
                                        $year = date('Y', $post['created']);

                                        if ($year !== $currentYear) {
                                                if ($currentYear !== '') echo '</ul>';
                                                echo '<h2 class="archives-year">' . htmlspecialchars($year, ENT_QUOTES, 'UTF-8') . '</h2>';
                                                echo '<ul class="archives-posts">';
                                                $currentYear = $year;
                                        }

                                        $monthDay = date('m-d', $post['created']);
                                        $permalink = Typecho\Router::url('post', $post, $options->index);
                                        $title = htmlspecialchars((string) $post['title'], ENT_QUOTES, 'UTF-8');

                                        echo '<li>';
                                        echo '<span class="archives-date">' . $monthDay . '</span>';
                                        echo '<a href="' . $permalink . '" class="archives-title">' . $title . '</a>';
                                        echo '</li>';
                                }

                                echo '</ul>';
                        } else {
                                echo '<p>' . htmlspecialchars((string) _t('暂无文章'), ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        ?>
                </article>
                <nav class="pagination" aria-label="文章列表分页">
                <?php if ($archivePage > 1): ?><a href="?archive_page=<?php echo $archivePage - 1; ?>">上一页</a><?php endif; ?>
                <span><?php echo $archivePage . ' / ' . $archivePages; ?></span>
                <?php if ($archivePage < $archivePages): ?><a href="?archive_page=<?php echo $archivePage + 1; ?>">下一页</a><?php endif; ?>
                </nav>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

