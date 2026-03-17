/**
 * Swiper 轮播初始化
 * 依赖: 在 footer 内联中设置 window.DYGITA_SWIPER = { delay: 3000, speed: 1000, autoplay: true }
 */
(function() {
    function init() {
        var swiperHome = document.getElementById('swiper-home');
        if (!swiperHome) return;
        if (typeof window.Swiper !== 'function') {
            if (typeof console !== 'undefined' && console.warn) {
                console.warn('Swiper 未加载成功，已跳过轮播初始化。');
            }
            return;
        }
        var opts = window.DYGITA_SWIPER || {};
        var config = {
            loop: true,
            speed: opts.speed || 1000,
            pagination: { el: '.swiper-pagination', clickable: true },
            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
            slidesPerView: 1,
            spaceBetween: 0,
            breakpoints: { 768: { slidesPerView: 1, spaceBetween: 0 }, 1024: { slidesPerView: 1, spaceBetween: 0 } }
        };
        if (opts.autoplay) {
            config.autoplay = {
                delay: opts.delay || 3000,
                disableOnInteraction: false
            };
        }
        new window.Swiper('#swiper-home', config);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
