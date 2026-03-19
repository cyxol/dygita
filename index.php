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
$this->need('views/components/sidebar-left.php'); ?>
            <!-- 轮播图 -->
            <?php if ($this->options->swiperEnabled == '1'): ?>
                <div class="carousel">
                    <div class="swiper-container" id="swiper-home">
                        <div class="swiper-wrapper">
                            <?php
                            $swiperSlides = json_decode($this->options->swiperSlides, true);
                            $indexThemeBaseUrl = rtrim($this->options->themeUrl, '/');
                            $indexSiteBaseUrl = rtrim($this->options->siteUrl, '/');
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
                <?php $this->need('views/components/post-card.php'); ?>
            <?php endwhile; ?>
            <?php $this->pageNav('&laquo; ' . dygita_t('前一页'), dygita_t('后一页') . ' &raquo;'); ?>
<?php $this->need('views/components/sidebar-right.php');
$this->need('views/components/footer.php'); ?>