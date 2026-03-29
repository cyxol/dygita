<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div class="main-container">
<aside class="sidebar sidebar-left<?php echo ($this->is('post') || $this->is('page')) ? ' is-post' : ''; ?>">
    <button class="sidebar-toggle left" aria-label="折叠左侧栏" title="折叠左侧栏">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <div class="sidebar-sticky-content">

    <!-- 文章目录（文章页置顶 + sticky） -->
    <?php if ($this->is('post') || $this->is('page')): ?>
    <div class="widget widget-catalog">
        <div class="title">
            <h2><i class="fa fa-list"></i> <?php dygita_e('文章目录'); ?></h2>
        </div>
        <div class="widget-content">
            <?php
                $catalogCache = Dygita_Catalog_Cache::getCache($this->cid);
                if ($catalogCache !== null) {
                    $catalogHtml = $catalogCache['catalog'];
                } else {
                    $catalog = Dygita_ArticleCatalog::instance();
                    $catalogHtml = $catalog->renderCatalogHtml();
                }
                if (!empty($catalogHtml)) {
                    echo '<div class="catalog-content">' . $catalogHtml . '</div>';
                } else {
                    echo '<p class="no-catalog">' . dygita_t('本文没有目录') . '</p>';
                }
            ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- AI 问答 -->
    <?php
    $aichatIsPost   = ($this->is('post') || $this->is('page'));
    $aichatTitle    = $aichatIsPost ? (string) $this->title : '';
    $aichatLoggedIn = \Typecho\Widget::widget('Widget_User')->hasLogin();
    $aichatLogin    = rtrim((string) $this->options->siteUrl, '/') . '/admin/';
    $aichatRecentPosts = [];
    try {
        $db = \Typecho\Db::get();
        $rows = $db->fetchAll($db->select('cid', 'title')
            ->from(dygita_get_table('contents'))
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->order('created', \Typecho\Db::SORT_DESC)
            ->limit(8));
        if ($rows) {
            foreach ($rows as $row) {
                $aichatRecentPosts[] = ['cid' => (int)$row['cid'], 'title' => (string)$row['title']];
            }
        }
    } catch (\Exception $e) {}
    ?>
    <script>
    window.DYGITA = window.DYGITA || {};
    window.DYGITA.aiChat = {
        isPost:       <?php echo $aichatIsPost   ? 'true' : 'false'; ?>,
        postTitle:    <?php echo json_encode($aichatTitle,       JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
        isLoggedIn:   <?php echo $aichatLoggedIn ? 'true' : 'false'; ?>,
        loginUrl:     <?php echo json_encode($aichatLogin,       JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES); ?>,
        recentPosts:  <?php echo json_encode($aichatRecentPosts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>,
        apiUrl:       '',
        guestMaxAsks: 1
    };
    </script>
    <div class="widget widget-ai-chat">
        <div class="title"><h2><i class="fa fa-comments" aria-hidden="true"></i> <?php dygita_e('AI 智能体问答'); ?></h2></div>
        <div class="widget-content">
            <div class="ai-chat-messages" id="ai-chat-messages"
                 aria-live="polite" aria-label="<?php dygita_e('AI 对话'); ?>"></div>
            <div class="ai-chat-footer">
                <div class="ai-chat-input-wrap" id="ai-chat-input-wrap">
                    <input type="text" class="ai-chat-input" id="ai-chat-input"
                           placeholder="<?php dygita_e('问点什么...'); ?>"
                           maxlength="200" autocomplete="off" />
                    <button class="ai-chat-send" id="ai-chat-send" type="button"
                            aria-label="<?php dygita_e('发送'); ?>">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                    </button>
                </div>
                <p class="ai-chat-hint" id="ai-chat-login-hint" hidden></p>
            </div>
        </div>
    </div>

    <!-- 链接分享 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('链接分享'); ?></h2></div>
        <div class="widget-content">
            <ul class="link-list">
                <li><a href="https://huggingface.co/" target="_blank" rel="noopener" title="Hugging Face — AI 模型/数据集平台">🤗 Hugging Face</a></li>
                <li><a href="https://www.kaggle.com/" target="_blank" rel="noopener" title="Kaggle — 数据科学竞赛与数据集">📊 Kaggle</a></li>
                <li><a href="https://paperswithcode.com/" target="_blank" rel="noopener" title="Papers With Code — AI 论文与代码">📄 Papers With Code</a></li>
                <li><a href="https://towardsdatascience.com/" target="_blank" rel="noopener" title="Towards Data Science — 数据科学博客">✍️ Towards Data Science</a></li>
                <li><a href="https://www.fast.ai/" target="_blank" rel="noopener" title="fast.ai — 深度学习课程">🎓 fast.ai</a></li>
                <li><a href="https://openai.com/" target="_blank" rel="noopener" title="OpenAI — ChatGPT / GPT-4">🧠 OpenAI</a></li>
                <li><a href="https://www.anthropic.com/" target="_blank" rel="noopener" title="Anthropic — Claude AI">✦ Anthropic</a></li>
                <li><a href="https://ai.google/" target="_blank" rel="noopener" title="Google AI — Gemini / DeepMind">🔍 Google AI</a></li>
                <li><a href="https://www.datacamp.com/" target="_blank" rel="noopener" title="DataCamp — 数据科学在线学习">🏕️ DataCamp</a></li>
                <li><a href="https://www.analyticsvidhya.com/" target="_blank" rel="noopener" title="Analytics Vidhya — 数据分析社区">📈 Analytics Vidhya</a></li>
            </ul>
        </div>
    </div>
    </div><!-- /.sidebar-sticky-content -->
</aside>

<div class="content-wrap" role="main">
    <div class="content<?php echo isset($GLOBALS['dygita_content_class']) ? ' ' . htmlspecialchars($GLOBALS['dygita_content_class'], ENT_QUOTES, 'UTF-8') : ''; ?>">

