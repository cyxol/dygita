<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
if (function_exists('dygita_content_class')) {
    dygita_content_class('index posts-expand');
} else {
    $GLOBALS['dygita_content_class'] = 'index posts-expand';
}
?>
<?php $this->need('views/components/header.php'); ?>
<?php $this->need('views/components/sidebar-left.php'); ?>

        <header class="article-header">
            <h1 class="article-title"><?php echo htmlspecialchars((string) _t('关于我'), ENT_QUOTES, 'UTF-8'); ?></h1>
        </header>

        <article class="article-content author-about-flat">
                <div class="author-about-inner">
                    <div class="author-profile">
                        <div class="author-avatar">
                                <img src="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>" alt="Yacine Tsai">
                        </div>
                        <h2 class="author-name">Yacine Tsai</h2>
                    </div>
                    <div class="author-lines">
                        <p>大数据AI产品经理</p>
                        <p>来自河南，现居南京，就职奥派</p>
                        <p>爱生活、爱音乐、爱打羽毛球</p>
                        <p>爱爬山、爱台球、爱世间万物</p>
                        <p>热爱可抵岁月漫长</p>
                        <p>乘风破浪奔赴山海</p>
                        <p>俯首高调细心做事</p>
                        <p>昂首低调宽心做人</p>
                        <p>心怀猛虎 细嗅蔷薇</p>
                        </div>
                </div>
        </article>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

