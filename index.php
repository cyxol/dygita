<?php

/**
 * Dygita Theme
 * 
 * @package Dygita
 * @author Yacine Tsai
 * @version 1.1.0
 * @link http://caiya.xin
 */

if (!defined('__TYPECHO_ROOT_DIR__')) exit;
$GLOBALS['dygita_content_class'] = 'index posts-expand';
$this->need('views/components/header.php');
$this->need('views/components/layout-start.php'); ?>
            <!-- 轮播图 -->
            <?php if ($this->options->swiperEnabled == '1'): ?>
                <div class="carousel">
                    <div class="swiper-container" id="swiper-home">
                        <div class="swiper-wrapper">
                            <?php
                            $swiperSlides = json_decode($this->options->swiperSlides, true);
                            ob_start(); $this->options->themeUrl(); $indexThemeBaseUrl = rtrim(ob_get_clean(), '/');
                            ob_start(); $this->options->siteUrl(); $indexSiteBaseUrl = rtrim(ob_get_clean(), '/');
                            if (isset($swiperSlides['slides']) && is_array($swiperSlides['slides'])) {
                                foreach ($swiperSlides['slides'] as $slide) {
                                    $image = isset($slide['image']) ? $slide['image'] : '';
                                    $title = isset($slide['title']) ? htmlspecialchars($slide['title'], ENT_QUOTES, 'UTF-8') : '';
                                    $link = isset($slide['link']) ? $slide['link'] : '';
                                    $imageUrl = strpos($image, 'http') === 0
                                        ? htmlspecialchars($image, ENT_QUOTES, 'UTF-8')
                                        : htmlspecialchars($indexThemeBaseUrl . '/' . ltrim($image, '/'), ENT_QUOTES, 'UTF-8');
                                    $linkUrl = strpos($link, 'http') === 0
                                        ? htmlspecialchars($link, ENT_QUOTES, 'UTF-8')
                                        : ($link ? htmlspecialchars($indexSiteBaseUrl . '/' . ltrim($link, '/'), ENT_QUOTES, 'UTF-8') : '#');
                                    echo '<div class="swiper-slide">';
                                    echo '<a href="' . $linkUrl . '" target="_blank">';
                                    echo '<img src="' . $imageUrl . '" alt="' . $title . '" />';
                                    echo '<div class="swiper-slide-title">' . $title . '</div>';
                                    echo '</a>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <div class="swiper-pagination"></div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- 文章列表 -->
            <?php while ($this->next()): ?>
                <article itemscope itemtype="http://schema.org/Article" class="post-block index">
                    <link itemprop="mainEntityOfPage" href="<?php $this->permalink(); ?>">
                    <span hidden itemprop="author" itemscope itemtype="http://schema.org/Person">
                        <meta itemprop="name" content="<?php $this->author(); ?>">
                    </span>
                    <span hidden itemprop="publisher" itemscope itemtype="http://schema.org/Organization">
                        <meta itemprop="name" content="<?php $this->options->title(); ?>">
                    </span>
                    <header class="post-header">
                        <h2 class="post-title" itemprop="name headline">
                            <a href="<?php $this->permalink(); ?>" class="post-title-link" itemprop="url"><?php $this->title(); ?></a>
                        </h2>
                    </header>
                    <div class="post-body" itemprop="articleBody">
                        <div class="thumb">
                            <a target="_blank" href="<?php $this->permalink(); ?>">
                                <img itemprop="contentUrl" class="random lazy" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-src="<?php echo getThumbnail($this); ?>" alt="<?php $this->title(); ?>" width="200" height="120" />
                            </a>
                        </div>
                        <div class="excerpt">
                            <p><?php $this->excerpt(140, '...'); ?></p>
                        </div>
                    </div>
                    <p class="auth-span">
                        <span class="muted"><i class="fa fa-user"></i> <a href="<?php $this->author->permalink(); ?>"><?php $this->author(); ?></a></span>
                        <span class="muted"><i class="fa fa-clock-o"></i> <?php $this->date('Y-m-d'); ?></span>
                        <span class="muted"><i class="fa fa-eye"></i> <?php dygita_e('浏览'); ?>(<?php getPostView($this); ?>)</span>
                        <span class="muted"><i class="fa fa-comments-o"></i> <a target="_blank" href="<?php $this->permalink(); ?>#comments"><?php $this->commentsNum(dygita_t('0评论'), dygita_t('1评论'), dygita_t('%d评论')); ?></a></span>
                        <span class="muted">
                            <a href="#" data-action="ding" data-id="<?php $this->cid(); ?>" class="Addlike action" role="button"><i class="fa fa-heart-o"></i><span class="count"><?php echo agreeNum($this->cid); ?></span><?php dygita_e('喜欢'); ?></a>
                        </span>
                    </p>
                </article>
            <?php endwhile; ?>
            <?php $this->pageNav('&laquo; ' . dygita_t('前一页'), dygita_t('后一页') . ' &raquo;'); ?>
<?php $this->need('views/components/layout-end.php');
$this->need('views/components/footer.php'); ?>