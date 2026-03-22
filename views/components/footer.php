<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
</section> <!-- container -->

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-copyright">
            <span class="footer-copy">©</span>
            <span class="footer-years">2014&ndash;<?php echo date('Y'); ?></span>
            <span class="footer-sep">|</span>
            <a href="https://caiya.xin/" target="_blank" rel="noopener" class="footer-site-link">菜牙点心</a>
            <span class="footer-sep">|</span>
            <span class="footer-brand">
                <span class="footer-icon footer-icon-cabbage" data-tip="菜" aria-label="白菜"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 26" aria-hidden="true"><defs><!-- 从下到上：白→绿 渐变 --><linearGradient id="cg" x1="0" y1="1" x2="0" y2="0"><stop offset="0%" stop-color="#f0f9f0"/><stop offset="40%" stop-color="#a3d9a3"/><stop offset="100%" stop-color="#237a23"/></linearGradient><!-- 中心茎：从下到上 白→半透明 --><linearGradient id="cs" x1="0" y1="1" x2="0" y2="0"><stop offset="0%" stop-color="white"/><stop offset="70%" stop-color="rgba(255,255,255,0.85)"/><stop offset="100%" stop-color="rgba(255,255,255,0.15)"/></linearGradient><!-- 侧茎渐变 --><linearGradient id="ss" x1="0" y1="1" x2="0" y2="0"><stop offset="0%" stop-color="rgba(255,255,255,0.8)"/><stop offset="100%" stop-color="rgba(255,255,255,0.05)"/></linearGradient></defs><!-- 主体：渐变填充（根部白、叶端绿） --><path d="M6 24C2 20 1 13 3 8 5 3 9 1 12 1 15 1 19 3 21 8 23 13 22 20 18 24 15 26 9 26 6 24Z" fill="url(#cg)"/><!-- 中心白茎（根部醒目白，往上渐隐） --><path d="M9 24C9 16 10 8 12 3 14 8 15 16 15 24Z" fill="url(#cs)"/><!-- 左侧白茎 --><path d="M7 23C7 16 8 9 10 5 9 9 8.5 16 8.5 23Z" fill="url(#ss)"/><!-- 右侧白茎 --><path d="M17 23C17 16 16 9 14 5 15 9 15.5 16 15.5 23Z" fill="url(#ss)"/><!-- 顶部深绿叶尖 --><path d="M5 10C3 5 7 1.5 12 1.5 17 1.5 21 5 19 10 17 6 14 4 12 4 10 4 7 6 5 10Z" fill="#1a5e1a"/><!-- 叶脉 --><path d="M8 6Q7 4 9 2.5" stroke="#5ec45e" stroke-width="0.5" fill="none" stroke-linecap="round"/><path d="M16 6Q17 4 15 2.5" stroke="#5ec45e" stroke-width="0.5" fill="none" stroke-linecap="round"/></svg></span><span class="footer-icon footer-icon-tooth" data-tip="牙" aria-label="牙齿">🦷</span><span class="footer-icon footer-icon-dot" data-tip="点" aria-hidden="true"></span><span class="footer-icon footer-icon-heart" data-tip="心" aria-hidden="true">❤</span>
            </span>
            <span class="footer-sep">|</span>
            <span class="footer-theme">Theme by Dygita</span>
            <span class="footer-sep">|</span>
            <span class="footer-powered">Ported to <a href="https://typecho.org/" target="_blank" rel="noopener">Typecho</a></span>
        </div>
    </div>
</footer>

<script defer src="<?php $this->options->themeUrl('js/main.js'); ?>"></script>
<!-- Swiper.js 轮播图 - CDN + 本地备用 -->
<?php if ($this->options->swiperEnabled == '1'):
    $swiperJsUrl = dygita_cdn_url('Swiper', '8.4.5', 'swiper-bundle.min.js');
?>
<script defer src="<?php echo $swiperJsUrl; ?>" onerror="this.onerror=null;this.src='https://unpkg.com/swiper@8/swiper-bundle.min.js'"></script>
<?php
endif; ?>
<!-- 粒子背景动画 -->
<script defer src="<?php $this->options->themeUrl('js/vendor/particles.min.js'); ?>"></script>
<script defer src="<?php $this->options->themeUrl('js/headerCanvas.js'); ?>"></script>
<script defer src="<?php $this->options->themeUrl('js/sidebar.js'); ?>"></script>
<script defer src="<?php $this->options->themeUrl('js/theme-switcher.js'); ?>"></script>
<script defer src="<?php $this->options->themeUrl('js/reading-progress.js'); ?>"></script>
<?php if ($this->options->swiperEnabled == '1'): ?>
<script>
window.DYGITA = window.DYGITA || {};
window.DYGITA.swiper = {
    autoplay: <?php echo ($this->options->swiperAutoplay == '1') ? 'true' : 'false'; ?>,
    delay: <?php echo $this->options->swiperDelay ? intval($this->options->swiperDelay) : 3000; ?>,
    speed: <?php echo $this->options->swiperSpeed ? intval($this->options->swiperSpeed) : 1000; ?>
};
</script>
<script defer src="<?php $this->options->themeUrl('js/swiper-init.js'); ?>"></script>
<?php endif; ?>

<!-- 站点统计代码 -->
<?php if ($this->options->enableStatistics == '1'): ?>
    <!-- 百度统计 -->
    <?php if (!empty($this->options->baiduAnalytics)): ?>
    <script>
    var _hmt = _hmt || [];
    (function() {
        var hm = document.createElement("script");
        hm.src = "https://hm.baidu.com/hm.js?" + <?php echo json_encode($this->options->baiduAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
        var s = document.getElementsByTagName("script")[0]; 
        s.parentNode.insertBefore(hm, s);
    })();
    </script>
    <?php
    endif; ?>
    
    <!-- Google Analytics -->
    <?php if (!empty($this->options->googleAnalytics)): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo htmlspecialchars($this->options->googleAnalytics, ENT_QUOTES, 'UTF-8'); ?>"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', <?php echo json_encode($this->options->googleAnalytics, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT); ?>);
    </script>
    <?php
    endif; ?>
<?php
endif; ?>

<?php $this->footer(); ?>
<div class="back-to-top" role="button" aria-label="<?php _e('返回顶部'); ?>" tabindex="0">
    <i class="fa fa-arrow-up" aria-hidden="true"></i>
    <span>0%</span>
</div>
<div class="reading-progress-bar" role="progressbar" aria-label="<?php _e('阅读进度'); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>

<template id="tpl-search-popup">
    <div class="search-header">
        <span class="search-icon"><i class="fa fa-search"></i></span>
        <form method="post" action="" id="search-form-popup" class="search-input-container" role="search">
            <input type="text" class="search-input" placeholder="<?php _e('请输入关键词搜索...'); ?>" name="s" id="search-input-js" />
        </form>
        <span class="popup-btn-close"><i class="fa fa-times"></i></span>
    </div>
    <div id="search-result"><div id="no-result"><i class="fa fa-search fa-5x"></i></div></div>
</template>

<?php
$searchIndexUrl = \Typecho\Router::url('do', array('action' => 'dygita-search-index'), $this->options->index);
?>
<script>
window.DYGITA = window.DYGITA || {};
window.DYGITA.searchIndexUrl = <?php echo json_encode($searchIndexUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
</script>

<script>
    requestAnimationFrame(function(){requestAnimationFrame(function(){document.documentElement.classList.remove('no-transitions')})});
</script>
</body>

</html>