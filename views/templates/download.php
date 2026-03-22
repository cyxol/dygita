<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php $this->need('views/components/header.php'); ?>
<?php
$pid = $this->request->get('pid');
$dlData = $pid ? dygita_get_download_data($pid) : null;
if (!$dlData) {
    header('Location: ' . $this->options->siteUrl);
    exit;
}
$post         = $dlData['post'];
$theCode1     = $dlData['name'];
$theCode2     = $dlData['size'];
$theCode3     = $dlData['links'];
$title        = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
$postModified = $post['modified'];
$permalink    = $dlData['permalink'];
?>

<?php $this->need('views/components/sidebar-left.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php echo htmlspecialchars($permalink, ENT_QUOTES, 'UTF-8'); ?>"><?php echo $title; ?></a></h1>
        </header>

        <div class="article-content">
            <?php $ad1 = dygita_opt($this->options, 'dygita_downloadad1', 'git_downloadad1'); if ($ad1) echo $ad1; ?>
            
            <h2><?php _e('资源信息'); ?></h2>
            <div class="alert alert-success">
                <ul class="infos clearfix">
                    <li><?php _e('资源名称'); ?>: <?php echo htmlspecialchars($theCode1, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><?php _e('文件大小'); ?>: <?php echo htmlspecialchars($theCode2, ENT_QUOTES, 'UTF-8'); ?></li>
                    <li><?php _e('更新日期'); ?>: <?php echo date('Y-m-d H:i:s', $postModified); ?></li>
                </ul>
            </div>
            
            <h2><?php _e('下载地址'); ?></h2>
            <div id="filelink">
                <div class="filelink-inner">
                    <?php
                    if ($theCode3) {
                        $git_download_linkss = explode("\n", $theCode3);
                        foreach ($git_download_linkss as $git_download_links) {
                            $parts = explode(",", $git_download_links);
                            if (count($parts) >= 3) {
                                $href = htmlspecialchars(trim($parts[0]), ENT_QUOTES, 'UTF-8');
                                $text = htmlspecialchars(trim($parts[1]), ENT_QUOTES, 'UTF-8');
                                $desc = htmlspecialchars(trim($parts[2]), ENT_QUOTES, 'UTF-8');
                                echo '<a href="' . $href . '" target="_blank" rel="nofollow noopener" title="' . $desc . '">' . $text . '</a>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
            
            <div class="clearfix"></div>
            
            <h2><?php _e('下载说明'); ?></h2>
            <div class="alert alert-info" role="alert">
                <?php $dl = dygita_opt($this->options, 'dygita_dlpage_dl', 'git_dlpage_dl'); if ($dl) echo $dl; ?>
            </div>
            
            <h2><?php _e('免责声明'); ?></h2>
            <div class="alert alert-warning" role="alert">
                <p><?php $mz = dygita_opt($this->options, 'dygita_dlpage_mz', 'git_dlpage_mz'); if ($mz) echo $mz; ?></p>
            </div>
            
            <?php $ad2 = dygita_opt($this->options, 'dygita_downloadad2', 'git_downloadad2'); if ($ad2) echo $ad2; ?>
        </div>

        <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
