<?php
/** Good Space corporate theme: presentation only. AI functionality is in the plugin. */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/inc/blog-categories.php';

function gs_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    add_theme_support('responsive-embeds');
    add_theme_support('custom-logo', array('height'=>80,'width'=>240,'flex-height'=>true,'flex-width'=>true));
    register_nav_menus(array('primary'=>'ヘッダーナビゲーション', 'footer'=>'フッターナビゲーション'));
    add_image_size('gs-card', 720, 450, true);
}
add_action('after_setup_theme', 'gs_theme_setup');

function gs_assets() {
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('gs-site', get_stylesheet_uri(), array(), $ver);
    wp_enqueue_script('gs-menu', get_template_directory_uri().'/assets/menu.js', array(), $ver, true);
}
add_action('wp_enqueue_scripts','gs_assets');

function gs_blog_link() {
    $id = (int)get_option('page_for_posts');
    return $id ? get_permalink($id) : home_url('/blog/');
}
function gs_logo_html() {
    if (has_custom_logo()) { the_custom_logo(); return; }
    echo '<a class="brand" href="'.esc_url(home_url('/')).'" aria-label="Good Space ホーム">';
    echo '<span class="brand-symbol" aria-hidden="true"><span></span></span>';
    echo '<span class="brand-type"><strong>Good Space</strong><small>SPACE OPERATIONS & DESIGN</small></span></a>';
}
function gs_get_eyebrow($text) { return '<span class="eyebrow">'.esc_html($text).'</span>'; }
function gs_link($slug) { return esc_url(home_url('/'.ltrim($slug,'/'))); }
function gs_fallback_menu($args=array()) {
    echo '<ul class="nav-list">';
    $items = array(
      array('事業内容','/#services'),array('民泊運営代行','/service/minpaku/'),
      array('レンタルスペース','/service/rental-space/'),array('ブログ','/blog/'),array('会社概要','/company/')
    );
    foreach ($items as $item) {
      echo '<li><a href="'.esc_url(home_url($item[1])).'">'.esc_html($item[0]).'</a></li>';
    }
    echo '</ul>';
}
function gs_excerpt_length($length){ return 35; }
add_filter('excerpt_length','gs_excerpt_length',999);
function gs_excerpt_more($more){ return '…'; }
add_filter('excerpt_more','gs_excerpt_more');

// Minimal metadata. When a dedicated SEO plugin is enabled, disable this block to avoid duplicate meta tags.
function gs_head_metadata() {
    if (is_front_page()) {
        $description='空間の可能性を事業の価値へ。Good Spaceは民泊・レンタルスペースの立ち上げと運営、空室活用をサポートします。';
    } elseif (is_home()) {
        $description='民泊運営、レンタルスペース運営、空室活用の実務情報をお届けするGood Space Journal。';
    } elseif (is_singular()) {
        $id=get_queried_object_id();
        $description=has_excerpt($id) ? get_the_excerpt($id) : wp_trim_words(wp_strip_all_tags(get_post_field('post_content',$id)),50,'…');
        if ($description==='') { $description=get_the_title($id).' | Good Space'; }
    } else {
        $description='株式会社Good Space。民泊とレンタルスペースの運営支援、空間活用の情報をご案内します。';
    }
    echo '<meta name="description" content="'.esc_attr(wp_strip_all_tags($description)).'">' . "\n";
    echo '<meta property="og:type" content="'.(is_singular('post') ? 'article' : 'website').'">' . "\n";
    echo '<meta property="og:site_name" content="Good Space">' . "\n";
    if (is_front_page()) {
        $schema=array('@context'=>'https://schema.org','@type'=>'Organization','name'=>'株式会社Good Space',
            'url'=>home_url('/'), 'email'=>'info@good-space.jp', 'telephone'=>'+81-3-4500-2481',
            'address'=>array('@type'=>'PostalAddress','addressCountry'=>'JP','addressRegion'=>'東京都',
                'addressLocality'=>'新宿区','streetAddress'=>'大久保2丁目21-10-101号室'));
        echo '<script type="application/ld+json">'.wp_json_encode($schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG).'</script>' . "\n";
    }
}
add_action('wp_head','gs_head_metadata',1);

function gs_card($post_id=0) {
    $post_id = $post_id ?: get_the_ID();
    $title = get_the_title($post_id);
    $url = get_permalink($post_id);
    $terms = get_the_category($post_id);
    $category = !empty($terms) ? $terms[0]->name : 'INSIGHTS';
    ?>
    <article class="journal-card">
      <a class="journal-image" href="<?php echo esc_url($url); ?>" aria-label="<?php echo esc_attr($title); ?>">
        <?php if (has_post_thumbnail($post_id)): echo get_the_post_thumbnail($post_id, 'gs-card', array('loading'=>'lazy')); else: ?>
          <span class="journal-placeholder"><span>GOOD SPACE</span><i aria-hidden="true"></i></span>
        <?php endif; ?>
      </a>
      <div class="journal-meta"><span><?php echo esc_html($category); ?></span><time datetime="<?php echo esc_attr(get_the_date('c',$post_id)); ?>"><?php echo esc_html(get_the_date('Y.m.d',$post_id)); ?></time></div>
      <h3><a href="<?php echo esc_url($url); ?>"><?php echo esc_html($title); ?></a></h3>
      <p><?php echo esc_html(wp_trim_words(get_the_excerpt($post_id),55,'…')); ?></p>
      <a class="text-link" href="<?php echo esc_url($url); ?>">記事を読む <span aria-hidden="true">↗</span></a>
    </article>
    <?php
}
