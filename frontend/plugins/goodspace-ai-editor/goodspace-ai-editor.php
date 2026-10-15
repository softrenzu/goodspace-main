<?php
/**
 * Plugin Name: Good Space AI Editor
 * Description: 登録済みの確認済み情報から記事を作成し、品質チェックに通れば公開するGood Space専用AI編集支援。
 * Version: 1.2.0
 * Requires PHP: 8.2
 * Requires at least: 6.5
 * Text Domain: goodspace-ai-editor
 */
if (!defined('ABSPATH')) { exit; }

const GSAI_POST_TYPE = 'gs_ai_topic';
const GSAI_CRON_HOOK = 'gsai_generate_one_draft';

add_action('init', function () {
    register_post_type(GSAI_POST_TYPE, array(
        'labels' => array('name'=>'AI記事テーマ', 'singular_name'=>'AI記事テーマ'),
        'public'=>false, 'show_ui'=>true, 'show_in_menu'=>'gsai-editor',
        'show_in_rest'=>false, 'exclude_from_search'=>true,
        'supports'=>array('title'), 'capability_type'=>'post',
        'map_meta_cap'=>true,
    ));
});

add_action('admin_menu', function () {
    add_menu_page('Good Space AI Editor','Good Space AI','manage_options','gsai-editor','gsai_render_admin','dashicons-edit-page',61);
    add_submenu_page('gsai-editor','AI設定・記事生成','AI設定・記事生成','manage_options','gsai-editor','gsai_render_admin');
});

function gsai_text_length($s) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }
function gsai_secret() { return (string)get_option('gsai_openrouter_key',''); }
function gsai_model() { return (string)get_option('gsai_openrouter_model','openai/gpt-4o-mini'); }
function gsai_enabled() { return get_option('gsai_daily_enabled','0') === '1'; }
function gsai_next_seven_jst() {
    $tz=new DateTimeZone('Asia/Tokyo');
    $now=new DateTimeImmutable('now',$tz);
    $next=$now->setTime(7,0,0);
    if ($next->getTimestamp() <= time()) { $next=$next->modify('+1 day'); }
    return $next->getTimestamp();
}
function gsai_sync_schedule() {
    if (gsai_enabled()) {
        if (!wp_next_scheduled(GSAI_CRON_HOOK)) {
            wp_schedule_event(gsai_next_seven_jst(),'daily',GSAI_CRON_HOOK);
        }
    } else {
        wp_clear_scheduled_hook(GSAI_CRON_HOOK);
    }
}
register_activation_hook(__FILE__,function() {
    add_option('gsai_openrouter_key','', '', false);
    add_option('gsai_openrouter_model','openai/gpt-4o-mini', '', false);
    add_option('gsai_daily_enabled','0', '', false);
    gsai_sync_schedule();
});
register_deactivation_hook(__FILE__,function() { wp_clear_scheduled_hook(GSAI_CRON_HOOK); });

function gsai_admin_redirect($result) {
    wp_safe_redirect(add_query_arg('gsai_result',rawurlencode($result),admin_url('admin.php?page=gsai-editor')));
    exit;
}
function gsai_require_authorized($nonce_action) {
    if (!current_user_can('manage_options')) { wp_die('権限がありません','アクセス拒否',array('response'=>403)); }
    check_admin_referer($nonce_action);
}

add_action('admin_post_gsai_save_settings',function() {
    gsai_require_authorized('gsai_settings');
    $key=isset($_POST['gsai_key']) ? trim((string)wp_unslash($_POST['gsai_key'])) : '';
    if ($key !== '') {
        if (strlen($key)>1000) { gsai_admin_redirect('key_error'); }
        update_option('gsai_openrouter_key',$key,false);
    }
    if (isset($_POST['gsai_clear_key']) && (string)$_POST['gsai_clear_key'] === '1') {
        update_option('gsai_openrouter_key','',false);
    }
    $model=isset($_POST['gsai_model']) ? sanitize_text_field(wp_unslash($_POST['gsai_model'])) : 'openai/gpt-4o-mini';
    if (!preg_match('#^[a-zA-Z0-9._:/-]{3,100}$#',$model)) { gsai_admin_redirect('model_error'); }
    update_option('gsai_openrouter_model',$model,false);
    update_option('gsai_daily_enabled',isset($_POST['gsai_daily'])?'1':'0',false);
    gsai_sync_schedule();
    gsai_admin_redirect('settings_saved');
});

