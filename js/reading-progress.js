/**
 * 返回顶部与阅读进度条
 */
(function() {
    var stateKey = '__dygitaReadingProgressState';

    function cleanupPrevious() {
        var prev = window[stateKey];
        if (prev && typeof prev.cleanup === 'function') {
            prev.cleanup();
        }
    }

    function init() {
        cleanupPrevious();

        var backToTop = document.querySelector('.back-to-top');
        var readingProgressBar = document.querySelector('.reading-progress-bar');
        var ticking = false;

        if (!backToTop && !readingProgressBar) return;

        function updateScroll() {
            var scrollTop = window.scrollY || document.documentElement.scrollTop;
            var docHeight = document.documentElement.scrollHeight;
            var winHeight = window.innerHeight;
            var contentVisibilityHeight = docHeight - winHeight;
            var scrollPercent = (contentVisibilityHeight > 0) ? Math.min(100 * scrollTop / contentVisibilityHeight, 100) : 0;

            if (backToTop) {
                if (scrollTop > 50) {
                    backToTop.classList.add('back-to-top-on');
                } else {
                    backToTop.classList.remove('back-to-top-on');
                }
                var percentSpan = backToTop.querySelector('span');
                if (percentSpan) percentSpan.innerText = Math.round(scrollPercent) + '%';
            }
            if (readingProgressBar) {
                readingProgressBar.style.width = scrollPercent.toFixed(2) + '%';
                readingProgressBar.setAttribute('aria-valuenow', Math.round(scrollPercent));
            }
        }

        function onScroll() {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function() {
                updateScroll();
                ticking = false;
            });
        }

        function onResize() {
            updateScroll();
        }

        updateScroll();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onResize);

        if (backToTop) {
            backToTop.addEventListener('click', function(e) {
                e.preventDefault();
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
            backToTop.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                }
            });
        }

        window[stateKey] = {
            cleanup: function() {
                window.removeEventListener('scroll', onScroll);
                window.removeEventListener('resize', onResize);
            }
        };
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
