<?php
/**
 * Good Space AI: one published post per JST calendar day (best-effort).
 * No external facts are invented for the emergency fallback.
 */
if (!defined('ABSPATH')) { exit; }

function gsai_daily_jst_now() {
    return new DateTimeImmutable('now', new DateTimeZone('Asia/Tokyo'));
}

function gsai_daily_post_exists($day) {
    return !empty(get_posts(array(
        'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 1,
        'fields' => 'ids', 'suppress_filters' => false,
        'date_query' => array(array(
            'column' => 'post_date',
            'after' => $day . ' 00:00:00',
            'before' => $day . ' 23:59:59',
            'inclusive' => true,
        )),
    )));
}

function gsai_daily_plan($day) {
    // Editorial scope: practical topics only, derived from Good Space's known service areas.
    $areas = array(
        '物件選定', 'スペースの用途企画', 'ターゲット設定', '室内コーディネート',
        '備品の選定', '設備設置', '施設写真の撮影', '予約サイト掲載',
        '料金設定', '料金調整', '予約管理', '利用者メッセージ対応',
        '電話対応', 'トラブル対応', '清掃品質', '消耗品補充',
        'レビュー対応', 'オーナーへの運営報告',
    );
    $angles = array(
        '導入前の確認', '日々の点検', '担当者への引継ぎ', '初めての運営',
        '運営手順の標準化', '利用者目線の改善', '問い合わせを減らす工夫',
        '繁忙期の備え', '閑散期の見直し', '改善の優先順位',
        '必要な情報の整理', 'チェックリストの作り方', '定期見直し',
        '作業時間の減らし方', '品質を保つ工夫', '作業の記録',
        '見落としを防ぐ確認', '手戻りを減らす段取り', '役割分担',
        'トラブル時の初動', '情報共有', '運用開始前の準備',
        '改善案の検討', 'オーナー負担の軽減', '振り返りの方法',
    );
    $base = new DateTimeImmutable('2026-10-14', new DateTimeZone('Asia/Tokyo'));
    $target = new DateTimeImmutable($day, new DateTimeZone('Asia/Tokyo'));
    $index = max(0, (int)$base->diff($target)->format('%r%a'));
    // Coprime increments spread the editorial areas and angles through the series.
    $area = $areas[$index % count($areas)];
    $angle = $angles[(int)floor($index / count($areas)) % count($angles)];
    $title = $area . 'の' . $angle . '｜民泊・レンタルスペース運営の実務';
    $facts = 'Good Spaceが案内するサービス領域には、スペースの企画開発、物件選定、室内の企画、備品・設備の準備、撮影・掲載、予約管理、利用者対応、清掃・備品管理などがあります。'
      . 'この記事では「' . $area . '」を「' . $angle . '」の観点から扱います。'
      . '具体的な数字、法令、許認可、施設実績、料金を推測して書かず、担当者が確認・記録・改善するための一般的な実務上の提案に限定してください。';
    return array('title'=>$title, 'facts'=>$facts, 'sources'=>array('https://good-space.jp/'),
                 'area'=>$area, 'angle'=>$angle);
}

function gsai_daily_auto_topic($day, $plan) {
    $existing = get_posts(array(
        'post_type'=>GSAI_POST_TYPE, 'post_status'=>'publish',
        'posts_per_page'=>1, 'meta_key'=>'gsai_auto_day', 'meta_value'=>$day,
    ));
    if ($existing) { return (int)$existing[0]->ID; }
    $id=wp_insert_post(array(
        'post_type'=>GSAI_POST_TYPE, 'post_status'=>'publish',
        'post_title'=>$plan['title'],
    ),true);
    if (is_wp_error($id)) { return $id; }
    update_post_meta($id,'gsai_verified_facts',$plan['facts']);
    update_post_meta($id,'gsai_source_urls',$plan['sources']);
    update_post_meta($id,'gsai_auto_day',$day);
    return (int)$id;
}

