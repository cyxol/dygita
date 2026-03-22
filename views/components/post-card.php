<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>

<?php
/** @var \Widget\Archive $this */
$isAuthorArchive = $this->is('author');
$showCategoryCorner = $this->is('index');
$postCategoryLabel = '';
if ($showCategoryCorner) {
    ob_start();
    $this->category(',');
    $postCategoryLinks = trim((string) ob_get_clean());
    if ($postCategoryLinks !== '' && preg_match('/<a\b[^>]*>.*?<\/a>/i', $postCategoryLinks, $postCategoryMatch)) {
        $postCategoryLabel = $postCategoryMatch[0];
    }
}
?>

<article class="excerpt excerpt-one">
    <header>
        <?php if ($postCategoryLabel !== ''): ?>
            <span class="label">
                <?php echo $postCategoryLabel; ?>
            </span>
        <?php endif; ?>
        <h2>
            <a target="_blank" rel="noopener noreferrer" href="<?php $this->permalink(); ?>" title="<?php $this->title(); ?>">
                <?php $this->title(); ?>
            </a>
        </h2>
    </header>

    <div class="focus">
        <a target="_blank" rel="noopener noreferrer" href="<?php $this->permalink(); ?>">
            <img class="thumb" src="<?php echo dygita_get_thumbnail($this); ?>" alt="<?php $this->title(); ?>" loading="lazy" />
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
            <i class="fa fa-eye"></i> <?php _e('浏览'); ?>(<?php echo dygita_get_post_view($this); ?>)
        </span>
        <span class="muted">
            <i class="fa fa-comments-o"></i>
            <a target="_blank" rel="noopener noreferrer" href="<?php $this->permalink(); ?>#comments">
                <?php $this->commentsNum(_t('0评论'), _t('1评论'), _t('%d评论')); ?>
            </a>
        </span>
        <span class="muted">
            <a href="#" data-action="ding" data-id="<?php $this->cid(); ?>" class="Addlike action" role="button">
                <i class="fa fa-heart-o"></i>
                <span class="count"><?php echo dygita_agree_num($this->cid); ?></span>
                <?php _e('喜欢'); ?>
            </a>
        </span>
    </p>
</article>

