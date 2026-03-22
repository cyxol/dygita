<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
// 检查投稿功能是否开启
if (empty(dygita_opt($this->options, 'dygita_tougao_b', 'git_tougao_b')) || dygita_opt($this->options, 'dygita_tougao_b', 'git_tougao_b') != '1') {
    header('Location: ' . $this->options->siteUrl);
    exit;
}

$csrfToken = Typecho\Cookie::get('dygita_tougao_token');
if (empty($csrfToken)) {
    $csrfToken = md5(uniqid((string) mt_rand(), true));
    Typecho\Cookie::set('dygita_tougao_token', $csrfToken);
}

// 处理表单提交
if (isset($_POST['tougao_form']) && $_POST['tougao_form'] == 'send') {
    $postedToken = isset($_POST['tougao_token']) ? (string) $_POST['tougao_token'] : '';
    $cookieToken = (string) Typecho\Cookie::get('dygita_tougao_token');
    $tokenValid = function_exists('hash_equals') ? hash_equals($cookieToken, $postedToken) : ($cookieToken === $postedToken);
    if (empty($cookieToken) || empty($postedToken) || !$tokenValid) {
        throw new \Typecho\Widget\Exception(_t('请求已过期，请刷新页面后重试。'), 403);
    }
    $current_url = $this->permalink;
    
    // 表单变量初始化（保存原始数据，输出时转义）
    $name = isset($_POST['tougao_authorname']) ? trim((string) $_POST['tougao_authorname']) : '';
    $title = isset($_POST['tougao_title']) ? trim((string) $_POST['tougao_title']) : '';
    $content = isset($_POST['tougao_content']) ? trim((string) $_POST['tougao_content']) : '';
    $tomail = dygita_opt($this->options, 'dygita_tougao_mailto', 'git_tougao_mailto');
    
    // 过滤 HTML 标签防止 XSS
    $name = strip_tags($name);
    $title = strip_tags($title);
    $content = strip_tags($content, '<p><br><strong><em><ul><ol><li><blockquote><code><pre><h2><h3><h4>');
    
    // 表单项数据验证
    if (empty($name) || mb_strlen($name) > 20) {
        throw new \Typecho\Widget\Exception(_t('昵称必须填写，且长度不得超过20字。'), 200);
    }
    if (empty($title) || mb_strlen($title) > 100) {
        throw new \Typecho\Widget\Exception(_t('标题必须填写，且长度不得超过100字。'), 200);
    }
    if (empty($content) || mb_strlen($content) > 3000 || mb_strlen($content) < 100) {
        throw new \Typecho\Widget\Exception(_t('内容必须填写，且长度不得超过3000字，不得少于100字。'), 200);
    }
    
    $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $post_content = $content . '<br />感谢来自:' . $safeName . '的投稿';
    
    // 创建新文章
    $db = Typecho\Db::get();
    $insertId = $db->query($db->insert('table.contents')
        ->rows(array(
            'title'         => $title,
            'text'          => $post_content,
            'created'       => time(),
            'modified'      => time(),
            'authorId'      => 1, // 默认作者ID
            'status'        => 'draft', // 默认为草稿状态
            'type'          => 'post',
            'password'      => '',
            'slug'          => '',
            'parent'        => 0,
            'order'         => 0,
            'template'      => '',
            'commentsNum'   => 0,
            'allowComment'  => 1,
            'allowPing'     => 1,
            'allowFeed'     => 1,
            'keywords'      => ''
        )));
    
    if ($insertId) {
        // 投稿成功给博主发送邮件（需要服务器支持邮件功能）
        if ($tomail) {
            mail($tomail, "=?UTF-8?B?" . base64_encode(_t('站长，有新投稿！')) . "?=", $title . "\n\n" . $post_content, "Content-Type: text/plain; charset=UTF-8\r\n");
        }
        throw new \Typecho\Widget\Exception(_t('投稿成功！感谢投稿！<a href="' . $current_url . '">点此返回</a>'), 200);
    } else {
        throw new \Typecho\Widget\Exception(_t('投稿失败！<a href="' . $current_url . '">点此返回</a>'), 200);
    }
}
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>
        <header class="archive-header">
            <h1><a href="<?php $this->permalink(); ?>"><?php $this->title(); ?></a></h1>
        </header>

        <div class="article-content">
            <?php $this->content(); ?>
            
            <form class="googlo-tougao" method="post" action="<?php $this->permalink(); ?>">
                <div class="field-row">
                    <p><label for="tougao_authorname">昵称:*</label></p>
                    <input type="text" size="80" value="" id="tougao_authorname" name="tougao_authorname" />
                </div>
                <div class="field-row">
                    <p><label for="tougao_title">标题:*</label></p>
                    <input type="text" size="80" value="" id="tougao_title" name="tougao_title" />
                </div>
                <div class="field-row">
                    <p><label for="tougao_content">内容:*</label></p>
                    <textarea name="tougao_content" id="tougao_content" rows="12" cols=""></textarea>
                </div>
                <div class="actions-row">
                    <input type="hidden" value="send" name="tougao_form" />
                    <input type="hidden" name="tougao_token" value="<?php echo htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8'); ?>" />
                    <input class="button" type="submit" value="提交" /> &nbsp;&nbsp; 
                    <input class="buttn" type="reset" value="重填" />
                </div>
            </form>
            
            <br />
            <?php $this->need('views/components/comments.php'); ?>
<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>
