<?php get_header(); ?>
<?php $category=get_queried_object(); ?>
<main id="content" class="inner-main blog-category-main">
  <section class="page-hero blog-hero blog-category-hero">
    <div class="container">
      <div class="blog-breadcrumb"><a href="<?php echo esc_url(gs_blog_link()); ?>">ブログ一覧</a> <span aria-hidden="true">／</span> <?php single_cat_title(); ?></div>
      <span class="eyebrow">CATEGORY / GOOD SPACE JOURNAL</span>
      <h1><?php single_cat_title(); ?></h1>
      <?php if (!empty($category->description)): ?><p><?php echo esc_html(wp_strip_all_tags($category->description)); ?></p>
      <?php else: $definitions=gs_blog_category_definitions(); $info=$definitions[$category->slug]??null; if ($info): ?>
        <p><?php echo esc_html($info['description']); ?></p>
      <?php endif; endif; ?>
    </div>
  </section>
  <section class="section blog-index-section">
    <div class="container">
      <?php gs_blog_directory(false); ?>
      <div class="blog-results-heading">
        <div><span class="eyebrow">CATEGORY ARTICLES</span><h2><?php single_cat_title(); ?>の記事</h2></div>
        <span class="blog-results-count"><?php echo esc_html(number_format_i18n((int)($GLOBALS['wp_query']->found_posts ?? 0))); ?> 記事</span>
      </div>
      <?php if (have_posts()): ?><div class="journal-grid journal-grid-archive">
        <?php while (have_posts()): the_post(); gs_card(); endwhile; ?>
      </div><div class="pagination"><?php the_posts_pagination(array('mid_size'=>1,'prev_text'=>'← 前へ','next_text'=>'次へ →')); ?></div>
      <?php else: ?><div class="empty-journal"><h3>このカテゴリーの記事は準備中です</h3><p>他のカテゴリーもご覧ください。</p><a class="text-link" href="<?php echo esc_url(gs_blog_link()); ?>">ブログ一覧へ →</a></div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
