/**
 * AI 智能体问答侧栏组件
 * 依赖: window.DYGITA.aiChat（由 sidebar-left.php 注入）
 */
(function () {
    'use strict';

    var initialized = false;
    var MIN_H = 200, MAX_H = 480;

    // ── 模型列表 ─────────────────────────────────────────────────────────────
    var MODELS = [
        { id: 'claude-opus-4-6',          label: 'Opus 4.6',  full: 'Claude Opus 4.6'          },
        { id: 'claude-sonnet-4-6',        label: 'Sonnet',    full: 'Claude Sonnet 4.6'        },
        { id: 'chatgpt-5.4',              label: 'GPT-5',     full: 'ChatGPT 5.4'              },
        { id: 'gemini-3.1-pro-preview',   label: 'Gemini',    full: 'Gemini 3.1 Pro Preview'   },
        { id: 'kimi-2.5',                 label: 'Kimi',      full: 'Kimi 2.5'                 },
        { id: 'minimax-2.7',              label: 'Minimax',   full: 'Minimax 2.7'              },
        { id: 'deepseek-3.2',             label: 'DeepSeek',  full: 'DeepSeek 3.2'             },
        { id: 'qwen3.5',                  label: 'Qwen',      full: 'Qwen 3.5'                 },
        { id: 'glm-5.0',                  label: 'GLM',       full: 'GLM 5.0'                  }
    ];
    var MODEL_KEY = 'dygita_ai_model_idx';

    function loadModelIdx() {
        try { var v = parseInt(localStorage.getItem(MODEL_KEY), 10); return isNaN(v) ? 0 : v % MODELS.length; }
        catch (e) { return 0; }
    }
    function saveModelIdx(i) {
        try { localStorage.setItem(MODEL_KEY, String(i)); } catch (e) {}
    }

    // ── Mock replies ─────────────────────────────────────────────────────────
    var MOCK_POST = [
        '关于《{title}》，您问到了「{q}」。文章从多角度对这一主题进行了分析，建议结合正文重点段落深入阅读，相信会有所帮助～',
        '「{q}」——好问题！结合《{title}》的内容，核心在于把握文章的论述主线。如果还有疑问，欢迎继续追问 😊',
        '针对「{q}」，《{title}》中有详细探讨，建议关注文章核心概念部分。有其他问题随时来问！'
    ];
    var MOCK_GENERIC = [
        '收到您的问题「{q}」！我目前以演示模式运行，真实 AI 接入后将为您提供精准解答 💬',
        '「{q}」是个有意思的问题！AI 完整功能即将上线，届时可以深入探讨 🚀',
        '您问到了「{q}」，值得细细思考！等正式接入后我会给您详细解答，敬请期待～'
    ];

    function pick(arr) { return arr[Math.floor(Math.random() * arr.length)]; }
    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;')
                        .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function init() {
        if (initialized) return;

        var cfg     = (window.DYGITA && window.DYGITA.aiChat) || {};
        var msgBox  = document.getElementById('ai-chat-messages');
        var input   = document.getElementById('ai-chat-input');
        var sendBtn = document.getElementById('ai-chat-send');
        var hintEl  = document.getElementById('ai-chat-login-hint');
        var inputWrap = document.getElementById('ai-chat-input-wrap');

        if (!msgBox || !input || !sendBtn || !inputWrap) return;
        initialized = true;

        var isLoggedIn   = !!cfg.isLoggedIn;
        var loginUrl     = escHtml(cfg.loginUrl || '#');
        var guestMaxAsks = typeof cfg.guestMaxAsks === 'number' ? cfg.guestMaxAsks : 1;
        var isPost       = !!cfg.isPost;
        var postTitle    = String(cfg.postTitle || '');
        var recentPosts  = Array.isArray(cfg.recentPosts) ? cfg.recentPosts : [];
        var STORE_KEY    = 'dygita_ai_guest_asks';

        var selectedTitle  = isPost ? postTitle : '';
        var currentModelIdx = loadModelIdx();

        // ── Guest quota ───────────────────────────────────────────────────────
        function guestCount() {
            try { return parseInt(localStorage.getItem(STORE_KEY) || '0', 10) || 0; } catch (e) { return 0; }
        }
        function bumpGuest() {
            try { localStorage.setItem(STORE_KEY, String(guestCount() + 1)); } catch (e) {}
        }
        function canAsk() { return isLoggedIn || guestCount() < guestMaxAsks; }

        // ── Dynamic height ────────────────────────────────────────────────────
        function growBox() {
            var sh  = msgBox.scrollHeight;
            var cur = msgBox.offsetHeight || MIN_H;
            if (sh > cur && cur < MAX_H) {
                msgBox.style.height = Math.min(sh, MAX_H) + 'px';
            }
            msgBox.scrollTop = msgBox.scrollHeight;
        }

        // ── DOM helpers ───────────────────────────────────────────────────────
        function bubble(text, role) {
            var d = document.createElement('div');
            d.className = 'ai-chat-bubble ' + role;
            d.textContent = text;
            msgBox.appendChild(d);
            growBox();
            return d;
        }
        function loadingBubble() {
            var d = document.createElement('div');
            d.className = 'ai-chat-bubble ai loading';
            d.innerHTML = '<span></span><span></span><span></span>';
            msgBox.appendChild(d);
            growBox();
            return d;
        }
        function showHint(html) {
            if (!hintEl) return; hintEl.innerHTML = html; hintEl.hidden = false;
        }
        function hideHint() {
            if (!hintEl) return; hintEl.hidden = true; hintEl.innerHTML = '';
        }

        // ── Mock reply ────────────────────────────────────────────────────────
        function mockReply(q) {
            var short = q.length > 28 ? q.slice(0, 28) + '…' : q;
            var base = selectedTitle
                ? pick(MOCK_POST).replace('{title}', selectedTitle).replace('{q}', short)
                : pick(MOCK_GENERIC).replace('{q}', short);
            return base;
        }

        // ── Model selector ────────────────────────────────────────────────────
        function buildModelSelector() {
            var wrap = document.createElement('div');
            wrap.className = 'ai-chat-model-wrap';

            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'ai-chat-model-btn';
            btn.id = 'ai-chat-model-btn';
            btn.title = MODELS[currentModelIdx].full;

            var lbl = document.createElement('span');
            lbl.className = 'ai-chat-model-label';
            lbl.textContent = MODELS[currentModelIdx].label;
            btn.appendChild(lbl);

            var chev = document.createElement('span');
            chev.className = 'ai-chat-model-chevron';
            chev.innerHTML = '&#8964;'; // ⌄
            btn.appendChild(chev);

            var dropdown = document.createElement('ul');
            dropdown.className = 'ai-chat-model-dropdown';
            dropdown.hidden = true;

            MODELS.forEach(function (m, i) {
                var li = document.createElement('li');
                li.className = 'ai-chat-model-item' + (i === currentModelIdx ? ' active' : '');
                li.textContent = m.full;
                li.addEventListener('click', function (e) {
                    e.stopPropagation();
                    currentModelIdx = i;
                    saveModelIdx(i);
                    lbl.textContent = m.label;
                    btn.title = m.full;
                    dropdown.querySelectorAll('.ai-chat-model-item').forEach(function (el, j) {
                        el.classList.toggle('active', j === i);
                    });
                    closeDropdown();
                });
                dropdown.appendChild(li);
            });

            wrap.appendChild(btn);
            wrap.appendChild(dropdown);

            function openDropdown() {
                dropdown.hidden = false;
                btn.classList.add('open');
            }
            function closeDropdown() {
                dropdown.hidden = true;
                btn.classList.remove('open');
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (dropdown.hidden) openDropdown(); else closeDropdown();
            });

            // 点击外部关闭
            document.addEventListener('click', function () { closeDropdown(); });

            // 插入到 sendBtn 之前
            inputWrap.insertBefore(wrap, sendBtn);
        }

        buildModelSelector();

        // ── Skill bar（首页文章选择）─────────────────────────────────────────
        function buildSkillBar() {
            if (isPost || !recentPosts.length) return;

            var bar = document.createElement('div');
            bar.className = 'ai-chat-skill-bar';

            var label = document.createElement('span');
            label.className = 'ai-chat-skill-label';
            label.textContent = '选一篇文章问我：';
            bar.appendChild(label);

            var list = document.createElement('div');
            list.className = 'ai-chat-skill-list';
            bar.appendChild(list);

            var activeChip = null;

            recentPosts.forEach(function (post) {
                var chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'ai-chat-skill-chip';
                chip.title = post.title;
                chip.textContent = post.title;
                chip.addEventListener('click', function () {
                    if (activeChip) activeChip.classList.remove('active');
                    if (activeChip === chip) {
                        activeChip = null;
                        selectedTitle = '';
                        input.placeholder = '问点什么...';
                        bubble('已切换到通用问答模式 📋', 'ai');
                    } else {
                        activeChip = chip;
                        chip.classList.add('active');
                        selectedTitle = post.title;
                        var shortT = post.title.length > 12 ? post.title.slice(0, 12) + '…' : post.title;
                        input.placeholder = '关于《' + shortT + '》问我...';
                        bubble('已选择《' + post.title + '》，请提问 ✦', 'ai');
                    }
                });
                list.appendChild(chip);
            });

            var footer = msgBox.nextElementSibling;
            if (footer) footer.parentNode.insertBefore(bar, footer);
        }

        // ── Send ──────────────────────────────────────────────────────────────
        function send() {
            var text = input.value.trim();
            if (!text) return;
            if (!canAsk()) {
                showHint('再来一条？请先 <a href="' + loginUrl + '">登录</a> 继续提问 ✨');
                return;
            }
            hideHint();
            input.value = '';
            sendBtn.disabled = true;
            input.disabled = true;

            bubble(text, 'user');
            if (!isLoggedIn) bumpGuest();

            var spin = loadingBubble();
            setTimeout(function () {
                if (spin.parentNode) msgBox.removeChild(spin);
                bubble(mockReply(text), 'ai');
                sendBtn.disabled = false;
                input.disabled = false;
                input.focus();
                if (!isLoggedIn && !canAsk()) {
                    showHint('已用完免费提问次数，<a href="' + loginUrl + '">登录</a> 后畅享无限问答 🌟');
                }
            }, 800 + Math.random() * 700);
        }

        sendBtn.addEventListener('click', send);
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); send(); }
        });

        // 欢迎语 + skill bar
        var ctx = (isPost && postTitle) ? '《' + postTitle + '》' : '博客';
        bubble('你好！我是 AI 智能体，有关 ' + ctx + ' 的问题，尽管问我 😊', 'ai');
        buildSkillBar();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
