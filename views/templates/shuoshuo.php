<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php
$pageSize = 10;
$page = max(1, (int) $this->request->get('page', 1));
$db = Typecho\Db::get();
$contentsTable = dygita_get_table('contents');
$usersTable = dygita_get_table('users');
$options = Typecho\Widget::widget('Widget_Options');

$total = $db->fetchObject($db->select(array('COUNT(*)' => 'num'))->from($contentsTable)
    ->where('type = ?', 'post')
    ->where('status = ?', 'publish'))->num;
$totalPages = $total ? max(1, (int) ceil($total / $pageSize)) : 1;
$page = min($page, $totalPages);

$rows = $db->fetchAll($db->select()->from($contentsTable)
    ->join($usersTable, $contentsTable . '.authorId = ' . $usersTable . '.uid')
    ->where($contentsTable . '.type = ?', 'post')
    ->where($contentsTable . '.status = ?', 'publish')
    ->order($contentsTable . '.created', Typecho\Db::SORT_DESC)
    ->page($page, $pageSize));
?>

<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><?php $this->title(); ?></h1>
        </header>

        <div class="shuoshuo">
            <ul class="archives-monthlisting">
                <?php
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $permalink = Typecho\Router::url('post', $row, $options->index);
                        $avatarUrl = Typecho\Common::gravatarUrl(isset($row['mail']) ? $row['mail'] : '', 64, '', '', true);
                        $authorName = isset($row['screenName']) ? htmlspecialchars($row['screenName'], ENT_QUOTES, 'UTF-8') : '';
                        $dateStr = date('Y年n月j日G:i', $row['created']);
                        $cid = (int) $row['cid'];
                        $content = isset($row['text']) ? $row['text'] : '';
                        ?>
                        <li>
                            <span class="tt"><?php echo $dateStr; ?></span>
                            <div id="shuo-<?php echo $cid; ?>" class="shuoshuo-content">
                                <?php echo $content; ?>
                                <br />
                                <div class="shuoshuo-meta">
                                    <span class="shuoshuo-sjsj"><?php echo $dateStr; ?></span>
                                    <span>— <i class="fa fa-user"></i> <?php echo $authorName; ?></span>
                                </div>
                            </div>
                            <span class="zhutou">
                                <img src="<?php echo htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo $authorName; ?>" width="64" height="64" />
                            </span>
                        </li>
                        <?php
                    }
                } else {
                    echo '<li>暂无说说</li>';
                }
                ?>
            </ul>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="page-navigator">
            <?php
            $baseUrl = $this->request->makeUriByRequest('');
            $baseUrl = rtrim(preg_replace('/[?&]page=\d+/', '', $baseUrl), '?&');
            $sep = strpos($baseUrl, '?') !== false ? '&' : '?';
            if ($page > 1) {
                $prevUrl = $page == 2 ? $baseUrl : $baseUrl . $sep . 'page=' . ($page - 1);
                echo '<a href="' . htmlspecialchars($prevUrl, ENT_QUOTES, 'UTF-8') . '">上一页</a> ';
            }
            echo '<span>' . $page . ' / ' . $totalPages . '</span>';
            if ($page < $totalPages) {
                $nextUrl = $baseUrl . $sep . 'page=' . ($page + 1);
                echo ' <a href="' . htmlspecialchars($nextUrl, ENT_QUOTES, 'UTF-8') . '">下一页</a>';
            }
            ?>
        </div>
        <?php endif; ?>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
