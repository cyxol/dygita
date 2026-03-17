<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div id="respond" class="no_webshot">
    <?php 
    $commentSystem = $this->options->commentSystem ? $this->options->commentSystem : 'default';
    
    // 根据配置显示不同的评论系统
    if ($commentSystem === 'gitalk') {
        // Gitalk 评论系统（出于安全原因，不再在前端暴露 clientSecret）
        if (!empty($this->options->gitalkClientID) && !empty($this->options->gitalkRepo) && !empty($this->options->gitalkOwner)) {
    ?>
        <div id="gitalk-container"></div>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gitalk@1/dist/gitalk.css">
        <script src="https://cdn.jsdelivr.net/npm/gitalk@1/dist/gitalk.min.js"></script>
        <!--
            安全提示：Gitalk 需要在前端暴露 clientSecret，这是 GitHub OAuth App 的已知限制。
            建议：1) 使用 GitHub OAuth App（非 GitHub App），其 clientSecret 泄露风险较低
                  2) 或改用 Valine/默认评论系统以避免此问题
        -->
        <script>
        var gitalk = new Gitalk({
            clientID: <?php echo json_encode($this->options->gitalkClientID, JSON_HEX_TAG); ?>,
            // 出于安全考虑，建议仅在服务端使用 clientSecret，不再在前端输出
            repo: <?php echo json_encode($this->options->gitalkRepo, JSON_HEX_TAG); ?>,
            owner: <?php echo json_encode($this->options->gitalkOwner, JSON_HEX_TAG); ?>,
            admin: [<?php echo json_encode($this->options->gitalkOwner, JSON_HEX_TAG); ?>],
            id: location.pathname,
            distractionFreeMode: false
        });
        gitalk.render('gitalk-container');
        </script>
    <?php 
        } else {
            echo '<h3>Gitalk 配置不完整，请在后台填写相关配置</h3>';
        }
    } elseif ($commentSystem === 'valine') {
        // Valine 评论系统
        if (!empty($this->options->valineAppId) && !empty($this->options->valineAppKey)) {
    ?>
        <div id="vcomment"></div>
        <script src="https://cdn.jsdelivr.net/npm/valine@1/dist/Valine.min.js"></script>
        <script>
        new Valine({
            el: '#vcomment',
            appId: <?php echo json_encode($this->options->valineAppId, JSON_HEX_TAG | JSON_HEX_APOS); ?>,
            appKey: <?php echo json_encode($this->options->valineAppKey, JSON_HEX_TAG | JSON_HEX_APOS); ?>,
            avatar: <?php echo json_encode($this->options->valineAvatar, JSON_HEX_TAG | JSON_HEX_APOS); ?>,
            placeholder: '请输入您的评论...',
            visitor: true
        });
        </script>
    <?php 
        } else {
            echo '<h3>Valine 配置不完整，请在后台填写相关配置</h3>';
        }
    } elseif ($commentSystem === 'disqus') {
        // Disqus 评论系统
        if (!empty($this->options->disqusShortname)) {
    ?>
        <div id="disqus_thread"></div>
        <script>
        var disqus_config = function () {
            this.page.url = '<?php $this->permalink(); ?>';
            this.page.identifier = '<?php $this->cid(); ?>';
        };
        (function() {
            var d = document, s = d.createElement('script');
            s.src = 'https://<?php $this->options->disqusShortname(); ?>.disqus.com/embed.js';
            s.setAttribute('data-timestamp', +new Date());
            (d.head || d.body).appendChild(s);
        })();
        </script>
    <?php 
        } else {
            echo '<h3>Disqus 配置不完整，请在后台填写相关配置</h3>';
        }
    } else {
        // 默认评论系统
        $this->comments()->to($comments);
        
        if($this->allow('comment')): 
    ?>
        <div id="<?php $this->respondId(); ?>" class="respond">
            <div class="cancel-comment-reply">
            <?php $comments->cancelReply(); ?>
            </div>
        
            <h3 id="response"><?php dygita_e('添加新评论'); ?></h3>
            <form method="post" action="<?php $this->commentUrl() ?>" id="comment-form" role="form">
                <?php if($this->user->hasLogin()): ?>
                <p><?php dygita_e('登录身份: '); ?><a href="<?php $this->options->profileUrl(); ?>"><?php $this->user->screenName(); ?></a>. <a href="<?php $this->options->logoutUrl(); ?>" title="Logout"><?php dygita_e('退出'); ?> &raquo;</a></p>
                <?php else: ?>
                <p>
                    <label for="author-<?php $this->respondId(); ?>" class="required"><?php dygita_e('称呼'); ?></label>
                    <input type="text" name="author" id="author-<?php $this->respondId(); ?>" class="text" value="<?php $this->remember('author'); ?>" required />
                </p>
                <p>
                    <label for="mail-<?php $this->respondId(); ?>" class="required"><?php dygita_e('Email'); ?></label>
                    <input type="email" name="mail" id="mail-<?php $this->respondId(); ?>" class="text" value="<?php $this->remember('mail'); ?>" <?php if ($this->options->commentsRequireMail): ?> required<?php endif; ?> />
                </p>
                <p>
                    <label for="url-<?php $this->respondId(); ?>"><?php dygita_e('网站'); ?></label>
                    <input type="url" name="url" id="url-<?php $this->respondId(); ?>" class="text" placeholder="<?php dygita_e('http://'); ?>" value="<?php $this->remember('url'); ?>" <?php if ($this->options->commentsRequireURL): ?> required<?php endif; ?> />
                </p>
                <?php endif; ?>
                <p>
                    <textarea rows="8" cols="50" name="text" id="textarea-<?php $this->respondId(); ?>" class="textarea input-block-level comt-area" required><?php $this->remember('text'); ?></textarea>
                </p>
                <p>
                    <button type="submit" class="submit btn btn-primary"><?php dygita_e('提交评论'); ?></button>
                </p>
            </form>
        </div>
        <?php else: ?>
        <h3><?php dygita_e('评论已关闭'); ?></h3>
        <?php endif; ?>

        <?php if ($comments->have()): ?>
        <h3 class="comments-count-title"><?php $this->commentsNum(dygita_t('暂无评论'), dygita_t('仅有一条评论'), dygita_t('已有 %d 条评论')); ?></h3>
        
        <?php $comments->listComments(); ?>

        <?php $comments->pageNav('&laquo; 前一页', '后一页 &raquo;'); ?>
        
        <?php endif; ?>
    <?php }
?>
</div>