function gsai_https_urls($input) {
    $urls=array();
    foreach (preg_split('/\R/u',(string)$input) as $line) {
        $line=trim($line);
        if ($line==='') { continue; }
        $url=esc_url_raw($line,array('https'));
        $parts=wp_parse_url($url);
        if ($url === '' || !$parts || strtolower((string)($parts['scheme']??''))!=='https' || empty($parts['host'])) {
            throw new InvalidArgumentException('HTTPSの参考URLを1行ずつ入力してください。');
        }
        $urls[]=$url;
    }
    return array_values(array_unique($urls));
}

add_action('admin_post_gsai_add_topic',function() {
    gsai_require_authorized('gsai_topic');
    $title=sanitize_text_field(wp_unslash($_POST['gsai_topic']??''));
    $facts=sanitize_textarea_field(wp_unslash($_POST['gsai_facts']??''));
    $sources=sanitize_textarea_field(wp_unslash($_POST['gsai_sources']??''));
    try { $urls=gsai_https_urls($sources); } catch (Exception $ex) { gsai_admin_redirect('url_error'); }
    if (gsai_text_length($title)<6 || gsai_text_length($facts)<30 || !$urls || gsai_text_length($facts)>5000) {
        gsai_admin_redirect('topic_error');
    }
    $id=wp_insert_post(array('post_type'=>GSAI_POST_TYPE,'post_status'=>'publish','post_title'=>$title),true);
    if (is_wp_error($id)) { gsai_admin_redirect('topic_error'); }
    update_post_meta($id,'gsai_verified_facts',$facts);
    update_post_meta($id,'gsai_source_urls',$urls);
    $category_slug=sanitize_key(wp_unslash($_POST['gsai_category_slug']??''));
    $valid_slugs=function_exists('gs_blog_category_definitions') ? array_keys(gs_blog_category_definitions()) : array();
    if ($category_slug && in_array($category_slug,$valid_slugs,true)) {
        update_post_meta($id,'gsai_category_slug',$category_slug);
    }
    gsai_admin_redirect('topic_added');
});

/**
 * Assign a WordPress blog category to AI posts without changing their permalink.
 * The theme supplies the shared editorial mapping; fall back to operations.
 */
function gsai_assign_blog_category($post_id, $title, $topic_id=0) {
    $slug=$topic_id ? (string)get_post_meta($topic_id,'gsai_category_slug',true) : '';
    if (!$slug && function_exists('gs_blog_category_slug_for')) {
        $topic_title=$topic_id ? get_the_title($topic_id) : '';
        $slug=gs_blog_category_slug_for($topic_title ?: $title);
    }
    if (!$slug) { $slug='operations'; }
    $term=get_category_by_slug($slug);
    if (!$term) { $term=get_category_by_slug('operations'); }
    if ($term) { wp_set_post_categories((int)$post_id,array((int)$term->term_id),false); }
}

