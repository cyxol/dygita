<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>

<?php
$pageSize = 20;
$page = max(1, (int) $this->request->get('page', 1));
$db = Typecho\Db::get();
$contentsTable = dygita_get_table('contents');
$fieldsTable = dygita_get_table('fields');
$options = Typecho\Widget::widget('Widget_Options');

$total = $db->fetchObject($db->select(array('COUNT(*)' => 'num'))->from($contentsTable)
    ->where('type = ?', 'post')
    ->where('status = ?', 'publish'))->num;
$totalPages = $total ? max(1, (int) ceil($total / $pageSize)) : 1;
$page = min($page, $totalPages);

$rows = $db->fetchAll($db->select()->from($contentsTable)
    ->where('type = ?', 'post')
    ->where('status = ?', 'publish')
    ->order('created', Typecho\Db::SORT_DESC)
    ->page($page, $pageSize));

$cids = array_column($rows, 'cid');
$fieldMap = array();
if (!empty($cids)) {
    $fieldRows = $db->fetchAll($db->select()->from($fieldsTable)
        ->where('cid IN ?', $cids)
        ->where('name IN ?', array('thumb', 'git_product_cpjianjie', 'git_product_jiage')));
    foreach ($fieldRows as $fr) {
        $cid = $fr['cid'];
        if (!isset($fieldMap[$cid])) $fieldMap[$cid] = array();
        $fieldMap[$cid][$fr['name']] = $fr['str_value'];
    }
}
?>
<?php $this->need('views/components/layout-start.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div id="cardslist" class="cardlist" role="main">
            <?php
            if (!empty($rows)) {
                foreach ($rows as $row) {
                    $cid = (int) $row['cid'];
                    $permalink = Typecho\Router::url('post', $row, $options->index);
                    $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                    $productIntro = isset($fieldMap[$cid]['git_product_cpjianjie']) ? htmlspecialchars($fieldMap[$cid]['git_product_cpjianjie'], ENT_QUOTES, 'UTF-8') : '';
                    $productPrice = isset($fieldMap[$cid]['git_product_jiage']) ? htmlspecialchars($fieldMap[$cid]['git_product_jiage'], ENT_QUOTES, 'UTF-8') : '';

                    if (!empty($fieldMap[$cid]['thumb'])) {
                        $thumbnail = $fieldMap[$cid]['thumb'];
                    } elseif (preg_match('/<img.+?src=["\']([^"\']+)["\']/', $row['text'], $m)) {
                        $thumbnail = $m[1];
                    } else {
                        $thumbnail = getRandomPlaceholderImageUrl($options);
                    }
                    if ($thumbnail && !preg_match('/^https?:\/\//i', $thumbnail)) {
                        $thumbnail = rtrim($options->siteUrl, '/') . '/' . ltrim($thumbnail, '/');
                    }
                    $thumbnail = htmlspecialchars($thumbnail, ENT_QUOTES, 'UTF-8');
                    ?>
                    <div class="col span_1_of_4" role="main">
                        <div class="card-item">
                            <a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>" class="fancyimg home-blog-entry-thumb">
                                <div class="thumb-img focus">
                                    <img class="thumb" src="<?php echo $thumbnail; ?>" alt="<?php echo $title; ?>" />
                                </div>
                            </a>
                            <h3><a href="<?php echo $permalink; ?>" title="<?php echo $title; ?>" target="_blank"><?php echo $title; ?></a></h3>
                            <p><?php echo $productIntro; ?></p>
                            <div class="cardpricebtn">
                                <i class="fa fa-jpy"></i> <?php echo $productPrice; ?>
                                <a class="cardbuy" href="<?php echo $permalink; ?>"><i class="fa fa-shopping-cart"></i> 立刻购买</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<p>暂无产品</p>';
            }
            ?>
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
<?php $this->need('views/components/layout-end.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
