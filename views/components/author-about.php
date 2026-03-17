<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<article class="post-block author-about">
    <div class="post-body">
        <div class="author-about-inner">
            <div class="author-profile">
                <div class="author-avatar">
                    <?php if ($this->options->logoUrl): ?>
                        <img src="<?php $this->options->logoUrl(); ?>" alt="<?php $this->author->screenName(); ?>">
                    <?php else: ?>
                        <img src="<?php $this->options->themeUrl('img/authorpic.jpg'); ?>" alt="<?php $this->author->screenName(); ?>">
                    <?php endif; ?>
                </div>
                <h2 class="author-name"><?php $this->author->screenName(); ?></h2>
            </div>

            <div class="author-bio">
                <p><?php $this->options->description(); ?></p>
            </div>

            <div class="author-contact">
                <h3><?php echo htmlspecialchars((string) dygita_t('与我联系'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="contact-list">
                    <?php
                    $contactEmail = $this->options->contactEmail ? dygita_escape($this->options->contactEmail) : '';
                    $contactQQ   = $this->options->contactQQ ? dygita_escape($this->options->contactQQ) : '';
                    if ($contactEmail): ?>
                        <p><i class="fa fa-envelope"></i> <a href="mailto:<?php echo $contactEmail; ?>"><?php echo $contactEmail; ?></a></p>
                    <?php endif;
                    if ($contactQQ): ?>
                        <p><i class="fa fa-qq"></i> <?php echo $contactQQ; ?></p>
                    <?php endif;
                    if (!$contactEmail && !$contactQQ): ?>
                        <p><?php dygita_e('请在主题设置中填写联系邮箱或QQ'); ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="author-stats">
                <h3><?php echo htmlspecialchars((string) dygita_t('更多信息'), ENT_QUOTES, 'UTF-8'); ?></h3>
                <?php $stat = getStat(); ?>
                <p>
                    <span><i class="fa fa-file-text-o"></i> <?php echo (int) $stat['posts']; ?> <?php echo htmlspecialchars((string) dygita_t('文章'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fa fa-folder-o"></i> <?php echo (int) $stat['categories']; ?> <?php echo htmlspecialchars((string) dygita_t('分类'), ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><i class="fa fa-tags"></i> <?php echo (int) $stat['tags']; ?> <?php echo htmlspecialchars((string) dygita_t('标签'), ENT_QUOTES, 'UTF-8'); ?></span>
                </p>
            </div>
        </div>
    </div>
</article>