function gsai_create_draft($topic_id) {
    $topic=get_post($topic_id);
    if (!$topic || $topic->post_type!==GSAI_POST_TYPE || $topic->post_status!=='publish') {
        return new WP_Error('invalid_topic','登録済みのテーマが見つかりません');
    }
    if ((int)get_post_meta($topic_id,'gsai_generated_post',true)) {
        return new WP_Error('already_generated','このテーマから下書きを作成済みです');
    }
    $facts=(string)get_post_meta($topic_id,'gsai_verified_facts',true);
    $sources=(array)get_post_meta($topic_id,'gsai_source_urls',true);
    if (gsai_text_length($facts)<30 || !$sources) {
        return new WP_Error('missing_facts','確認済みの情報と参考URLが必要です');
    }
    $key=gsai_secret();
    if (!$key) { return new WP_Error('missing_key','OpenRouter APIキーが未設定です'); }
    $instructions='あなたは株式会社Good Spaceの法人向けブログ編集者です。提供された確認済み事実だけを根拠に、日本語の実務記事を書いてください。実在しない実績、料金、収益、許認可、法改正内容、施設名、口コミを捏造しないでください。外部リンクの本文を実際に取得していないので、その内容を読んだと主張しないでください。断定が不適切な内容は「要確認」と明示してください。見出しh2を3つ以上、本文800字程度以上の、分かりやすい文章を作成してください。営業色は控えめに。出力は厳密にJSONオブジェクトのみで、キーはtitle、excerpt、content_htmlです。';
    $payload=array(
        'model'=>gsai_model(), 'temperature'=>0.2,
        'response_format'=>array('type'=>'json_object'),
        'messages'=>array(
            array('role'=>'system','content'=>$instructions),
            array('role'=>'user','content'=>wp_json_encode(array(
                'topic'=>$topic->post_title,
                'verified_facts'=>$facts,
                'reference_urls_for_editorial_check_only'=>$sources,
                'instructions'=>'参考URLの内容は取得していない。記事の出典リンクはWordPress側で添付する。自然な日本語で書く。'
            ),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))
        ),
    );
    $response=wp_remote_post('https://openrouter.ai/api/v1/chat/completions',array(
        'timeout'=>90,
        'headers'=>array('Content-Type'=>'application/json','Authorization'=>'Bearer '.$key,'HTTP-Referer'=>home_url('/'),'X-Title'=>'GoodSpace AI Editor'),
        'body'=>wp_json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES),
    ));
    if (is_wp_error($response)) { return new WP_Error('api_error','AI APIへの接続に失敗しました'); }
    if (wp_remote_retrieve_response_code($response)!==200) { return new WP_Error('api_status','AI APIがエラーを返しました（HTTP '.(int)wp_remote_retrieve_response_code($response).'）'); }
    $data=json_decode(wp_remote_retrieve_body($response),true);
    $raw=$data['choices'][0]['message']['content']??'';
    if (is_array($raw)) { $raw=implode('',array_map(static function($part){return is_array($part)?($part['text']??''):'';},$raw)); }
    $article=is_string($raw)?json_decode($raw,true):null;
    if (!is_array($article)) { return new WP_Error('bad_json','AIの応答形式が不正です'); }
    $title=sanitize_text_field($article['title']??'');
    $excerpt=sanitize_text_field($article['excerpt']??'');
    $content=wp_kses_post((string)($article['content_html']??''));
    $plain=wp_strip_all_tags($content);
    if (gsai_text_length($title)<8 || gsai_text_length($title)>115 || gsai_text_length($plain)<400 || stripos($content,'<h2')===false) {
        return new WP_Error('quality','生成文章の品質チェックに通りませんでした。投稿しません');
    }
    $links='';
    foreach ($sources as $url) {
        $links.='<li><a href="'.esc_url($url).'" target="_blank" rel="noopener noreferrer">'.esc_html($url).'</a></li>';
    }
    $content.='<h2>編集者向けの確認先リンク</h2><p>以下のURLは参考情報として登録されたものです。本文との整合性を公開前に必ず確認してください。</p><ul>'.$links.'</ul>';
    $id=wp_insert_post(array(
        'post_type'=>'post','post_status'=>'publish',
        'post_title'=>$title,'post_excerpt'=>$excerpt,'post_content'=>$content,
        'meta_input'=>array('gsai_source_topic'=>$topic_id,'gsai_needs_fact_check'=>'1'),
    ),true);
    if (is_wp_error($id)) { return new WP_Error('save_error','WordPressに記事を公開できませんでした'); }
    gsai_assign_blog_category((int)$id,$title,$topic_id);
    update_post_meta($topic_id,'gsai_generated_post',(int)$id);
    return (int)$id;
}

