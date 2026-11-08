<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

function themeConfig($form)
{
    // 获取全局配置（注意：在函数上下文中不能使用 $this->options）
    $options = Typecho\Widget::widget('Widget_Options');
    // 侧边栏显示控制
    $sidebarBlock = new Typecho\Widget\Helper\Form\Element\Checkbox('sidebarBlock',
        array(
        'ShowCategory' => _t('显示分类'),
        ),
        array('ShowCategory'), _t('侧边栏显示'));

    $form->addInput($sidebarBlock->multiMode());

    // 主题色 dygita_skin_b（兼容 git_skin_b）
    $skinCur = dygita_opt($options, 'dygita_skin_b', 'git_skin_b') ?: 'git_red_b';
    $dygita_skin_b = new Typecho\Widget\Helper\Form\Element\Radio('dygita_skin_b',
        array(
        'git_red_b' => _t('红色'),
        'git_blue_b' => _t('蓝色'),
        'git_black_b' => _t('黑色'),
        'git_purple_b' => _t('紫色'),
        'git_yellow_b' => _t('黄色'),
        'git_light_b' => _t('浅蓝'),
        'git_green_b' => _t('绿色'),
    ),
        $skinCur, _t('主题配色'), _t('选择主题的配色方案')
        );
    $form->addInput($dygita_skin_b);

    // 深色模式支持
    $colorSchema = new Typecho\Widget\Helper\Form\Element\Select(
        'colorSchema',
        array(
            null => _t('自动'),
            'light' => _t('浅色'),
            'dark' => _t('深色'),
        ),
        null,
        _t('外观风格'),
        _t('如果选择了自动，主题将根据系统设置自动切换浅色/深色模式')
    );
    $form->addInput($colorSchema);

    // Swiper.js 幻灯片配置
    $swiperEnabled = new Typecho\Widget\Helper\Form\Element\Radio('swiperEnabled',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('开启Swiper幻灯片'), _t('是否开启首页Swiper幻灯片')
        );
    $form->addInput($swiperEnabled);
    
    $swiperSlides = new Typecho\Widget\Helper\Form\Element\Textarea('swiperSlides', NULL, '{"slides":[{"image":"img/pic/1.jpg","title":"欢迎访问","link":""},{"image":"img/pic/2.jpg","title":"现代设计","link":""},{"image":"img/pic/3.jpg","title":"响应式布局","link":""},{"image":"img/pic/4.jpg","title":"技术创新","link":""},{"image":"img/pic/5.jpg","title":"创意设计","link":""}]}', _t('幻灯片配置'), _t('JSON格式，包含图片路径、标题和链接，例如：{"slides":[{"image":"img/pic/1.jpg","title":"标题1","link":"链接1"}]}'));
    $form->addInput($swiperSlides);
    
    $swiperAutoplay = new Typecho\Widget\Helper\Form\Element\Radio('swiperAutoplay',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('自动播放'), _t('是否开启幻灯片自动播放')
        );
    $form->addInput($swiperAutoplay);
    
    $swiperSpeed = new Typecho\Widget\Helper\Form\Element\Text('swiperSpeed', NULL, '1000', _t('切换速度'), _t('幻灯片切换速度，单位毫秒，默认1000'));
    $form->addInput($swiperSpeed);
    
    $swiperDelay = new Typecho\Widget\Helper\Form\Element\Text('swiperDelay', NULL, '3000', _t('自动播放延迟'), _t('幻灯片自动播放延迟时间，单位毫秒，默认3000'));
    $form->addInput($swiperDelay);
    
    // 导航栏配置
    $navLinksEnabled = new Typecho\Widget\Helper\Form\Element\Radio('navLinksEnabled',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '1', _t('开启自定义导航链接'), _t('是否开启自定义导航链接')
        );
    $form->addInput($navLinksEnabled);
    
    $navLinks = new Typecho\Widget\Helper\Form\Element\Textarea('navLinks', NULL, '{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"},{"name":"标签云","url":"tag","target":"_self"}]}', _t('导航链接配置'), _t('JSON格式，包含导航链接名称、URL和目标。特殊URL：@author 自动检测作者页面。例如：{"links":[{"name":"首页","url":"","target":"_self"},{"name":"作者","url":"@author","target":"_self"}]}'));
    $form->addInput($navLinks);
    
    // 友情链接设置
    $links = new Typecho\Widget\Helper\Form\Element\Textarea('links', NULL, NULL, _t('友情链接'), _t('格式：名称|链接|描述（可选），每行一个'));
    $form->addInput($links);
    
    // 评论系统设置
    $commentSystem = new Typecho\Widget\Helper\Form\Element\Select(
        'commentSystem',
        array(
            'default' => _t('默认评论系统'),
            'gitalk' => _t('Gitalk'),
            'valine' => _t('Valine'),
            'disqus' => _t('Disqus')
        ),
        'default',
        _t('评论系统'),
        _t('选择网站使用的评论系统')
    );
    $form->addInput($commentSystem);
    
    // Gitalk 配置
    $gitalkClientID = new Typecho\Widget\Helper\Form\Element\Text('gitalkClientID', NULL, NULL, _t('Gitalk Client ID'), _t('GitHub OAuth Application Client ID'));
    $form->addInput($gitalkClientID);
    
    $gitalkClientSecret = new Typecho\Widget\Helper\Form\Element\Text('gitalkClientSecret', NULL, NULL, _t('Gitalk Client Secret'), _t('GitHub OAuth Application Client Secret'));
    $form->addInput($gitalkClientSecret);
    
    $gitalkRepo = new Typecho\Widget\Helper\Form\Element\Text('gitalkRepo', NULL, NULL, _t('Gitalk Repo'), _t('GitHub 仓库名'));
    $form->addInput($gitalkRepo);
    
    $gitalkOwner = new Typecho\Widget\Helper\Form\Element\Text('gitalkOwner', NULL, NULL, _t('Gitalk Owner'), _t('GitHub 用户名'));
    $form->addInput($gitalkOwner);
    
    // Valine 配置
    $valineAppId = new Typecho\Widget\Helper\Form\Element\Text('valineAppId', NULL, NULL, _t('Valine App ID'), _t('LeanCloud Application ID'));
    $form->addInput($valineAppId);
    
    $valineAppKey = new Typecho\Widget\Helper\Form\Element\Text('valineAppKey', NULL, NULL, _t('Valine App Key'), _t('LeanCloud Application Key'));
    $form->addInput($valineAppKey);
    
    $valineAvatar = new Typecho\Widget\Helper\Form\Element\Text('valineAvatar', NULL, 'mp', _t('Valine Avatar'), _t('评论者头像样式，可选值：mp, identicon, monsterid, wavatar, robohash, retro, hide'));
    $form->addInput($valineAvatar);
    
    // Disqus 配置
    $disqusShortname = new Typecho\Widget\Helper\Form\Element\Text('disqusShortname', NULL, NULL, _t('Disqus Shortname'), _t('Disqus 站点标识符'));
    $form->addInput($disqusShortname);
    
    // 站点统计配置
    $baiduAnalytics = new Typecho\Widget\Helper\Form\Element\Text('baiduAnalytics', NULL, NULL, _t('百度统计代码'), _t('百度统计的站点ID，例如：8a7b6c5d4e3f2g1h'));
    $form->addInput($baiduAnalytics);
    
    $googleAnalytics = new Typecho\Widget\Helper\Form\Element\Text('googleAnalytics', NULL, NULL, _t('Google Analytics 代码'), _t('Google Analytics 的跟踪ID，例如：UA-12345678-9'));
    $form->addInput($googleAnalytics);
    
    $enableStatistics = new Typecho\Widget\Helper\Form\Element\Radio('enableStatistics',
        array('0' => _t('关闭'), '1' => _t('开启')),
        '0', _t('开启站点统计'), _t('是否开启站点访问统计功能')
    );
    $form->addInput($enableStatistics);

    // 页脚版权（留空则显示：© 年份 站点标题）
    $copyright = new Typecho\Widget\Helper\Form\Element\Textarea('copyright', NULL, NULL, _t('页脚版权信息'), _t('留空则显示默认：© 年份 + 站点标题。可填自定义版权文字（纯文本）'));
    $form->addInput($copyright);

    // 博主信息配置
    $authorAvatar = new Typecho\Widget\Helper\Form\Element\Text('authorAvatar', NULL, NULL, _t('侧边栏及关于页头像URL'), _t('仅用于侧边栏和关于博主页面，标题栏不显示头像。留空使用主题默认头像 img/caiya.xin.jpg'));
    $form->addInput($authorAvatar);

    $authorName = new Typecho\Widget\Helper\Form\Element\Text('authorName', NULL, 'Yacine Tsai', _t('博主昵称'), _t('显示在侧边栏和关于页面的昵称，留空使用站点标题'));
    $form->addInput($authorName);

    $authorTitle = new Typecho\Widget\Helper\Form\Element\Text('authorTitle', NULL, '大数据产品经理', _t('博主职位/标语'), _t('显示在侧边栏简介中的一行描述'));
    $form->addInput($authorTitle);

    $authorBio = new Typecho\Widget\Helper\Form\Element\Textarea('authorBio', NULL, "大数据AI产品经理\n来自河南，现居南京，就职奥派\n爱生活、爱音乐、爱打羽毛球\n爱爬山、爱台球、爱世间万物\n热爱可抵岁月漫长\n乘风破浪奔赴山海\n俯首高调细心做事\n昂首低调宽心做人\n心怀猛虎 细嗅蔷薇", _t('博主个人简介'), _t('每行一条，显示在关于页面'));
    $form->addInput($authorBio);

    $gravatarProfile = new Typecho\Widget\Helper\Form\Element\Text('gravatarProfile', NULL, 'https://gravatar.com/exuberant3c83335dc7', _t('Gravatar 个人主页'), _t('Gravatar rel=me 链接，留空则不输出'));
    $form->addInput($gravatarProfile);

    $resourceLinks = new Typecho\Widget\Helper\Form\Element\Textarea('resourceLinks', NULL, "🤗 Hugging Face|https://huggingface.co/|AI 模型/数据集平台\n📊 Kaggle|https://www.kaggle.com/|数据科学竞赛与数据集\n📄 Papers With Code|https://paperswithcode.com/|AI 论文与代码\n✍️ Towards Data Science|https://towardsdatascience.com/|数据科学博客\n🎓 fast.ai|https://www.fast.ai/|深度学习课程\n🧠 OpenAI|https://openai.com/|ChatGPT / GPT-4\n✦ Anthropic|https://www.anthropic.com/|Claude AI\n🔍 Google AI|https://ai.google/|Gemini / DeepMind\n🏕️ DataCamp|https://www.datacamp.com/|数据科学在线学习\n📈 Analytics Vidhya|https://www.analyticsvidhya.com/|数据分析社区", _t('侧边栏资源链接'), _t('左侧栏链接分享区域，格式：名称|链接|描述，每行一个'));
    $form->addInput($resourceLinks);
    
    // 投稿功能设置（dygita_* 兼容 git_*）
    $tougaoCur = dygita_opt($options, 'dygita_tougao_b', 'git_tougao_b') ?: '0';
    $dygita_tougao_b = new Typecho\Widget\Helper\Form\Element\Radio('dygita_tougao_b',
        array('0' => _t('关闭'), '1' => _t('开启')),
        $tougaoCur, _t('开启投稿功能'), _t('是否允许访客通过投稿页面提交文章（需创建投稿页面并选择投稿模板）')
    );
    $form->addInput($dygita_tougao_b);
    
    $mailtoCur = dygita_opt($options, 'dygita_tougao_mailto', 'git_tougao_mailto');
    $dygita_tougao_mailto = new Typecho\Widget\Helper\Form\Element\Text('dygita_tougao_mailto', NULL, $mailtoCur, _t('投稿通知邮箱'), _t('有新投稿时发送通知到此邮箱，留空则不发送'));
    $form->addInput($dygita_tougao_mailto);
    
    // 下载页设置
    $dlCur = dygita_opt($options, 'dygita_dlpage_dl', 'git_dlpage_dl');
    $dygita_dlpage_dl = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_dlpage_dl', NULL, $dlCur !== null ? $dlCur : _t('请确认您已知悉下载须知后再进行下载。'), _t('下载说明'), _t('在下载页面显示的下载说明文字'));
    $form->addInput($dygita_dlpage_dl);
    
    $mzCur = dygita_opt($options, 'dygita_dlpage_mz', 'git_dlpage_mz');
    $dygita_dlpage_mz = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_dlpage_mz', NULL, $mzCur !== null ? $mzCur : _t('本站资源仅供学习交流，请于下载后24小时内删除。'), _t('免责声明'), _t('在下载页面显示的免责声明文字'));
    $form->addInput($dygita_dlpage_mz);
    
    $ad1Cur = dygita_opt($options, 'dygita_downloadad1', 'git_downloadad1');
    $dygita_downloadad1 = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_downloadad1', NULL, $ad1Cur, _t('下载页顶部广告'), _t('在下载页面文章上方显示的广告代码'));
    $form->addInput($dygita_downloadad1);
    
    $ad2Cur = dygita_opt($options, 'dygita_downloadad2', 'git_downloadad2');
    $dygita_downloadad2 = new Typecho\Widget\Helper\Form\Element\Textarea('dygita_downloadad2', NULL, $ad2Cur, _t('下载页底部广告'), _t('在下载页面底部显示的广告代码'));
    $form->addInput($dygita_downloadad2);
    
    // 第三方 CDN 配置（解决 jsdelivr/unpkg 国内访问问题）
    $cdnProviderCur = dygita_opt($options, 'dygita_cdn_provider', 'git_cdn_provider') ?: 'jsdelivr';
    $dygita_cdn_provider = new Typecho\Widget\Helper\Form\Element\Select(
        'dygita_cdn_provider',
        array(
            'jsdelivr' => _t('jsdelivr + unpkg 回退'),
            'staticfile' => _t('七牛云 Staticfile CDN'),
            'bootcdn' => _t('BootCDN'),
            'cdnjs' => _t('Cloudflare CDNJS'),
            'local' => _t('本地资源（需手动下载到主题目录）')
        ),
        $cdnProviderCur,
        _t('第三方 CDN 提供商'),
        _t('选择 Swiper、PrismJS、Gitalk、Valine 等第三方库的 CDN 来源。如选择本地资源，需手动下载文件到主题 /vendor/ 目录')
    );
    $form->addInput($dygita_cdn_provider);
}

function themeFields($layout) {
    $thumb = new \Typecho\Widget\Helper\Form\Element\Text(
        'thumb',
        null,
        null,
        _t('文章缩略图'),
        _t('在这里填入一个图片URL地址, 作为文章的缩略图')
    );
    $layout->addItem($thumb);

    $excerpt = new \Typecho\Widget\Helper\Form\Element\Textarea(
        'excerpt',
        null,
        null,
        _t('文章摘要'),
        _t('在这里填入文章摘要, 会显示在文章列表页')
    );
    $layout->addItem($excerpt);
}
