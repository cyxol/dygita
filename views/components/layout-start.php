<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div class="main-container">
<?php $this->need('views/components/sidebar-left.php'); ?>

<div class="content-wrap" role="main">
    <div class="content<?php echo isset($GLOBALS['dygita_content_class']) ? ' ' . htmlspecialchars($GLOBALS['dygita_content_class'], ENT_QUOTES, 'UTF-8') : ''; ?>">