add_action('admin_post_gsai_generate',function() {
    gsai_require_authorized('gsai_generate');
    $topic_id=absint($_POST['gsai_topic_id']??0);
    $id=gsai_create_draft($topic_id);
    if (is_wp_error($id)) { gsai_admin_redirect('generate_error_'.$id->get_error_code()); }
    wp_safe_redirect(admin_url('post.php?post='.$id.'&action=edit')); exit;
});

// Manual topics and automatically prepared editorial topics can be published. No external scraping.
require_once __DIR__ . '/goodspace-daily-publisher.php';
add_action(GSAI_CRON_HOOK, 'gsai_daily_tick');
add_action('admin_post_gsai_refill_topics', function() {
    gsai_require_authorized('gsai_refill_topics');
    $result=gsai_daily_refill(14);
    gsai_admin_redirect(($result['status']??'')==='ok' ? 'topics_refilled' : 'topics_refill_failed');
});

function gsai_render_admin() {
    if (!current_user_can('manage_options')) { return; }
    $queue=get_posts(array('post_type'=>GSAI_POST_TYPE,'post_status'=>'publish','posts_per_page'=>80,'orderby'=>'date','order'=>'DESC'));
    $messages=array('settings_saved'=>'設定を保存しました','topic_added'=>'確認済み記事テーマを登録しました','key_error'=>'APIキーが長すぎます','model_error'=>'モデル名が不正です','url_error'=>'URLは有効なHTTPS形式にしてください','topic_error'=>'テーマ、30文字以上の確認済み事実、参考URLを入力してください','topics_refilled'=>'直近14日分の記事テーマを補充しました','topics_refill_failed'=>'記事テーマの補充に失敗しました。サーバーログを確認してください');
    $notice=isset($_GET['gsai_result'])?sanitize_text_field(wp_unslash($_GET['gsai_result'])):'';
    ?>
    <div class="wrap"><h1>Good Space AI Editor</h1>
      <p>登録された確認済み事実と参考URLからブログ記事を生成し、<strong>品質チェックを通過したらWordPressに自動公開</strong>します。料金・許認可・実績・法令などの情報はテーマ登録前に必ず確認してください。生成内容の事後確認も推奨します。</p>
      <?php if ($notice): ?><div class="notice <?php echo esc_attr(str_starts_with($notice,'generate_error')||str_ends_with($notice,'error')?'notice-error':'notice-success'); ?> is-dismissible"><p><?php echo esc_html($messages[$notice]??('処理結果: '.$notice)); ?></p></div><?php endif; ?>
      <hr><h2>1. API設定</h2>
      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
        <input type="hidden" name="action" value="gsai_save_settings"><?php wp_nonce_field('gsai_settings'); ?>
        <table class="form-table"><tr><th>OpenRouter APIキー</th><td><input type="password" autocomplete="new-password" name="gsai_key" class="regular-text" placeholder="<?php echo gsai_secret()?'登録済み（変更する場合のみ入力）':'APIキーを入力'; ?>"><p class="description">APIキーはサーバーから外部に表示しません。保存時はWordPress DBに保管されます。HTTPS・管理者権限・DBバックアップ保護が必要です。</p><label><input type="checkbox" name="gsai_clear_key" value="1">保存済みキーを削除</label></td></tr>
        <tr><th>モデルID</th><td><input type="text" name="gsai_model" class="regular-text" value="<?php echo esc_attr(gsai_model()); ?>"></td></tr>
        <tr><th>毎日07:00ごろに記事を自動公開</th><td><label><input type="checkbox" name="gsai_daily" value="1" <?php checked(gsai_enabled()); ?>>有効化する（初期値OFF、登録済み未処理テーマから1件を生成・公開）</label><p class="description">WordPress Cronはサイトアクセスに依存するため、正確な時刻の実行にはOSのcronなどの設定が必要です。時刻はJSTです。</p></td></tr></table>
        <?php submit_button('設定を保存'); ?>
      </form><hr><h2>2. 記事テーマ（毎日自動更新）</h2>
      <p>今後14日分のテーマを自動的に準備し、毎日0:15（JST）に候補を補充します。テーマを使い切っても自動で補充されます。古くなった未使用の自動テーマは非表示にし、手動で登録したテーマは変更しません。</p>
      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin-bottom:20px">
        <input type="hidden" name="action" value="gsai_refill_topics"><?php wp_nonce_field('gsai_refill_topics'); ?>
        <?php submit_button('記事テーマを今すぐ補充','secondary','submit',false); ?>
      </form>
      <h3>個別にテーマを追加する（任意）</h3>
      <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="gsai_add_topic"><?php wp_nonce_field('gsai_topic'); ?>
      <table class="form-table"><tr><th>記事テーマ</th><td><input name="gsai_topic" class="large-text" required placeholder="例：民泊運営を始める前に確認すべき3つの項目"></td></tr>
      <tr><th>ブログカテゴリー</th><td>
      <select name="gsai_category_slug"><option value="">自動判定する</option>
      <?php if (function_exists('gs_blog_category_definitions')): foreach(gs_blog_category_definitions() as $slug=>$item): ?>
      <option value="<?php echo esc_attr($slug); ?>"><?php echo esc_html($item['name']); ?></option>
      <?php endforeach; endif; ?></select><p class="description">記事を公開するときのカテゴリーです。未指定なら記事テーマから推定します。</p></td></tr>
      <tr><th>確認済み事実</th><td><textarea name="gsai_facts" rows="7" class="large-text" required placeholder="例：このテーマで断定してよい事実を箇条書きで30文字以上記入。法制度、価格、収益の一次資料を確認すること。"></textarea></td></tr>
      <tr><th>参考URL</th><td><textarea name="gsai_sources" rows="4" class="large-text" required placeholder="https://...\nhttps://..."></textarea><p class="description">1行にHTTPSのURLを1つ。URL先の情報をAIが取得するわけではありません。事実の転記は別欄に必要です。</p></td></tr></table>
      <?php submit_button('テーマを登録'); ?></form><hr>
      <h2>3. 登録済みテーマ・記事生成と公開</h2>
      <?php if (!$queue): ?><p>まだテーマは登録されていません。</p><?php else: ?><table class="widefat striped"><thead><tr><th>テーマ</th><th>状態</th><th>操作</th></tr></thead><tbody>
      <?php foreach ($queue as $topic): $draft_id=(int)get_post_meta($topic->ID,'gsai_generated_post',true); ?><tr><td><?php echo esc_html($topic->post_title); ?>
          <?php $auto_day=get_post_meta($topic->ID,'gsai_auto_day',true); if ($auto_day): ?>
            <br><small>自動追加：<?php echo esc_html($auto_day); ?>向け</small>
          <?php endif; ?></td><td><?php echo $draft_id ? '公開済み' : '未生成'; ?></td><td><?php if ($draft_id): ?><a href="<?php echo esc_url(admin_url('post.php?post='.$draft_id.'&action=edit')); ?>">公開済み記事の編集画面へ</a><?php else: ?><form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"><input type="hidden" name="action" value="gsai_generate"><input type="hidden" name="gsai_topic_id" value="<?php echo esc_attr($topic->ID); ?>"><?php wp_nonce_field('gsai_generate'); ?><button type="submit" class="button">AI記事を生成して公開</button></form><?php endif; ?></td></tr><?php endforeach; ?></tbody></table><?php endif; ?>
      <p style="margin-top:25px;color:#59636a">AI APIへの送信内容: テーマ・確認済み事実・参考URL。生成本文は審査待ちにせずWordPressに公開されます。個人情報や未公開機密は登録しないでください。毎日自動公開するには上のチェックを有効にしてください。未処理テーマは毎日自動補充されます。</p>
    </div>
    <?php
}
