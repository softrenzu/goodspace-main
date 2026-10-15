<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width,initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content">本文へスキップ</a>
<header class="site-header" id="top">
  <div class="header-inner container">
    <?php gs_logo_html(); ?>
    <button type="button" class="nav-toggle" aria-label="メニューを開く" aria-expanded="false" aria-controls="primary-navigation"><span></span><span></span><span></span></button>
    <nav class="site-nav" id="primary-navigation" aria-label="メインナビゲーション">
      <?php wp_nav_menu(array('theme_location'=>'primary','container'=>false,'menu_class'=>'nav-list','fallback_cb'=>'gs_fallback_menu')); ?>
      <div class="mobile-blog-directory">
        <p class="mobile-menu-section-label">ブログを探す</p>
        <?php gs_blog_search_form('mobile-blog-search'); ?>
        <?php gs_blog_category_links('mobile-blog-categories'); ?>
      </div>
      <a class="header-contact" href="<?php echo gs_link('/contact/'); ?>">お問い合わせ <span aria-hidden="true">↗</span></a>
    </nav>
  </div>
</header>