function gsai_daily_fallback_publish($day, $plan) {
    // Transparent, evergreen editorial fallback: never claim an AI researched external sites.
    $area=esc_html($plan['area']);
    $angle=esc_html($plan['angle']);
    $date=esc_html($day);
    $heading='運営チェックノート：'.$plan['area'].'と'.$plan['angle'];
    $paragraphs=array(
        '<h2>今回のテーマ</h2><p>民泊やレンタルスペースを運営するとき、'.$area.'を'.$angle.'の視点で整理することは、日常業務を見直すきっかけになります。以下は特定の施設や法制度についての断定ではなく、一般的な運営実務のための確認手順です。</p>',
        '<h2>現状を確認する</h2><p>最初に、実際の運営手順を一つずつ書き出します。担当者、作業するタイミング、必要な情報、記録先が分かれている場合は、全体の流れを一枚の一覧にすると確認しやすくなります。すでに問題なく進んでいる点と、改善が必要な点は区別して扱いましょう。</p>',
        '<h2>利用者とオーナーの目線を分けて考える</h2><p>利用者には、迷わず利用できる案内と問い合わせへの適切な対応が重要です。一方、オーナーにとっては、作業負担や支出、運営状況を把握できることが重要です。'.$area.'に関しても、両者が必要とする情報や確認頻度を整理し、無理のない手順を検討します。</p>',
        '<h2>改善を実施する順番</h2><p>改善案を一度にすべて導入するのではなく、まず影響が大きいと思われる項目を選び、担当者と期限を決めて試します。実施前後の問い合わせ内容、作業時間、利用者からの声など、実際に測れる指標を選んで記録します。効果が確認できた手順だけを日常運用に取り込みます。</p>',
        '<h2>継続して見直すポイント</h2><p>担当者が変わっても対応できるよう、手順と判断基準を文書化します。定期的に運営記録を振り返り、設備や利用方法が変わったときは案内も更新してください。必要に応じて専門家や関係機関に確認し、法令や安全性に関わる事項は推測で判断しないことが大切です。</p>',
        '<p>本記事は'.$date.'の運営実務チェックノートです。個別施設への適用に際しては現場の状況を確認してください。</p>',
    );
    $id=wp_insert_post(array(
        'post_type'=>'post', 'post_status'=>'publish', 'post_title'=>$heading,
        'post_content'=>implode("\n",$paragraphs),
        'post_excerpt'=>'民泊・レンタルスペース運営における'.$plan['area'].'を、'.$plan['angle'].'の観点から整理する実務チェックノート。',
        'post_date'=>current_time('mysql'),
        'meta_input'=>array('gsai_daily_fallback'=>$day,'gsai_daily_needs_review'=>'1'),
    ),true);
    return $id;
}

function gsai_daily_publish_once() {
    global $wpdb;
    $now=gsai_daily_jst_now();
    $day=$now->format('Y-m-d');
    if (!gsai_enabled()) { return array('status'=>'disabled','day'=>$day); }
    if ((int)$now->format('G') < 7) { return array('status'=>'not_due','day'=>$day); }
    $lock='gsai_daily_'.str_replace('-','',$day);
    if ((int)$wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, 0)', $lock)) !== 1) {
        return array('status'=>'busy','day'=>$day);
    }
    try {
        if (gsai_daily_post_exists($day)) {
            $result=array('status'=>'already_published','day'=>$day);
            update_option('gsai_daily_last_result',$result,false);
            return $result;
        }
        $plan=gsai_daily_plan($day);
        $topics=get_posts(array(
            'post_type'=>GSAI_POST_TYPE,'post_status'=>'publish',
            'posts_per_page'=>100,'orderby'=>'date','order'=>'ASC',
        ));
        $topic_id=0;
        foreach ($topics as $topic) {
            if (!(int)get_post_meta($topic->ID,'gsai_generated_post',true)) {
                $topic_id=(int)$topic->ID;
                break;
            }
        }
        if (!$topic_id) {
            $topic_id=gsai_daily_auto_topic($day,$plan);
        }
        $publish_error='';
        if (is_wp_error($topic_id)) {
            $publish_error=$topic_id->get_error_code();
        } else {
            $id=gsai_create_draft($topic_id); // Legacy name; new plugin publishes on success.
            if (!is_wp_error($id) && get_post_status($id)==='publish') {
                $result=array('status'=>'published_ai','day'=>$day,'post_id'=>(int)$id);
                update_option('gsai_daily_last_result',$result,false);
                return $result;
            }
            $publish_error=is_wp_error($id) ? $id->get_error_code() : 'not_published';
        }
        // If OpenRouter fails, publish an original, restricted-scope operational checklist.
        $id=gsai_daily_fallback_publish($day,$plan);
        if (!is_wp_error($id) && get_post_status($id)==='publish') {
            $result=array('status'=>'published_fallback','day'=>$day,
                          'post_id'=>(int)$id,'api_error'=>$publish_error);
            update_option('gsai_daily_last_result',$result,false);
            return $result;
        }
        $result=array('status'=>'failed','day'=>$day,
                      'api_error'=>$publish_error,
                      'fallback_error'=>is_wp_error($id)?$id->get_error_code():'publish_failed');
        update_option('gsai_daily_last_result',$result,false);
        wp_mail(get_option('admin_email'),'Good Space ブログ自動公開失敗: '.$day,
                '自動公開に失敗しました。WordPressのGood Space AI管理画面とサーバーログを確認してください。');
        return $result;
    } finally {
        $wpdb->get_var($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function gsai_daily_tick() {
    return gsai_daily_publish_once();
}

if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('gsai daily', function () {
        $r=gsai_daily_publish_once();
        WP_CLI::log(wp_json_encode($r,JSON_UNESCAPED_UNICODE));
        if (($r['status']??'')==='failed') { WP_CLI::error('Daily publication failed'); }
    });
    WP_CLI::add_command('gsai status', function () {
        WP_CLI::log(wp_json_encode(get_option('gsai_daily_last_result',array()),JSON_UNESCAPED_UNICODE));
    });
}
