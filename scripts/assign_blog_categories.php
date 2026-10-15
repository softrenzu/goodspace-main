<?php
/**
 * One-time safe migration for legacy Good Space posts.
 * Run: docker compose --profile maintenance run --rm -T wpcli wp eval-file scripts/assign_blog_categories.php
 * Only posts with no category or the default "Uncategorized" category are changed.
 */
if (!defined('ABSPATH')) { exit; }
if (!function_exists('gs_blog_category_slug_for')) {
    WP_CLI::error('Good Space Renew theme must be active for category migration.');
}
gs_blog_seed_categories();
$ids=get_posts(array(
    'post_type'=>'post','post_status'=>'any','posts_per_page'=>-1,
    'orderby'=>'ID','order'=>'ASC','fields'=>'ids',
));
$changed=0;
$skipped=0;
$unmapped=0;
$default_id=(int)get_option('default_category');
foreach ($ids as $id) {
    $assigned=wp_get_post_categories($id);
    if (is_wp_error($assigned)) { $unmapped++; continue; }
    $assigned=array_map('intval',$assigned);
    // Respect explicit category choices by editors.
    if (count(array_diff($assigned,array($default_id)))>0) {
        $skipped++;
        continue;
    }
    $post=get_post($id);
    $source=($post ? $post->post_title.' '.$post->post_excerpt : '');
    $slug=gs_blog_category_slug_for($source);
    $term=get_category_by_slug($slug);
    if (!$term) { $unmapped++; continue; }
    $result=wp_set_post_categories((int)$id,array((int)$term->term_id),false);
    if (is_wp_error($result)) { $unmapped++; continue; }
    $changed++;
}
WP_CLI::log('Category migration: classified='.$changed.
    ' editor_assigned_skipped='.$skipped.' errors='.$unmapped);
