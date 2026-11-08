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

            var likeUrl = window.DYGITA && window.DYGITA.config && window.DYGITA.config.likeUrl
                ? window.DYGITA.config.likeUrl
                : '/action/dygita-like';

            // 修复经常出现的 CORS (跨域) 和 HTTPS 301重定向（导致 POST 丢失参数变 GET 的问题）
            // 确保点赞请求的域名和协议与当前浏览器地址栏保持绝对一致！
            if (likeUrl.indexOf('http') === 0) {
                try {
                    var parsed = new URL(likeUrl);
                    parsed.protocol = window.location.protocol;
                    parsed.host = window.location.host;
                    likeUrl = parsed.href;
                } catch (e) {
                    // ignore
                }
            }

            fetch(likeUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: 'cid=' + encodeURIComponent(cid)
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
        var overlay = null;
        var input = null;
        var result = null;
        var timer = null;
        var controller = null;
        var sequence = 0;
        var cleanup = [];
        function listen(node, event, fn) {
            node.addEventListener(event, fn);
            cleanup.push(function() { node.removeEventListener(event, fn); });
        }
        function close() { if (overlay) overlay.classList.remove('search-active'); }
        function search() {
            var query = input.value.trim();
            var current = ++sequence;
            if (controller) controller.abort();
            if (!query) { result.textContent = '输入关键词搜索文章'; return; }
            controller = new AbortController();
            var url = new URL((window.DYGITA && window.DYGITA.searchIndexUrl) || '/action/dygita-search-index', window.location.origin);
            url.protocol = window.location.protocol;
            url.host = window.location.host;
            url.searchParams.set('q', query);
            result.textContent = '正在搜索…';
            fetch(url.href, { credentials: 'same-origin', cache: 'no-store', signal: controller.signal })
                .then(function(response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                }).then(function(rows) {
                    if (current !== sequence || !result) return;
                    if (!Array.isArray(rows)) throw new Error('Invalid response');
                    if (!rows.length) { result.textContent = '未找到相关文章，按回车尝试完整搜索。'; return; }
                    result.innerHTML = rows.slice(0, 8).map(function(row) {
                        var link = new URL(row.url, window.location.origin);
                        if (link.protocol !== 'http:' && link.protocol !== 'https:') return '';
                        return '<p class="search-result"><a href="' + escapeHtml(link.href) + '">'
                            + '<strong>' + escapeHtml(row.title) + '</strong><br>'
                            + '<small>' + escapeHtml(row.date) + '</small><br>'
                            + '<span>' + escapeHtml(row.excerpt) + '</span></a></p>';
                    }).join('');
                }).catch(function(error) {
                    if (error.name === 'AbortError' || current !== sequence || !result) return;
                    result.textContent = '搜索暂时不可用，请重试或按回车使用完整搜索。';
                });
        }
        function open(event) {
            event.preventDefault();
            if (!overlay) {
                var template = document.getElementById('tpl-search-popup');
                if (!template) return;
                overlay = document.createElement('div');
                overlay.className = 'search-pop-overlay';
                var popup = document.createElement('div');
                popup.className = 'search-popup';
                popup.appendChild(template.content.cloneNode(true));
                overlay.appendChild(popup);
                document.body.appendChild(overlay);
                input = overlay.querySelector('.search-input');
                result = overlay.querySelector('#search-result');
                listen(overlay.querySelector('.popup-btn-close'), 'click', close);
                listen(overlay, 'click', function(e) { if (e.target === overlay) close(); });
                listen(input, 'input', function() {
                    // Immediately invalidate an older request, including during debounce.
                    sequence++;
                    if (controller) controller.abort();
                    clearTimeout(timer);
                    timer = setTimeout(search, 200);
                });
            }
            overlay.classList.add('search-active');
            input.focus();
            search();
        }
        var triggers = [document.getElementById('search-trigger-nav'), document.getElementById('search-trigger'), document.querySelector('.search-toggle')];
        triggers.filter(function(node, index) { return node && triggers.indexOf(node) === index; })
            .forEach(function(node) { listen(node, 'click', open); });
        listen(document, 'keydown', function(e) { if (e.key === 'Escape') close(); });
        return function() {
            sequence++;
            clearTimeout(timer);
            if (controller) controller.abort();
            cleanup.forEach(function(fn) { fn(); });
            if (overlay) overlay.remove();
            overlay = input = result = null;
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

        // 侧栏 sticky 控制：文章内容结束后取消 sticky，让侧栏随页面滚走
        var sidebarLeft = document.querySelector('.sidebar-left');
        var sidebarStickyContent = document.querySelector('.sidebar-left .sidebar-sticky-content');
        var articleFooter = document.querySelector('.article-footer');
        var stickyObserver = null;

        if (sidebarLeft && sidebarStickyContent && articleFooter && 'IntersectionObserver' in window) {
            stickyObserver = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        // 文章读完：计算侧栏内容当前位置，切换为 absolute 保持原位然后随页面滚走
                        var sidebarRect = sidebarLeft.getBoundingClientRect();
                        var contentRect = sidebarStickyContent.getBoundingClientRect();
                        var absoluteTop = contentRect.top - sidebarRect.top;
                        // 如果 absoluteTop 为负或非常小，说明可能还未真正 sticky，但一般已生效
                        sidebarLeft.style.position = 'relative';
                        sidebarStickyContent.style.position = 'absolute';
                        sidebarStickyContent.style.top = Math.max(0, absoluteTop) + 'px';
                        sidebarStickyContent.style.width = contentRect.width + 'px';
                    } else {
                        // 回到文章区域：恢复 sticky
                        sidebarStickyContent.style.position = '';
                        sidebarStickyContent.style.top = '';
                        sidebarStickyContent.style.width = '';
                        sidebarLeft.style.position = '';
                    }
                });
            }, {
                root: null,
                rootMargin: '0px 0px 0px 0px',
                threshold: 0
            });
            stickyObserver.observe(articleFooter);
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
                if (stickyObserver) {
                    stickyObserver.disconnect();
                    stickyObserver = null;
                }
                if (sidebarStickyContent) {
                    sidebarStickyContent.style.position = '';
                    sidebarStickyContent.style.top = '';
                    sidebarStickyContent.style.width = '';
                }
                if (sidebarLeft) {
                    sidebarLeft.style.position = '';
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
                    // Fallback sticky 控制
                    if (sidebarLeft && sidebarStickyContent && articleFooter) {
                        var footerTop = articleFooter.getBoundingClientRect().top;
                        if (footerTop <= window.innerHeight) {
                            if (!sidebarStickyContent.style.position) {
                                var sRect = sidebarLeft.getBoundingClientRect();
                                var cRect = sidebarStickyContent.getBoundingClientRect();
                                sidebarLeft.style.position = 'relative';
                                sidebarStickyContent.style.position = 'absolute';
                                sidebarStickyContent.style.top = Math.max(0, cRect.top - sRect.top) + 'px';
                                sidebarStickyContent.style.width = cRect.width + 'px';
                            }
                        } else {
                            sidebarLeft.style.position = '';
                            sidebarStickyContent.style.position = '';
                            sidebarStickyContent.style.top = '';
                            sidebarStickyContent.style.width = '';
                        }
                    }
                    ticking = false;
                });
            }
            window.addEventListener('scroll', onScrollFallback, { passive: true });

            // PJAX 内存泄漏修复：返回清理函数
            return function cleanup() {
                catalogContent.removeEventListener('click', onCatalogClick);
                window.removeEventListener('scroll', onScrollFallback);
                if (sidebarStickyContent) {
                    sidebarStickyContent.style.position = '';
                    sidebarStickyContent.style.top = '';
                    sidebarStickyContent.style.width = '';
                }
                if (sidebarLeft) {
                    sidebarLeft.style.position = '';
                }
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