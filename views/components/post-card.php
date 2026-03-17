<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
/** @var \Widget\Archive $this */
$isAuthorArchive = $this->is('author');
?>

<article class="excerpt excerpt-one">
    <header>
        <h2>
            <a target="_blank" href="<?php $this->permalink(); ?>" title="<?php $this->title(); ?>">
                <?php $this->title(); ?>
            </a>
        </h2>
    </header>

    <div class="focus">
        <a target="_blank" href="<?php $this->permalink(); ?>">
            <img class="thumb" src="<?php echo getThumbnail($this); ?>" alt="<?php $this->title(); ?>" loading="lazy" />
        </a>
    </div>

    <span class="note">
        <?php $this->excerpt(140, '...'); ?>
    </span>

    <p class="auth-span">
        <?php if (!$isAuthorArchive): ?>
            <span class="muted">
                <i class="fa fa-user"></i>
                <a href="<?php $this->author->permalink(); ?>"><?php $this->author(); ?></a>
            </span>
        <?php endif; ?>
        <span class="muted">
            <i class="fa fa-clock-o"></i> <?php $this->date('Y-m-d'); ?>
        </span>
        <span class="muted">
            <i class="fa fa-eye"></i> <?php dygita_e('浏览'); ?>(<?php getPostView($this); ?>)
        </span>
        <span class="muted">
            <i class="fa fa-comments-o"></i>
            <a target="_blank" href="<?php $this->permalink(); ?>#comments">
                <?php $this->commentsNum(dygita_t('0评论'), dygita_t('1评论'), dygita_t('%d评论')); ?>
            </a>
        </span>
        <span class="muted">
            <a href="#" data-action="ding" data-id="<?php $this->cid(); ?>" class="Addlike action" role="button">
                <i class="fa fa-heart-o"></i>
                <span class="count"><?php echo agreeNum($this->cid); ?></span>
                <?php dygita_e('喜欢'); ?>
            </a>
        </span>
    </p>
</article>

