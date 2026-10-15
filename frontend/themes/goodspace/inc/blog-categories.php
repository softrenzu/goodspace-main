<?php
/**
 * Good Space Journal directory. Slugs are stable so links survive title edits.
 */
if (!defined('ABSPATH')) { exit; }

function gs_blog_category_definitions() {
    return array(
        'minpaku-start' => array('name'=>'民泊の始め方', 'description'=>'民泊の検討から開業準備まで、最初に知っておきたいこと。'),
        'operations' => array('name'=>'運営ノウハウ', 'description'=>'予約対応、清掃、設備管理など日々の運営を効率よく。'),
        'rental-space' => array('name'=>'レンタルスペース', 'description'=>'貸会議室・撮影スタジオなど、スペース運営のヒント。'),
        'real-estate' => array('name'=>'不動産・空室活用', 'description'=>'物件選びや空室・遊休スペースの活用アイデア。'),
        'booking-marketing' => array('name'=>'集客・予約サイト', 'description'=>'Airbnbなどの予約サイト掲載や集客の改善。'),
        'laws' => array('name'=>'法令・許認可', 'description'=>'旅館業法や住宅宿泊事業法など、一次情報を確認する入口。'),
        'inbound' => array('name'=>'インバウンド', 'description'=>'訪日旅行者に向けた案内や受入れ環境づくり。'),
        'news' => array('name'=>'Good Spaceのお知らせ', 'description'=>'サービスや取り組みに関する最新情報。'),
    );
}

function gs_blog_seed_categories() {
    // Fast path after first success; explicit CLI can call this again.
    if (get_option('gs_blog_categories_version') === '2026-10-v1') { return; }
    foreach (gs_blog_category_definitions() as $slug=>$info) {
        if (term_exists($slug,'category')) { continue; }
        $created=wp_insert_term($info['name'],'category',array(
            'slug'=>$slug,'description'=>$info['description'],
        ));
        if (is_wp_error($created)) { return; }
    }
    update_option('gs_blog_categories_version','2026-10-v1',false);
}
add_action('init','gs_blog_seed_categories',20);

function gs_blog_category_items() {
    $items=array();
    foreach (gs_blog_category_definitions() as $slug=>$info) {
        $term=get_category_by_slug($slug);
        if (!$term) { continue; }
        $link=get_term_link($term);
        if (is_wp_error($link)) { continue; }
        $items[]=array('slug'=>$slug,'name'=>$term->name,
                       'description'=>$info['description'],
                       'count'=>(int)$term->count,'url'=>$link,'id'=>(int)$term->term_id);
    }
    return $items;
}

function gs_blog_category_slug_for($text) {
    $text=(string)$text;
    // Order is deliberate: more-specific groups should win before generic "民泊".
    if (preg_match('/旅館業法|民泊新法|住宅宿泊事業法|特区民泊|許認可|条例|届出|法令|消防法|保健所/u',$text)) { return 'laws'; }
    if (preg_match('/インバウンド|訪日|外国人観光|多言語/u',$text)) { return 'inbound'; }
    if (preg_match('/Airbnb|Booking\.com|OTA|予約サイト|掲載|集客|検索順位|広告|集客導線|施設写真|撮影/u',$text)) { return 'booking-marketing'; }
    if (preg_match('/レンタルスペース|貸会議室|撮影スタジオ|ダンススタジオ|時間貸し/u',$text)) { return 'rental-space'; }
    if (preg_match('/空室|空き家|空き部屋|不動産|物件選定|立地|用途企画|遊休/u',$text)) { return 'real-estate'; }
    if (preg_match('/民泊.*(始め|開始|開業|準備|立ち上げ)|民泊の始め方|民泊開業/u',$text)) { return 'minpaku-start'; }
    if (preg_match('/Good Space.*(お知らせ|ニュース)|サイト公開|ブログ開設|プレスリリース/u',$text)) { return 'news'; }
    return 'operations';
}

/** Render a reusable menu, with each category leading to a filtered archive. */
function gs_blog_category_links($class='blog-category-list', $show_all=true) {
    $active=is_category() ? get_queried_object_id() : 0;
    echo '<nav class="'.esc_attr($class).'" aria-label="ブログカテゴリ">';
    if ($show_all) {
        echo '<a href="'.esc_url(gs_blog_link()).'"'.(!$active && !is_search() ? ' aria-current="page"' : '').'>すべての記事 <span aria-hidden="true">→</span></a>';
    }
    foreach (gs_blog_category_items() as $item) {
        $current=$active===$item['id'] ? ' aria-current="page"' : '';
        echo '<a href="'.esc_url($item['url']).'"'.$current.'>'.esc_html($item['name']).'<span aria-hidden="true">→</span></a>';
    }
    echo '</nav>';
}

function gs_blog_search_form($class='blog-search-form') {
    echo '<form role="search" method="get" class="'.esc_attr($class).'" action="'.esc_url(home_url('/')).'">';
    echo '<label class="screen-reader-text" for="'.esc_attr($class).'-query">ブログを検索</label>';
    echo '<input id="'.esc_attr($class).'-query" type="search" name="s" placeholder="キーワードで記事を検索" value="'.esc_attr(get_search_query()).'" required>';
    echo '<input type="hidden" name="post_type" value="post">';
    echo '<button type="submit" aria-label="記事を検索">検索 <span aria-hidden="true">↗</span></button>';
    echo '</form>';
}

function gs_blog_directory($show_intro=true) {
    echo '<section class="blog-directory" aria-label="記事カテゴリから探す">';
    echo '<div class="blog-directory-head"><div>';
    echo '<span class="eyebrow">FIND YOUR TOPIC</span>';
    echo '<h2>知りたいテーマから探す</h2>';
    if ($show_intro) { echo '<p>開業の準備から日々の運営まで、目的に合わせて記事を探せます。</p>'; }
    echo '</div>';
    gs_blog_search_form();
    echo '</div><div class="blog-category-grid">';
    foreach (gs_blog_category_items() as $item) {
        $current=is_category($item['id']) ? ' aria-current="page"' : '';
        echo '<a class="blog-category-card" href="'.esc_url($item['url']).'"'.$current.'>';
        echo '<strong>'.esc_html($item['name']).'</strong>';
        echo '<span class="blog-category-description">'.esc_html($item['description']).'</span>';
        echo '<span class="blog-category-foot"><span>'.esc_html(number_format_i18n($item['count'])).' 記事</span><span aria-hidden="true">↗</span></span>';
        echo '</a>';
    }
    echo '</div></section>';
}
