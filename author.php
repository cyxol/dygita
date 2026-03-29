<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<?php
$GLOBALS['dygita_content_class'] = 'index posts-expand';
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
                                <?php $authorPageName = htmlspecialchars(dygita_get_author_name($this->options), ENT_QUOTES, 'UTF-8'); ?>
                                <img src="<?php echo dygita_get_author_avatar($this->options); ?>" alt="<?php echo $authorPageName; ?>">
                        </div>
                        <h2 class="author-name"><?php echo $authorPageName; ?></h2>
                    </div>
                    <div class="author-lines">
                        <?php
                        $authorBioText = isset($this->options->authorBio) ? trim((string) $this->options->authorBio) : '';
                        if ($authorBioText !== '') {
                            foreach (preg_split('/\r?\n/', $authorBioText) as $bioLine) {
                                $bioLine = trim($bioLine);
                                if ($bioLine !== '') {
                                    echo '<p>' . htmlspecialchars($bioLine, ENT_QUOTES, 'UTF-8') . '</p>';
                                }
                            }
                        } else {
                            echo '<p>' . htmlspecialchars((string) $this->options->description, ENT_QUOTES, 'UTF-8') . '</p>';
                        }
                        ?>
                        </div>
                </div>
        </article>

<?php $this->need('views/components/sidebar-right.php'); ?>
<?php $this->need('views/components/footer.php'); ?>

