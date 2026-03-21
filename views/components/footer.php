<?php if (!defined('__TYPECHO_ROOT_DIR__'))
    exit; ?>
</section> <!-- container -->

<footer class="footer">
    <div class="footer-inner">
        <div class="footer-copyright">
            <?php $currentYear = date('Y'); ?>
            <span>&copy; 2014-<?php echo htmlspecialchars((string) $currentYear, ENT_QUOTES, 'UTF-8'); ?> | 菜牙.<span class="footer-heart" aria-hidden="true">❤</span> | Theme by Dydita | Ported to Typecho</span>
        </div>
    </div>
</footer>

<script defer src="<?php $this->options->themeUrl('js/main.js'); ?>"></script>
<!-- Swiper.js 轮播图 - CDN + 本地备用 -->
<?php if ($this->options->swiperEnabled == '1'): ?>
<script defer src="https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js" onerror="this.onerror=null;this.src='https://unpkg.com/swiper@8/swiper-bundle.min.js'"></script>
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
<div class="back-to-top" role="button" aria-label="<?php dygita_e('返回顶部'); ?>" tabindex="0">
    <i class="fa fa-arrow-up" aria-hidden="true"></i>
    <span>0%</span>
</div>
<div class="reading-progress-bar" role="progressbar" aria-label="<?php dygita_e('阅读进度'); ?>" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"></div>

<script>
    requestAnimationFrame(function(){requestAnimationFrame(function(){document.documentElement.classList.remove('no-transitions')})});
</script>
</body>

</html>