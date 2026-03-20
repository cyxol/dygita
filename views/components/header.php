<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
<!--
代码如诗 , 如痴如醉 !
-->
<!DOCTYPE html>
<?php
$prefs = dygita_get_saved_theme_prefs();
$savedTheme = $prefs['theme'];
$savedHeaderColor = $prefs['headerColor'];
$dygitaLangAttr = trim((string)($this->options->lang ?: '')) ?: 'zh-CN';
?>
<html lang="<?php echo htmlspecialchars($dygitaLangAttr, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($savedTheme): ?> data-theme="<?php echo htmlspecialchars((string) $savedTheme, ENT_QUOTES, 'UTF-8'); ?>" <?php
elseif ($this->options->colorSchema): ?> data-theme="<?php $this->options->colorSchema(); ?>" <?php
endif; ?>>

<head>
    <meta charset="<?php $this->options->charset(); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <meta name="theme-color" content="#222">

    <!-- 保存的主题偏好，用于 JavaScript 初始化 -->
    <script>
        window.DYGITA = window.DYGITA || {};
        window.DYGITA.savedTheme = <?php echo json_encode($savedTheme, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        window.DYGITA.savedHeaderColor = <?php echo json_encode($savedHeaderColor, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        (function() {
            var isDark = window.DYGITA.savedTheme === 'dark' || (!window.DYGITA.savedTheme && typeof window.matchMedia !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) document.documentElement.setAttribute('data-theme', 'dark');
        })();
    </script>

    <title><?php $this->archiveTitle(array(
    'category' => dygita_t('分类 %s 下的文章'),
    'search' => dygita_t('包含关键字 %s 的文章'),
    'tag' => dygita_t('标签 %s 下的文章'),
    'author' => dygita_t('%s 发布的文章')
), '', ' - '); ?><?php $this->options->title(); ?></title>

    <!-- 预加载关键资源 -->
    <link rel="preload" href="<?php $this->options->themeUrl('css/style.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="<?php $this->options->themeUrl('css/vendor/font-awesome.min.css'); ?>" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="<?php $this->options->themeUrl('css/style.css'); ?>">
        <link rel="stylesheet" href="<?php $this->options->themeUrl('css/vendor/font-awesome.min.css'); ?>">
    </noscript>

    <!-- 网站图标 -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">

    <!-- 懒加载与主题相关样式已拆分到分层 CSS 源文件（base/layout/components/themes） -->

    <!-- SEO 相关标签 -->
    <meta name="keywords" content="<?php $this->options->keywords(); ?>">
    <meta name="robots" content="index,follow">
    <meta name="GOOGLEBOT" content="index,follow">
    <meta name="author" content="<?php $this->options->title(); ?>">
    <?php if (!$this->is('post') && !$this->is('page') && !$this->is('category')): ?>
        <meta name="description" content="<?php $this->options->description(); ?>">
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?php $this->options->title(); ?>">
        <meta property="og:url" content="<?php $this->options->siteUrl(); ?>">
        <meta property="og:site_name" content="<?php $this->options->title(); ?>">
        <meta property="og:description" content="<?php $this->options->description(); ?>">
        <meta property="og:locale" content="<?php $this->options->lang(); ?>">
        <meta property="og:image" content="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="<?php $this->options->title(); ?>">
        <meta name="twitter:description" content="<?php $this->options->description(); ?>">
        <meta name="twitter:image" content="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
    <?php
elseif ($this->is('category')):
        $categoryName = '';
        $categoryDescription = '';

        if (isset($this->category)) {
            if (is_object($this->category)) {
                if (method_exists($this->category, 'name')) {
                    ob_start();
                    $this->category->name();
                    $categoryName = trim((string) ob_get_clean());
                }
                if (method_exists($this->category, 'description')) {
                    ob_start();
                    $this->category->description();
                    $categoryDescription = trim((string) ob_get_clean());
                }
            } elseif (is_string($this->category)) {
                $categoryName = trim($this->category);
            }
        }

        if ($categoryName === '') {
            ob_start();
            $this->archiveTitle(array('category' => '%s'), '', '');
            $categoryName = trim(strip_tags((string) ob_get_clean()));
        }
        if ($categoryDescription === '') {
            $categoryDescription = (string) $this->options->description;
        }
    ?>
        <meta name="description" content="<?php echo htmlspecialchars($categoryDescription, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:type" content="website">
        <meta property="og:title" content="<?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?> - <?php $this->options->title(); ?>">
        <meta property="og:url" content="<?php $this->permalink(); ?>">
        <meta property="og:site_name" content="<?php $this->options->title(); ?>">
        <meta property="og:description" content="<?php echo htmlspecialchars($categoryDescription, ENT_QUOTES, 'UTF-8'); ?>">
        <meta property="og:locale" content="<?php $this->options->lang(); ?>">
        <meta property="og:image" content="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="<?php echo htmlspecialchars($categoryName, ENT_QUOTES, 'UTF-8'); ?> - <?php $this->options->title(); ?>">
        <meta name="twitter:description" content="<?php echo htmlspecialchars($categoryDescription, ENT_QUOTES, 'UTF-8'); ?>">
        <meta name="twitter:image" content="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
    <?php
endif; ?>
    <meta name="application-name" content="<?php $this->options->title(); ?>">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="<?php $this->options->title(); ?>">
    <meta name="format-detection" content="telephone=no, email=no">
    <meta name="msapplication-TileImage" content="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>">
    <meta name="msapplication-TileColor" content="#222">

    <!-- Canonical 标签 -->
    <?php
    $dygitaCanonicalUrl = trim((string) $this->request->getRequestUrl());
    if ($dygitaCanonicalUrl === '') {
        $dygitaCanonicalUrl = (string) $this->options->siteUrl;
    }
    try {
        ob_start();
        $this->permalink();
        $tmpCanonical = trim((string) ob_get_clean());
        if ($tmpCanonical !== '') {
            $dygitaCanonicalUrl = $tmpCanonical;
        }
    } catch (Throwable $e) {
        if (ob_get_level() > 0) {
            ob_end_clean();
        }
    }
    ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($dygitaCanonicalUrl, ENT_QUOTES, 'UTF-8'); ?>">

    <!-- 站点验证 -->
    <!-- 在这里添加 Google、Bing、Sogou 等站点验证代码 -->
    <link href="https://gravatar.com/exuberant3c83335dc7" rel="me" />
    <!-- 例如：<meta name="google-site-verification" content="your-verification-code"> -->

    <!-- 移动端优化 -->
    <meta name="HandheldFriendly" content="true">
    <meta name="MobileOptimized" content="320">



    <!-- 文章页面特定的 SEO 标签 -->
    <?php if ($this->is('post') || $this->is('page')): ?>
        <meta name="description" content="<?php $this->excerpt(150); ?>">
        <meta property="og:type" content="article">
        <meta property="og:title" content="<?php $this->title(); ?>">
        <meta property="og:url" content="<?php $this->permalink(); ?>">
        <meta property="og:description" content="<?php $this->excerpt(150); ?>">
        <meta property="og:image" content="<?php echo getThumbnail($this); ?>">
        <meta property="article:published_time" content="<?php $this->date('c'); ?>">
        <meta property="article:modified_time" content="<?php $this->modified('c'); ?>">
        <meta property="article:author" content="<?php $this->author(); ?>">
        <meta property="article:section" content="<?php $this->category(',', false); ?>">
        <?php $this->widget('Widget_Metas_Tag_Cloud', 'sort=mid&ignoreZeroCount=1&limit=10')->to($headerTags); ?>
        <?php while ($headerTags->next()): ?>
            <meta property="article:tag" content="<?php $headerTags->name(); ?>">
        <?php
    endwhile; ?>
        <meta name="twitter:title" content="<?php $this->title(); ?>">
        <meta name="twitter:description" content="<?php $this->excerpt(150); ?>">
        <meta name="twitter:image" content="<?php echo getThumbnail($this); ?>">

        <!-- 文章结构化数据 -->
        <?php
        $ldTitle = $this->title;
        ob_start(); $this->excerpt(150); $ldExcerpt = ob_get_clean();
        $ldPermalink = $this->permalink;
        $ldPublisherName = $this->options->title;
        $ldPublisherLogo = $this->options->themeUrl . '/img/caiya.xin.jpg';
        $ldArticle = array(
            "@context" => "https://schema.org",
            "@type" => "Article",
            "headline" => $ldTitle,
            "datePublished" => date('c', $this->created),
            "dateModified" => date('c', $this->modified),
            "author" => array(array(
                "@type" => "Person",
                "name" => $this->author->screenName
            )),
            "publisher" => array(
                "@type" => "Organization",
                "name" => $ldPublisherName,
                "logo" => array(
                    "@type" => "ImageObject",
                    "url" => $ldPublisherLogo,
                    "width" => 60,
                    "height" => 60
                )
            ),
            "description" => strip_tags($ldExcerpt),
            "mainEntityOfPage" => $ldPermalink,
            "image" => getThumbnail($this)
        );
        ?>
        <script type="application/ld+json">
            <?php echo json_encode($ldArticle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
        </script>
    <?php
endif; ?>

    <!-- 网站结构化数据 -->
    <?php
    $ldSiteTitle = $this->options->title;
    $ldSiteUrl = $this->options->siteUrl;
    $ldSiteDesc = $this->options->description;
    $ldWebsite = array(
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => $ldSiteTitle,
        "url" => $ldSiteUrl,
        "description" => $ldSiteDesc,
        "potentialAction" => array(
            "@type" => "SearchAction",
            "target" => $ldSiteUrl . 'search/{search_term_string}',
            "query-input" => "required name=search_term_string"
        )
    );
    ?>
    <script type="application/ld+json">
        <?php echo json_encode($ldWebsite, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>

    <!-- 主题配置 -->
    <script id="dygita-configurations">
        window.DYGITA = window.DYGITA || {};
        window.DYGITA.config = <?php echo json_encode(dygita_get_config_array($this->options), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    </script>

    <!-- 通过自有函数输出HTML头部信息 -->
    <?php $this->header(); ?>

    <!-- Swiper.js 轮播图样式 -->
    <?php if ($this->options->swiperEnabled == '1'): ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css" onerror="this.onerror=null;this.href='https://unpkg.com/swiper@8/swiper-bundle.min.css'">
    <?php
endif; ?>

    <!-- 代码语法高亮 - 仅在文章/页面中加载 -->
    <?php if ($this->is('post') || $this->is('page')): ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/prismjs@1/themes/prism-tomorrow.min.css" onerror="this.onerror=null;this.href='https://unpkg.com/prismjs@1/themes/prism-tomorrow.min.css'">
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/prism.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/prism.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/toolbar/prism-toolbar.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/plugins/toolbar/prism-toolbar.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-bash.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-bash.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-json.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-json.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-python.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-python.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-javascript.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-javascript.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-css.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-css.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/components/prism-markup.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/components/prism-markup.min.js'"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/prismjs@1/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/prismjs@1/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js'"></script>
    <?php endif; ?>

    <!-- 合并后的主题样式（由分层 CSS 源文件构建生成） -->
    <link rel="stylesheet" href="<?php $this->options->themeUrl('css/build.css'); ?>">

    <!-- 动态主题色样式 -->
    <style>
        <?php echo dygita_get_theme_skin_css(dygita_opt($this->options, 'dygita_skin_b', 'git_skin_b') ?: 'git_red_b'); ?>
    </style>
</head>

<body itemscope itemtype="http://schema.org/WebPage">
    <header id="l-header" class="l-header">
        <div class="hdbg skin-bg"></div>
        <div class="m-about">
            <div id="logo">
                <a href="<?php $this->options->siteUrl(); ?>" aria-label="返回首页"><img src="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>" alt="网站logo" aria-hidden="true"></a>
            </div>
            <h1 class="tit" itemprop="headline"><a href="<?php $this->options->siteUrl(); ?>" aria-label="返回首页"><?php $this->options->title(); ?></a></h1>
            <div class="about" itemprop="description"><?php $this->options->description(); ?></div>
        </div>
        <canvas id="header-canvas"></canvas>
    </header>
    <div id="m-nav" class="m-nav">
        <div class="m-nav-all">
            <div class="m-logo-url">    
                <img src="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>" alt="头像">
                <h3><?php $this->options->title(); ?></h3>
            </div>
            <?php
            $navBaseSiteUrl = rtrim($this->options->siteUrl, '/');
            $navOpts = $this->options;
            $navIdx = $navOpts->index;
            $navArchivesUrl = htmlspecialchars($navBaseSiteUrl . '/archives/', ENT_QUOTES, 'UTF-8');
            $navCategoriesUrl = htmlspecialchars($navBaseSiteUrl . '/category/', ENT_QUOTES, 'UTF-8');
            $navTagCloudUrl = htmlspecialchars($navBaseSiteUrl . '/tag', ENT_QUOTES, 'UTF-8');
            $navPathInfo = trim((string) $this->request->getPathInfo(), '/');
            $navArchivesActive = ($navPathInfo === 'archives' || $this->is('archive') || $this->is('page', 'archives'));
            $navCategoriesActive = ($navPathInfo === 'category' || $navPathInfo === 'categories' || $this->is('page', 'category') || $this->is('page', 'categories'));
            $navHasArchivesLink = false;
            $navHasTagCloudLink = false;
            $navHasCategoriesLink = false;
            $navTagCloudSlug = null;

            $navAuthorUrl = null;
            $navAllPages = [];
            $pageListWidget = $this->widget('Widget_Contents_Page_List');
            while ($pageListWidget->next()) {
                $s = $pageListWidget->slug;
                $pTitle = $pageListWidget->title;
                $pLink = $pageListWidget->permalink;
                $navAllPages[] = ['slug' => $s, 'title' => $pTitle, 'permalink' => $pLink];
                if ($navAuthorUrl === null && in_array($s, ['about', 'author', 'author_page'])) {
                    $navAuthorUrl = $pLink;
                }
                if ($navTagCloudUrl === null && in_array($s, ['page-tag-cloud', 'tag-cloud', 'tags'], true)) {
                    $navTagCloudUrl = $pLink;
                    $navTagCloudSlug = $s;
                }
            }
            if ($navAuthorUrl === null) {
                $navAuthorUrl = Typecho\Router::url('author', ['uid' => 1], $navIdx);
            }
            $navTagCloudUrl = htmlspecialchars((string) $navTagCloudUrl, ENT_QUOTES, 'UTF-8');
            $navAuthorUrl = htmlspecialchars((string) $navAuthorUrl, ENT_QUOTES, 'UTF-8');
            $navAuthorActive = $this->is('author') || $this->is('page', 'author') || $this->is('page', 'about') || $this->is('page', 'author_page');
            $navTagCloudActive = ($navPathInfo === 'tag' || $navPathInfo === 'tags' || $navPathInfo === 'page-tag-cloud.html' || $this->is('page', 'page-tag-cloud') || $this->is('page', 'tag-cloud') || $this->is('page', 'tags') || $this->is('tag'));
            ?>
            <div class="nav-container" role="navigation" aria-label="主导航">
                <ul class="nav" role="menubar">
                    <?php if ($this->options->navLinksEnabled == '1'): ?>
                        <?php
    $navLinks = json_decode($this->options->navLinks, true);
    $navDeferredAuthorItem = '';
    $navArchivesTarget = '_self';
    $navTagCloudTarget = '_self';
    $navCategoriesTarget = '_self';
    $navRenderArchivesUrl = $navArchivesUrl;
    $navRenderTagCloudUrl = $navTagCloudUrl;
    $navRenderCategoriesUrl = $navCategoriesUrl;
    if (isset($navLinks['links']) && is_array($navLinks['links'])) {
        foreach ($navLinks['links'] as $link) {
            $nameRaw = isset($link['name']) ? trim($link['name']) : '';
            if ($nameRaw === '作者') {
                $nameRaw = '关于博主';
            }
            if ($nameRaw === '标签云') {
                $nameRaw = '文章标签';
            }
            $name = htmlspecialchars((string) dygita_t($nameRaw), ENT_QUOTES, 'UTF-8');
            $url = isset($link['url']) ? $link['url'] : '';
            $urlPath = trim((string) parse_url((string) $url, PHP_URL_PATH), '/');
            $target = isset($link['target']) && in_array($link['target'], ['_self', '_blank']) ? $link['target'] : '_self';

            if ($url === '@author') {
                $linkUrl = $navAuthorUrl;
                $isActive = $navAuthorActive;
            } else {
                $linkUrl = strpos($url, 'http') === 0 ? $url : ($url ? $navBaseSiteUrl . '/' . ltrim($url, '/') : $navBaseSiteUrl);
                $linkUrl = htmlspecialchars((string) $linkUrl, ENT_QUOTES, 'UTF-8');
                $isActive = ($url == '' && $this->is('index'));
            }

            $isAuthorLink = ($url === '@author') || ($urlPath === 'author') || ($urlPath === 'about') || in_array($nameRaw, ['作者', '关于博主'], true);
            if ($isAuthorLink) {
                if ($navDeferredAuthorItem === '') {
                    $navDeferredAuthorItem = '<li ' . ($isActive ? 'class="active"' : '') . ' role="none">'
                        . '<a href="' . $linkUrl . '" target="' . $target . '" role="menuitem" ' . ($isActive ? 'aria-current="page"' : '') . '>关于博主</a>'
                        . '</li>';
                }
                continue;
            }

            $isArchivesLink = ($urlPath === 'archives') || in_array($url, ['archives', '/archives', 'archives/', '/archives/'], true);
            if ($isArchivesLink) {
                $navHasArchivesLink = true;
                $navRenderArchivesUrl = $linkUrl;
                $navArchivesTarget = $target;
                continue;
            }

            $isTagCloudLink = in_array($urlPath, ['tag', 'tags', 'page-tag-cloud.html', 'page-tag-cloud', 'tag-cloud'], true)
                || in_array($url, ['tag', '/tag', 'tag/', '/tag/', 'tags', '/tags', 'tags/', '/tags/', 'page-tag-cloud.html', '/page-tag-cloud.html', 'tag-cloud', '/tag-cloud'], true);
            if ($isTagCloudLink) {
                $navHasTagCloudLink = true;
                $navRenderTagCloudUrl = $linkUrl;
                $navTagCloudTarget = $target;
                continue;
            }

            $isCategoriesLink = in_array($urlPath, ['category', 'categories'], true)
                || in_array($url, ['category', '/category', 'category/', '/category/', 'categories', '/categories', 'categories/', '/categories/'], true);
            if ($isCategoriesLink) {
                $navHasCategoriesLink = true;
                $navRenderCategoriesUrl = $linkUrl;
                $navCategoriesTarget = $target;
                continue;
            }

            echo '<li ' . ($isActive ? 'class="active"' : '') . ' role="none">';
            echo '<a href="' . $linkUrl . '" target="' . $target . '" role="menuitem" ' . ($isActive ? 'aria-current="page"' : '') . '>' . $name . '</a>';
            echo '</li>';
        }
    }

    echo '<li ' . ($navArchivesActive ? 'class="active"' : '') . ' role="none">';
    echo '<a href="' . ($navHasArchivesLink ? $navRenderArchivesUrl : $navArchivesUrl) . '" target="' . $navArchivesTarget . '" role="menuitem" ' . ($navArchivesActive ? 'aria-current="page"' : '') . '>文章列表</a>';
    echo '</li>';

    echo '<li ' . ($navTagCloudActive ? 'class="active"' : '') . ' role="none">';
    echo '<a href="' . ($navHasTagCloudLink ? $navRenderTagCloudUrl : $navTagCloudUrl) . '" target="' . $navTagCloudTarget . '" role="menuitem" ' . ($navTagCloudActive ? 'aria-current="page"' : '') . '>文章标签</a>';
    echo '</li>';

    echo '<li ' . ($navCategoriesActive ? 'class="active"' : '') . ' role="none">';
    echo '<a href="' . ($navHasCategoriesLink ? $navRenderCategoriesUrl : $navCategoriesUrl) . '" target="' . $navCategoriesTarget . '" role="menuitem" ' . ($navCategoriesActive ? 'aria-current="page"' : '') . '>文章分类</a>';
    echo '</li>';

    if ($navDeferredAuthorItem !== '') {
        echo $navDeferredAuthorItem;
    } else {
        echo '<li ' . ($navAuthorActive ? 'class="active"' : '') . ' role="none">';
        echo '<a href="' . $navAuthorUrl . '" role="menuitem" ' . ($navAuthorActive ? 'aria-current="page"' : '') . '>关于博主</a>';
        echo '</li>';
    }
?>
                    <?php else: ?>
                        <li <?php if ($this->is('index')): ?> class="active" <?php endif; ?> role="none">
                            <a href="<?php $this->options->siteUrl(); ?>" role="menuitem" <?php if ($this->is('index')): ?> aria-current="page" <?php endif; ?>><?php dygita_e('首页'); ?></a>
                        </li>
                        <li <?php if ($navArchivesActive): ?> class="active" <?php endif; ?> role="none">
                            <a href="<?php echo $navArchivesUrl; ?>" role="menuitem" <?php if ($navArchivesActive): ?> aria-current="page" <?php endif; ?>>文章列表</a>
                        </li>
                        <li <?php if ($navTagCloudActive): ?> class="active" <?php endif; ?> role="none">
                            <a href="<?php echo $navTagCloudUrl; ?>" role="menuitem" <?php if ($navTagCloudActive): ?> aria-current="page" <?php endif; ?>>文章标签</a>
                        </li>
                        <li <?php if ($navCategoriesActive): ?> class="active" <?php endif; ?> role="none">
                            <a href="<?php echo $navCategoriesUrl; ?>" role="menuitem" <?php if ($navCategoriesActive): ?> aria-current="page" <?php endif; ?>>文章分类</a>
                        </li>
                        <li <?php if ($navAuthorActive): ?> class="active" <?php endif; ?> role="none">
                            <a href="<?php echo $navAuthorUrl; ?>" role="menuitem" <?php if ($navAuthorActive): ?> aria-current="page" <?php endif; ?>>关于博主</a>
                        </li>

                        <?php foreach ($navAllPages as $navPage):
                            $navPageTitle = $navPage['title'];
                            if (in_array($navPage['slug'], ['archives', 'categories', 'about', 'author', 'author_page', 'page-tag-cloud', 'tag-cloud', 'tags'], true)) continue;
                            if (strpos($navPageTitle, 'http') === 0 || strpos($navPageTitle, '/') !== false) continue;
                        ?>
                            <li <?php if ($this->is('page', $navPage['slug'])): ?> class="active" <?php endif; ?> role="none">
                                <a href="<?php echo htmlspecialchars((string) $navPage['permalink'], ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars((string) dygita_t($navPageTitle), ENT_QUOTES, 'UTF-8'); ?>" role="menuitem" <?php if ($this->is('page', $navPage['slug'])): ?> aria-current="page" <?php endif; ?>><?php echo htmlspecialchars((string) dygita_t($navPageTitle), ENT_QUOTES, 'UTF-8'); ?></a>
                            </li>
                        <?php endforeach; ?>

                    <?php endif; ?>
                    <!-- 搜索按钮 -->
                    <li class="search-toggle-li" role="none">
                        <div class="search-toggle">
                            <button id="search-trigger-nav" class="btn btn-secondary" role="menuitem" aria-label="打开搜索">
                                <i class="fa fa-search" aria-hidden="true"></i>
                            </button>
                        </div>
                    </li>
                    <!-- 颜色切换按钮 -->
                    <li class="color-toggle-li" role="none">
                        <div class="color-toggle">
                            <button id="color-toggle" class="btn btn-secondary" role="menuitem" aria-label="切换标题栏颜色">
                                <i class="fa fa-paint-brush" aria-hidden="true"></i>
                            </button>
                        </div>
                    </li>
                    <!-- 主题切换按钮 -->
                    <li class="theme-toggle-li" role="none">
                        <div class="theme-toggle">
                            <button id="theme-toggle" class="btn btn-secondary" role="menuitem" aria-label="切换主题">
                                <i class="fa fa-moon-o" aria-hidden="true"></i>
                            </button>
                        </div>
                    </li>
                    <!-- 语言切换：一键中/英文 -->
                    <?php
                    $dygitaLang = dygita_current_lang();
                    $dygitaLangSwitch = $dygitaLang === 'zh_CN' ? 'en_US' : 'zh_CN';
                    $dygitaLangUrl = $this->request->getRequestUrl();
                    $dygitaLangUrl .= (strpos($dygitaLangUrl, '?') !== false ? '&' : '?') . 'dygita_lang=' . $dygitaLangSwitch;
                    ?>
                    <li class="lang-toggle-li" role="none">
                        <div class="lang-toggle">
                            <a href="<?php echo htmlspecialchars((string) $dygitaLangUrl, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary" id="lang-toggle" role="menuitem" aria-label="<?php echo htmlspecialchars((string) dygita_t($dygitaLang === 'zh_CN' ? 'English' : '中文'), ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars((string) dygita_t($dygitaLang === 'zh_CN' ? 'English' : '中文'), ENT_QUOTES, 'UTF-8'); ?>">
                                <i class="fa fa-globe" aria-hidden="true"></i>
                            </a>
                        </div>
                    </li>
                </ul>
                <!-- 使用 .d-none .d-xl-block，在小于 lg 尺寸(1100px)的屏幕上隐藏 -->
                <form class="search-form-nav d-none d-xl-block" method="post" action="<?php $this->options->siteUrl(); ?>" role="search" aria-label="导航搜索">
                    <label for="s" class="sr-only">搜索关键词</label>
                    <input class="form-control" name="s" id="s" placeholder="搜索关键词..." type="text" aria-describedby="search-desc" />
                    <span id="search-desc" class="sr-only">输入关键词后按回车键搜索</span>
                    <button class="btn btn-secondary" type="submit" aria-label="搜索"><i class="fa fa-search" aria-hidden="true"></i> 查找</button>
                </form>
            </div>
        </div>
    </div>

    <div id="m-header" class="m-header">
        <div id="showLeftPush" class="left m-header-button"></div>
        <h1><a href="<?php $this->options->siteUrl(); ?>"><?php $this->options->title(); ?></a></h1>
        <div id="search-trigger" class="right m-header-search"></div>
    </div>
    <section class="container-inner"> 