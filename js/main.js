// Dygita main interactions (vanilla DOM only)
(function () {
    if (typeof window === 'undefined' || typeof document === 'undefined') return;

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
            payload.append('cid', cid);

            var likeUrl = window.DYGITA && window.DYGITA.config && window.DYGITA.config.likeUrl
                ? window.DYGITA.config.likeUrl
                : '/action/dygita-like';

            fetch(likeUrl, {
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
        var liveSearchLimit = 8;
        var searchDebounceTimer = null;

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
                var tpl = document.getElementById('tpl-search-popup');
                if (tpl) {
                    searchPopup.appendChild(tpl.content.cloneNode(true));
                }
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
                    if (searchDebounceTimer) window.clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = window.setTimeout(function () {
                        renderLiveSearch(searchInput.value.trim());
                    }, 120);
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
            if (input) {
                input.focus();
                renderLiveSearch(input.value.trim());
            }
        }

        var triggers = [
            document.getElementById('search-trigger-nav'),
            document.getElementById('search-trigger'),
            document.querySelector('.search-toggle')
        ];
        triggers.forEach(function (node) {
            if (!node || node.dataset.searchBound === '1') return;
            node.addEventListener('click', openSearch);
            node.dataset.searchBound = '1';
        });

        if (!window.__dygitaSearchEscBound) {
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && searchOverlay) searchOverlay.classList.remove('search-active');
            });
            window.__dygitaSearchEscBound = true;
        }

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

        function getSearchIndex() {
            if (!window.DYGITA || !Array.isArray(window.DYGITA.searchIndex)) return [];
            return window.DYGITA.searchIndex;
        }

        function renderLiveSearch(keyword) {
            if (!resultContent) return;

            if (!keyword) {
                resultContent.innerHTML = '<div id="no-result"><i class="fa fa-search fa-5x"></i></div>';
                return;
            }

            var normalizedKeyword = keyword.toLowerCase();
            var searchIndex = getSearchIndex();
            var matched = [];

            for (var i = 0; i < searchIndex.length; i++) {
                var item = searchIndex[i] || {};
                var title = String(item.title || '');
                var excerpt = String(item.excerpt || '');
                var haystack = (title + ' ' + excerpt).toLowerCase();
                if (haystack.indexOf(normalizedKeyword) === -1) continue;
                matched.push(item);
                if (matched.length >= liveSearchLimit) break;
            }

            if (!matched.length) {
                resultContent.innerHTML = '<div id="no-result"><p style="color:#999;font-size:14px;"><i class="fa fa-frown-o"></i> 未找到与 "<strong>' + escapeHtml(keyword) + '</strong>" 相关的内容，按回车尝试全站搜索</p></div>';
                return;
            }

            var html = '';
            for (var j = 0; j < matched.length; j++) {
                var row = matched[j] || {};
                var rowTitle = escapeHtml(String(row.title || '无标题'));
                var rowUrl = escapeHtml(String(row.url || '#'));
                var rowExcerpt = escapeHtml(String(row.excerpt || ''));
                var rowDate = escapeHtml(String(row.date || ''));
                html += '<p class="search-result">'
                    + '<a href="' + rowUrl + '" style="display:block;color:inherit;text-decoration:none;">'
                    + '<strong style="display:block;margin-bottom:4px;">' + rowTitle + '</strong>'
                    + '<span style="display:block;font-size:12px;color:#999;margin-bottom:4px;">' + rowDate + '</span>'
                    + '<span style="display:block;font-size:13px;line-height:1.6;color:inherit;">' + rowExcerpt + '</span>'
                    + '</a>'
                    + '</p>';
            }
            html += '<p class="search-result" style="color:#999;font-size:12px;"><i class="fa fa-keyboard-o"></i> 回车可使用完整搜索页查看更多结果</p>';
            resultContent.innerHTML = html;
        }

    }

    function initTocInteraction() {
        if (typeof window.__dygitaTocCleanup === 'function') {
            window.__dygitaTocCleanup();
            window.__dygitaTocCleanup = null;
        }

        var catalogContent = document.querySelector('.catalog-content');
        if (!catalogContent) return;

        var links = catalogContent.querySelectorAll('a[href^="#"]');
        if (!links.length) return;

        // Build anchor-to-link map from PHP-generated TOC
        var anchors = [];
        links.forEach(function (link) {
            var id = link.getAttribute('href').slice(1);
            var el = document.getElementById(id);
            if (el) anchors.push({ el: el, link: link });
        });

        if (!anchors.length) return;

        // Smooth scroll on TOC link click
        function onCatalogClick(e) {
            var link = e.target.closest('a[href^="#"]');
            if (!link) return;
            var target = document.getElementById(link.getAttribute('href').slice(1));
            if (!target) return;
            e.preventDefault();
            var offsetTop = target.getBoundingClientRect().top + window.scrollY - 80;
            window.scrollTo({ top: Math.max(offsetTop, 0), behavior: 'smooth' });
        }
        catalogContent.addEventListener('click', onCatalogClick);

        function updateCurrentSection(current) {
            links.forEach(function (link) { link.classList.remove('active'); });
            if (current) current.link.classList.add('active');
        }

        var activeAnchor = anchors[0] || null;
        updateCurrentSection(activeAnchor);

        var anchorOrderMap = new WeakMap();
        anchors.forEach(function (item, idx) {
            anchorOrderMap.set(item.el, idx);
        });

        var visibleEntries = new Map();
        var tocObserver = null;

        function pickActiveAnchor() {
            if (!visibleEntries.size) return;
            var best = null;
            visibleEntries.forEach(function (entry) {
                if (!best) {
                    best = entry;
                    return;
                }
                if (entry.boundingClientRect.top < best.boundingClientRect.top) {
                    best = entry;
                }
            });
            if (!best || !best.target) return;
            var idx = anchorOrderMap.get(best.target);
            if (typeof idx !== 'number' || !anchors[idx]) return;
            activeAnchor = anchors[idx];
            updateCurrentSection(activeAnchor);
        }

        if ('IntersectionObserver' in window) {
            tocObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        visibleEntries.set(entry.target, entry);
                    } else {
                        visibleEntries.delete(entry.target);
                    }
                });
                pickActiveAnchor();
            }, {
                root: null,
                rootMargin: '-10% 0px -70% 0px',
                threshold: [0, 0.01, 0.1, 0.25, 0.5, 1]
            });
            anchors.forEach(function (anchor) {
                tocObserver.observe(anchor.el);
            });
        } else {
            // Fallback for very old browsers without IntersectionObserver.
            var ticking = false;
            function onScrollFallback() {
                if (ticking) return;
                ticking = true;
                window.requestAnimationFrame(function () {
                    var scrollPosition = window.scrollY + 100;
                    var current = null;
                    anchors.forEach(function (anchor) {
                        var top = anchor.el.getBoundingClientRect().top + window.scrollY;
                        if (scrollPosition >= top) current = anchor;
                    });
                    if (current) {
                        activeAnchor = current;
                        updateCurrentSection(activeAnchor);
                    }
                    ticking = false;
                });
            }
            window.addEventListener('scroll', onScrollFallback, { passive: true });
            window.__dygitaTocCleanup = function () {
                catalogContent.removeEventListener('click', onCatalogClick);
                window.removeEventListener('scroll', onScrollFallback);
            };
            return;
        }

        window.__dygitaTocCleanup = function () {
            catalogContent.removeEventListener('click', onCatalogClick);
            if (tocObserver) tocObserver.disconnect();
            visibleEntries.clear();
        };
    }

    function initLazyLoad() {
        var images = document.querySelectorAll('img[data-src]:not([data-lazy-bound="1"])');
        if (!images.length) return;

        function loadImage(img) {
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            img.removeAttribute('data-src');
        }

        if ('IntersectionObserver' in window) {
            if (!window.__dygitaLazyObserver) {
                window.__dygitaLazyObserver = new IntersectionObserver(function (entries, observer) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting) return;
                        loadImage(entry.target);
                        observer.unobserve(entry.target);
                    });
                });
            }

            images.forEach(function (img) {
                img.dataset.lazyBound = '1';
                window.__dygitaLazyObserver.observe(img);
            });
            return;
        }

        if (!window.__dygitaLazyFallbackLoad) {
            window.__dygitaLazyFallbackTicking = false;
            window.__dygitaLazyFallbackLoad = function () {
                if (window.__dygitaLazyFallbackTicking) return;
                window.__dygitaLazyFallbackTicking = true;
                window.requestAnimationFrame(function () {
                    window.__dygitaLazyFallbackTicking = false;
                    var lazyImages = document.querySelectorAll('img[data-src]');
                    if (!lazyImages.length) return;
                    lazyImages.forEach(function (img) {
                        var rect = img.getBoundingClientRect();
                        if (rect.top <= window.innerHeight && rect.bottom >= 0) loadImage(img);
                    });
                });
            };
        }

        if (!window.__dygitaLazyFallbackBound) {
            document.addEventListener('scroll', window.__dygitaLazyFallbackLoad, { passive: true });
            window.addEventListener('resize', window.__dygitaLazyFallbackLoad);
            window.addEventListener('orientationchange', window.__dygitaLazyFallbackLoad);
            window.__dygitaLazyFallbackBound = true;
        }

        images.forEach(function (img) {
            img.dataset.lazyBound = '1';
        });

        window.__dygitaLazyFallbackLoad();
    }

    function initPageFeatures() {
        initSearch();
        initTocInteraction();
        initLazyLoad();
    }

    function bindPageLifecycle() {
        if (window.__dygitaPageLifecycleBound) return;

        function reinitPageFeatures() {
            window.requestAnimationFrame(function () {
                initPageFeatures();
            });
        }

        var events = ['pjax:complete', 'pjax:end', 'pjax:success', 'turbolinks:load', 'dygita:page-ready'];
        events.forEach(function (eventName) {
            document.addEventListener(eventName, reinitPageFeatures);
        });

        window.DYGITA = window.DYGITA || {};
        window.DYGITA.reinitPageFeatures = reinitPageFeatures;
        window.__dygitaPageLifecycleBound = true;
    }

    onReady(function () {
        if (!window.__dygitaMainGlobalBound) {
            initLikeAction();
            window.__dygitaMainGlobalBound = true;
        }
        bindPageLifecycle();
        initPageFeatures();
    });
})();