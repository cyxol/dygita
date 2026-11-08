/**
 * 主题切换与顶栏颜色切换
 * 依赖: window.DYGITA.savedTheme, window.DYGITA.savedHeaderColor, window.DYGITA.config.hostname
 */
(function() {
    function setCookie(name, value, days) {
        var maxAge = Math.max(1, parseInt(days, 10) || 365) * 24 * 60 * 60;
        var cookie = encodeURIComponent(name) + '=' + encodeURIComponent(value)
            + '; path=/; max-age=' + String(maxAge) + '; SameSite=Lax';
        if (location.protocol === 'https:') cookie += '; Secure';
        document.cookie = cookie;
    }

    function safeStorage(method, key, value) {
        try {
            if (typeof localStorage === 'undefined') return null;
            if (method === 'get') return localStorage.getItem(key);
            if (method === 'set') localStorage.setItem(key, value);
        } catch (e) { return null; }
    }

    function dygitaSavePreference(type, value) {
        var config = (window.DYGITA && window.DYGITA.config) || {};
        if (!config.canSavePreference) return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', config.preferenceUrl, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.timeout = 5000;
        function failed() {
            if (window.DYGITA.showToast) window.DYGITA.showToast('本地设置已保存，站点默认设置同步失败', 'error');
        }
        xhr.onerror = failed;
        xhr.ontimeout = failed;
        xhr.onload = function() {
            if (xhr.status < 200 || xhr.status >= 300) { failed(); return; }
            try { if (!JSON.parse(xhr.responseText).ok) failed(); } catch (e) { failed(); }
        };
        xhr.send('type=' + encodeURIComponent(type) + '&value=' + encodeURIComponent(value)
            + '&_=' + encodeURIComponent(config.preferenceToken));
    }

    function initThemeToggle() {
        var themeToggle = document.getElementById('theme-toggle');
        if (!themeToggle) return;

        var savedTheme = (window.DYGITA && window.DYGITA.savedTheme ? window.DYGITA.savedTheme : null) || safeStorage('get', 'theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.innerHTML = '<i class="fa fa-sun-o"></i>';
        } else {
            document.documentElement.setAttribute('data-theme', 'light');
            themeToggle.innerHTML = '<i class="fa fa-moon-o"></i>';
        }

        themeToggle.addEventListener('click', function() {
            var currentTheme = document.documentElement.getAttribute('data-theme');
            var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', newTheme);
            if (newTheme === 'dark') {
                themeToggle.innerHTML = '<i class="fa fa-sun-o"></i>';
            } else {
                themeToggle.innerHTML = '<i class="fa fa-moon-o"></i>';
            }
            safeStorage('set', 'theme', newTheme);
            setCookie('dygita_theme_pref', newTheme, 365);
            dygitaSavePreference('theme', newTheme);
        });
    }

    function initColorToggle() {
        var colorToggle = document.getElementById('color-toggle');
        if (!colorToggle) return;

        var headerColors = ['#E74C3C', '#3498db', '#27ae60', '#f39c12', '#9b59b6', '#1abc9c'];
        var savedColor = (window.DYGITA && window.DYGITA.savedHeaderColor ? window.DYGITA.savedHeaderColor : null) || safeStorage('get', 'headerColor');
        if (savedColor) {
            document.documentElement.style.setProperty('--header-bg-color', savedColor);
        }
        var currentColorIndex = savedColor ? headerColors.indexOf(savedColor) : 0;
        if (currentColorIndex === -1) currentColorIndex = 0;

        colorToggle.addEventListener('click', function() {
            currentColorIndex = (currentColorIndex + 1) % headerColors.length;
            var newColor = headerColors[currentColorIndex];
            document.documentElement.style.setProperty('--header-bg-color', newColor);
            safeStorage('set', 'headerColor', newColor);
            setCookie('dygita_header_color', newColor, 365);
            dygitaSavePreference('headerColor', newColor);
        });
    }

    function initTagCloudColors() {
        var tagsColors = ['#00a67c', '#5cb85c', '#d9534f', '#567e95', '#b37333', '#f4843d', '#15a287'];
        var tagsElements = document.querySelectorAll('.tag-cloud a');
        tagsElements.forEach(function(item) {
            item.style.backgroundColor = tagsColors[Math.floor(Math.random() * tagsColors.length)];
        });
    }

    function init() {
        initThemeToggle();
        initColorToggle();
        initTagCloudColors();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
