<?php if (!defined('__TYPECHO_ROOT_DIR__')) exit; ?>
<div class="main-container">
<aside class="sidebar sidebar-left">
    <button class="sidebar-toggle left" aria-label="折叠左侧栏" title="折叠左侧栏">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </button>
    <!-- 博主简介 -->
    <div class="widget">
        <div class="title"><h2><?php dygita_e('关于博主'); ?></h2></div>
        <div class="widget-content">
            <div class="widget-profile">
                <div class="profile-avatar">
                    <img src="<?php $this->options->themeUrl('img/caiya.xin.jpg'); ?>" alt="Yacine Tsai">
                </div>
                <div class="profile-info">
                    <p>Yacine Tsai</p>
                    <p>大数据产品经理</p>
                    <p>Vibe Coding</p>
                    <?php $profileStat = dygita_get_stat(); ?>
                    <div class="profile-stats" aria-label="站点统计">
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['posts']; ?></span>
                            <span class="profile-stats-label"><?php dygita_e('日志'); ?></span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['categories']; ?></span>
                            <span class="profile-stats-label"><?php dygita_e('分类'); ?></span>
                        </div>
                        <div class="profile-stats-item">
                            <span class="profile-stats-num"><?php echo (int) $profileStat['tags']; ?></span>
                            <span class="profile-stats-label"><?php dygita_e('标签'); ?></span>
                        </div>
                    </div>
                </div>
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

    <!-- 文章目录 -->
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
</aside>

<div class="content-wrap" role="main">
    <div class="content<?php echo isset($GLOBALS['dygita_content_class']) ? ' ' . htmlspecialchars($GLOBALS['dygita_content_class'], ENT_QUOTES, 'UTF-8') : ''; ?>">

