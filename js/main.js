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

    // Toast 提示框 - 替代原生 alert，提供更好的用户体验
    // 样式已迁移到 css/components/toast.css，通过 CSS 类控制
    function showToast(message, type) {
        type = type || 'info'; // 'success', 'error', 'info', 'warning'

        // 创建 toast 容器（如果不存在）
        var container = document.getElementById('dygita-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'dygita-toast-container';
            // 容器定位样式在 CSS 中定义 (#dygita-toast-container)
            document.body.appendChild(container);
        }

        // 创建 toast 元素 - 样式通过 CSS 类控制
        var toast = document.createElement('div');
        toast.className = 'dygita-toast dygita-toast-' + type;

        // 图标映射
        var icons = {
            'success': 'fa-check-circle',
            'error': 'fa-times-circle',
            'info': 'fa-info-circle',
            'warning': 'fa-exclamation-triangle'
        };
        var iconClass = icons[type] || icons.info;

        toast.innerHTML = '<i class="fa ' + iconClass + '"></i><span>' + escapeHtml(message) + '</span>';

        container.appendChild(toast);

        // 触发动画 - 通过添加 CSS 类控制
        requestAnimationFrame(function() {
            toast.classList.add('dygita-toast--visible');
        });

        // 自动关闭
        setTimeout(function() {
            toast.classList.add('dygita-toast--hiding');
            setTimeout(function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
                // 如果容器为空，移除容器
                if (container.children.length === 0 && container.parentNode) {
                    container.parentNode.removeChild(container);
                }
            }, 300);
        }, 3000);

        // 点击关闭
        toast.addEventListener('click', function() {
            toast.classList.add('dygita-toast--hiding');
            setTimeout(function() {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        });
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function initLikeAction() {
        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.action[data-action="ding"]');
            if (!btn) return;

            e.preventDefault();

            if (btn.dataset.loading === '1') return;
            var cid = btn.getAttribute('data-id');
            if (!cid) {
                showToast('点赞失败，请刷新后重试', 'error');
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
                        showToast('你已经赞过了！', 'info');
                        return;
                    }

                    var countNode = btn.querySelector('.count');
                    if (countNode) countNode.textContent = data;

                    var icon = btn.querySelector('i');
                    if (icon) {
                        icon.classList.remove('fa-heart-o');
                        icon.classList.add('fa-heart');
                    }
                    showToast('点赞成功！', 'success');
                })
                .catch(function () {
                    showToast('点赞失败，请稍后重试', 'error');
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

        // 性能优化：预建立搜索索引，避免每次搜索都遍历全部数据
        var searchIndexCache = null;
        var searchIndexReady = false;

        // PJAX 内存泄漏修复：存储清理函数
        var cleanupFunctions = [];

        function buildSearchIndex() {
            if (searchIndexReady) return;

            var rawIndex = getSearchIndex();
            if (!rawIndex || !rawIndex.length) {
                searchIndexCache = [];
                searchIndexReady = true;
                return;
            }

            // 预处理：将标题和摘要转为小写，建立倒排索引
            searchIndexCache = rawIndex.map(function(item) {
                var title = String(item.title || '');
                var excerpt = String(item.excerpt || '');
                return {
                    title: title,
                    excerpt: excerpt,
                    url: item.url || '#',
                    date: item.date || '',
                    // 预先转换为小写，避免搜索时重复转换
                    searchText: (title + ' ' + excerpt).toLowerCase(),
                    // 计算权重：标题匹配权重更高
                    titleLower: title.toLowerCase()
                };
            });

            searchIndexReady = true;
        }

        function ensureSearchPopup() {
            if (searchOverlay && searchPopup) {
                // 检查 DOM 是否仍然存在（防止 PJAX 导致的 detached DOM）
                if (!document.body.contains(searchOverlay)) {
                    searchOverlay = null;
                    searchPopup = null;
                    searchForm = null;
                    searchInput = null;
                    resultContent = null;
                }
            }

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
                var closeBtnHandler = function(e) {
                    e.stopPropagation();
                    searchOverlay.classList.remove('search-active');
                };
                closeBtn.addEventListener('click', closeBtnHandler);
                closeBtn.dataset.bound = '1';

                // 注册清理函数
                cleanupFunctions.push(function() {
                    closeBtn.removeEventListener('click', closeBtnHandler);
                    delete closeBtn.dataset.bound;
                });
            }

            if (!searchOverlay.dataset.bound) {
                var overlayHandler = function(e) {
                    if (e.target === searchOverlay) searchOverlay.classList.remove('search-active');
                };
                searchOverlay.addEventListener('click', overlayHandler);
                searchOverlay.dataset.bound = '1';

                // 注册清理函数
                cleanupFunctions.push(function() {
                    searchOverlay.removeEventListener('click', overlayHandler);
                    delete searchOverlay.dataset.bound;
                });
            }

            searchInput = searchPopup.querySelector('.search-input');
            resultContent = document.getElementById('search-result');

            if (searchInput && resultContent && !searchInput.dataset.bound) {
                var inputHandler = function() {
                    if (searchDebounceTimer) window.clearTimeout(searchDebounceTimer);
                    searchDebounceTimer = window.setTimeout(function () {
                        renderLiveSearch(searchInput.value.trim());
                    }, 120);
                };

                var keypressHandler = function(e) {
                    if (e.key !== 'Enter') return;
                    var keyword = searchInput.value.trim();
                    if (!keyword) return;
                    if (searchForm) searchForm.submit();
                };

                searchInput.addEventListener('input', inputHandler);
                searchInput.addEventListener('keypress', keypressHandler);
                searchInput.dataset.bound = '1';

                // 注册清理函数
                cleanupFunctions.push(function() {
                    searchInput.removeEventListener('input', inputHandler);
                    searchInput.removeEventListener('keypress', keypressHandler);
                    delete searchInput.dataset.bound;
                });
            }
        }

        function openSearch(e) {
            if (e) {
                e.stopPropagation();
                e.preventDefault();
            }

            // 延迟构建索引，只在首次打开搜索时构建
            if (!searchIndexReady) {
                buildSearchIndex();
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

            // 注册清理函数
            cleanupFunctions.push(function() {
                node.removeEventListener('click', openSearch);
                delete node.dataset.searchBound;
            });
        });

        if (!window.__dygitaSearchEscBound) {
            var escHandler = function(e) {
                if (e.key === 'Escape' && searchOverlay) searchOverlay.classList.remove('search-active');
            };
            document.addEventListener('keydown', escHandler);
            window.__dygitaSearchEscBound = true;

            // 注册清理函数
            cleanupFunctions.push(function() {
                document.removeEventListener('keydown', escHandler);
                window.__dygitaSearchEscBound = false;
            });
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

            // 确保索引已构建
            if (!searchIndexReady) {
                buildSearchIndex();
            }

            var normalizedKeyword = keyword.toLowerCase();
            var matched = [];

            // 性能优化：使用预处理的索引，避免重复转换小写
            // 同时实现优先级排序：标题匹配 > 内容匹配
            var titleMatches = [];
            var contentMatches = [];

            for (var i = 0; i < searchIndexCache.length; i++) {
                var item = searchIndexCache[i];

                // 检查是否匹配
                var matchIndex = item.searchText.indexOf(normalizedKeyword);
                if (matchIndex === -1) continue;

                // 区分标题匹配和内容匹配，标题匹配优先级更高
                var titleMatchIndex = item.titleLower.indexOf(normalizedKeyword);
                if (titleMatchIndex !== -1) {
                    titleMatches.push({
                        item: item,
                        matchIndex: titleMatchIndex
                    });
                } else {
                    contentMatches.push({
                        item: item,
                        matchIndex: matchIndex
                    });
                }

                // 早期退出：如果已经找到足够的结果
                if (titleMatches.length + contentMatches.length >= liveSearchLimit * 2) {
                    break;
                }
            }

            // 排序：标题匹配靠前，同类型按匹配位置排序（越靠前越相关）
            titleMatches.sort(function(a, b) {
                return a.matchIndex - b.matchIndex;
            });
            contentMatches.sort(function(a, b) {
                return a.matchIndex - b.matchIndex;
            });

            // 合并结果，标题匹配优先
            matched = titleMatches.concat(contentMatches).slice(0, liveSearchLimit);

            if (!matched.length) {
                resultContent.innerHTML = '<div id="no-result"><p style="color:#999;font-size:14px;"><i class="fa fa-frown-o"></i> 未找到与 "<strong>' + escapeHtml(keyword) + '</strong>" 相关的内容，按回车尝试全站搜索</p></div>';
                return;
            }

            var html = '';
            for (var j = 0; j < matched.length; j++) {
                var row = matched[j].item;
                var rowTitle = escapeHtml(row.title || '无标题');
                var rowUrl = escapeHtml(row.url);
                var rowExcerpt = escapeHtml(row.excerpt);
                var rowDate = escapeHtml(row.date);
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

        // PJAX 内存泄漏修复：返回清理函数
        return function cleanup() {
            // 清理所有事件监听器
            cleanupFunctions.forEach(function(fn) {
                try {
                    fn();
                } catch (e) {
                    // 忽略清理错误
                }
            });
            cleanupFunctions = [];

            // 清理 debounce timer
            if (searchDebounceTimer) {
                window.clearTimeout(searchDebounceTimer);
                searchDebounceTimer = null;
            }

            // 清除 DOM 引用，帮助垃圾回收
            searchOverlay = null;
            searchPopup = null;
            searchForm = null;
            searchInput = null;
            resultContent = null;

            // 清除搜索索引缓存，确保 PJAX 跳转后使用新页面的 searchIndex 数据
            searchIndexCache = null;
            searchIndexReady = false;
        };
    }

    function initTocInteraction() {
        var catalogContent = document.querySelector('.catalog-content');
        if (!catalogContent) return null;

        var links = catalogContent.querySelectorAll('a[href^="#"]');
        if (!links.length) return null;

        // Build anchor-to-link map from PHP-generated TOC
        var anchors = [];
        links.forEach(function (link) {
            var id = link.getAttribute('href').slice(1);
            var el = document.getElementById(id);
            if (el) anchors.push({ el: el, link: link });
        });

        if (!anchors.length) return null;

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

            // PJAX 内存泄漏修复：返回清理函数
            return function cleanup() {
                catalogContent.removeEventListener('click', onCatalogClick);
                if (tocObserver) {
                    tocObserver.disconnect();
                    tocObserver = null;
                }
                visibleEntries.clear();
                // 清除 DOM 引用
                catalogContent = null;
                links = null;
                anchors = null;
            };
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

            // PJAX 内存泄漏修复：返回清理函数
            return function cleanup() {
                catalogContent.removeEventListener('click', onCatalogClick);
                window.removeEventListener('scroll', onScrollFallback);
                // 清除 DOM 引用
                catalogContent = null;
                links = null;
                anchors = null;
            };
        }
    }

    function initLazyLoad() {
        var images = document.querySelectorAll('img[data-src]:not([data-lazy-bound="1"])');
        if (!images.length) return null;

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

            // PJAX 内存泄漏修复：返回清理函数
            return function cleanup() {
                // 取消观察所有图片
                images.forEach(function (img) {
                    if (window.__dygitaLazyObserver) {
                        window.__dygitaLazyObserver.unobserve(img);
                    }
                    delete img.dataset.lazyBound;
                });
                // 注意：不断开全局 observer，因为它可能被其他页面使用
                // 清除 DOM 引用
                images = null;
            };
        }

        // Fallback for browsers without IntersectionObserver (IE11 and below)
        // 性能决策：为避免强制同步布局（Layout Thrashing），直接加载所有图片
        // IntersectionObserver 支持度：Chrome 51+, Firefox 55+, Safari 12.1+, Edge 15+
        // 老旧浏览器用户牺牲部分流量换取滚动流畅度
        images.forEach(function (img) {
            img.dataset.lazyBound = '1';
            loadImage(img);
        });

        // PJAX 内存泄漏修复：返回清理函数
        return function cleanup() {
            // 清除标记和 DOM 引用
            images.forEach(function (img) {
                delete img.dataset.lazyBound;
            });
            images = null;
        };
    }

    // PJAX 内存泄漏修复：全局清理函数存储
    var globalCleanupFunctions = {
        search: null,
        toc: null,
        lazyLoad: null
    };

    function initPageFeatures() {
        // 先清理旧的事件监听器和 DOM 引用
        cleanupPageFeatures();

        // 初始化各个功能，并存储清理函数
        globalCleanupFunctions.search = initSearch();
        globalCleanupFunctions.toc = initTocInteraction();
        globalCleanupFunctions.lazyLoad = initLazyLoad();
    }

    function cleanupPageFeatures() {
        // 调用所有清理函数
        Object.keys(globalCleanupFunctions).forEach(function(key) {
            var cleanup = globalCleanupFunctions[key];
            if (typeof cleanup === 'function') {
                try {
                    cleanup();
                } catch (e) {
                    // 忽略清理错误
                }
            }
            globalCleanupFunctions[key] = null;
        });
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

        // 暴露 showToast 到全局，供其他脚本使用
        window.DYGITA = window.DYGITA || {};
        window.DYGITA.showToast = showToast;
    });
})();