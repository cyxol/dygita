// Dygita main interactions (vanilla DOM only)
(function () {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;
    if (window.__dygitaMainInitialized) return;
    window.__dygitaMainInitialized = true;

    function onReady(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn, { once: true });
        } else {
            fn();
        }
    }

    function initLikeAction() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.action[data-action="ding"]');
            if (!btn) return;

            e.preventDefault();

            if (btn.dataset.loading === '1') return;
            var cid = btn.getAttribute('data-id');
            if (!cid) {
                alert('点赞失败，请刷新后重试');
                return;
            }

            btn.dataset.loading = '1';

            var payload = new URLSearchParams();
            payload.append('action', 'like');
            payload.append('cid', cid);

            fetch(window.location.href, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: payload.toString()
            })
                .then(function (res) {
                    if (!res.ok) {
                        throw new Error('HTTP ' + res.status);
                    }
                    return res.text();
                })
                .then(function (raw) {
                    var data = (raw || '').trim();
                    if (data === 'already_liked') {
                        alert('你已经赞过了！');
                        return;
                    }

                    var countNode = btn.querySelector('.count');
                    if (countNode) countNode.textContent = data;

                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-heart-o');
                        icon.classList.add('fa-heart');
                    }
                    alert('点赞成功！');
                })
                .catch(function () {
                    alert('点赞失败，请稍后重试');
                })
                .finally(function () {
                    delete btn.dataset.loading;
                });
        });
    }

    function initSearch() {
        var searchOverlay = null;
        var searchPopup = null;
        var searchForm = null;
        var searchInput = null;
        var resultContent = null;

        function ensureSearchPopup() {
            if (searchOverlay && searchPopup) return;

            var existedOverlay = document.querySelector('.search-pop-overlay');
            if (existedOverlay) {
                searchOverlay = existedOverlay;
                searchPopup = searchOverlay.querySelector('.search-popup');
            }

            if (!searchOverlay) {
                searchOverlay = document.createElement('div');
                searchOverlay.className = 'search-pop-overlay';
                document.body.appendChild(searchOverlay);
            }

            if (!searchPopup) {
                searchPopup = document.createElement('div');
                searchPopup.className = 'search-popup';
                searchPopup.innerHTML =
                    '<div class="search-header">' +
                    '<span class="search-icon"><i class="fa fa-search"></i></span>' +
                    '<form method="post" action="" id="search-form-popup" class="search-input-container" role="search">' +
                    '<input type="text" class="search-input" placeholder="请输入关键词搜索..." name="s" id="search-input-js" />' +
                    '</form>' +
                    '<span class="popup-btn-close"><i class="fa fa-times"></i></span>' +
                    '</div>' +
                    '<div id="search-result"><div id="no-result"><i class="fa fa-search fa-5x"></i></div></div>';
                searchOverlay.appendChild(searchPopup);
            }

            searchForm = document.getElementById('search-form-popup');
            if (searchForm && window.DYGITA && window.DYGITA.config && window.DYGITA.config.hostname) {
                searchForm.action = String(window.DYGITA.config.hostname).replace(/\/$/, '');
            }

            var closeBtn = searchPopup.querySelector('.popup-btn-close');
            if (closeBtn && !closeBtn.dataset.bound) {
                closeBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    searchOverlay.classList.remove('search-active');
                });
                closeBtn.dataset.bound = '1';
            }

            if (!searchOverlay.dataset.bound) {
                searchOverlay.addEventListener('click', function (e) {
                    if (e.target === searchOverlay) searchOverlay.classList.remove('search-active');
                });
                searchOverlay.dataset.bound = '1';
            }

            searchInput = searchPopup.querySelector('.search-input');
            resultContent = document.getElementById('search-result');

            if (searchInput && resultContent && !searchInput.dataset.bound) {
                searchInput.addEventListener('input', function () {
                    var keyword = searchInput.value.trim();
                    if (keyword.length > 0) {
                        resultContent.innerHTML = '<div id="no-result"><p style="color:#999;font-size:14px;"><i class="fa fa-keyboard-o"></i> 按回车键搜索 "<strong>' + escapeHtml(keyword) + '</strong>"</p></div>';
                    } else {
                        resultContent.innerHTML = '<div id="no-result"><i class="fa fa-search fa-5x"></i></div>';
                    }
                });

                searchInput.addEventListener('keypress', function (e) {
                    if (e.key !== 'Enter') return;
                    var keyword = searchInput.value.trim();
                    if (!keyword) return;
                    if (searchForm) searchForm.submit();
                });

                searchInput.dataset.bound = '1';
            }
        }

        function openSearch(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }
            ensureSearchPopup();
            searchOverlay.classList.add('search-active');
            var input = searchPopup ? searchPopup.querySelector('.search-input') : null;
            if (input) input.focus();
        }

        var triggers = [
            document.getElementById('search-trigger-nav'),
            document.getElementById('search-trigger'),
            document.querySelector('.search-toggle')
        ];
        triggers.forEach(function (node) {
            if (node) node.addEventListener('click', openSearch);
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && searchOverlay) searchOverlay.classList.remove('search-active');
        });

        function escapeHtml(str) {
            return str.replace(/[&<>"']/g, function (ch) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#39;'
                }[ch];
            });
        }

        // Search popup DOM is lazily created on first open to avoid no-CSS fallback duplication.
    }

    function initCatalog() {
        var toc = document.getElementById('toc-content');
        var articleContent = document.querySelector('.article-content');
        if (!toc || !articleContent) return;

        var headers = articleContent.querySelectorAll('h2, h3');
        if (!headers.length) {
            var tocWidget = document.getElementById('widget-toc');
            if (tocWidget) tocWidget.style.display = 'none';
            return;
        }

        var frag = document.createDocumentFragment();
        var ul = document.createElement('ul');

        headers.forEach(function (header, index) {
            var id = 'section-' + index;
            header.id = id;

            var li = document.createElement('li');
            if (header.tagName.toLowerCase() === 'h3') {
                li.style.marginLeft = '15px';
                li.style.listStyle = 'circle';
            }

            var a = document.createElement('a');
            a.setAttribute('href', '#' + id);
            a.textContent = header.textContent || '';
            li.appendChild(a);
            ul.appendChild(li);
        });

        frag.appendChild(ul);
        toc.innerHTML = '';
        toc.appendChild(frag);

        document.addEventListener('click', function (e) {
            var link = e.target.closest('.catalog-content a');
            if (!link) return;

            var targetId = link.getAttribute('href');
            if (!targetId || targetId.charAt(0) !== '#') return;

            var targetElement = document.querySelector(targetId);
            if (!targetElement) return;

            e.preventDefault();
            var offsetTop = targetElement.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: Math.max(offsetTop, 0), behavior: 'smooth' });
        });

        var ticking = false;
        function updateCurrentSection() {
            var scrollPosition = window.scrollY + 100;
            var currentSection = '';

            headers.forEach(function (header) {
                var sectionTop = header.getBoundingClientRect().top + window.scrollY;
                if (scrollPosition >= sectionTop) currentSection = '#' + header.id;
            });

            if (!currentSection) return;

            var links = document.querySelectorAll('.catalog-content a');
            links.forEach(function (a) {
                a.classList.toggle('active', a.getAttribute('href') === currentSection);
            });
        }

        window.addEventListener('scroll', function () {
            if (ticking) return;
            ticking = true;
            window.requestAnimationFrame(function () {
                updateCurrentSection();
                ticking = false;
            });
        }, { passive: true });

        updateCurrentSection();
    }

    function initLazyLoad() {
        var images = document.querySelectorAll('img[data-src]');
        if (!images.length) return;

        function loadImage(img) {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            img.removeAttribute('data-src');
        }

        if ('IntersectionObserver' in window) {
            var imageObserver = new IntersectionObserver(function (entries, observer) {
                entries.forEach(function (entry) {
                    if (!entry.isIntersecting) return;
                    loadImage(entry.target);
                    observer.unobserve(entry.target);
                });
            });

            images.forEach(function (img) {
                imageObserver.observe(img);
            });
            return;
        }

        function fallbackLoad() {
            var lazyImages = document.querySelectorAll('img[data-src]');
            if (!lazyImages.length) {
                document.removeEventListener('scroll', fallbackLoad);
                window.removeEventListener('resize', fallbackLoad);
                window.removeEventListener('orientationchange', fallbackLoad);
                return;
            }

            lazyImages.forEach(function (img) {
                var rect = img.getBoundingClientRect();
                if (rect.top <= window.innerHeight && rect.bottom >= 0) loadImage(img);
            });
        }

        document.addEventListener('scroll', fallbackLoad, { passive: true });
        window.addEventListener('resize', fallbackLoad);
        window.addEventListener('orientationchange', fallbackLoad);
        fallbackLoad();
    }

    onReady(function () {
        initLikeAction();
        initSearch();
        initCatalog();
        initLazyLoad();
    });
})();