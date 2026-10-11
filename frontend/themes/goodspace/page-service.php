<?php
/** Template Name: サービス概要 */
get_header(); ?>
<main id="content" class="inner-main"><section class="page-hero"><div class="container"><span class="eyebrow">SERVICES</span><h1>事業内容</h1><p>空間の可能性を広げるための運営支援。</p></div></section><section class="section"><div class="container"><div class="service-tiles">
<a class="service-tile" href="<?php echo gs_link('/service/minpaku/'); ?>"><div class="tile-art tile-minpaku"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/room-stay.svg'); ?>" alt="民泊の室内イラスト"></div><div class="tile-content"><span class="eyebrow">01 / HOSPITALITY</span><h2>民泊運営代行</h2><p>立ち上げから日々の運営まで。</p><span class="tile-link">詳しく見る ↗</span></div></a>
<a class="service-tile" href="<?php echo gs_link('/service/rental-space/'); ?>"><div class="tile-art tile-rental"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/room-meet.svg'); ?>" alt="レンタルスペースの室内イラスト"></div><div class="tile-content"><span class="eyebrow">02 / SPACE MANAGEMENT</span><h2>レンタルスペース運営</h2><p>空室の活用から集客・管理の検討まで。</p><span class="tile-link">詳しく見る ↗</span></div></a>
</div></div></section></main>
<?php get_footer(); ?>
