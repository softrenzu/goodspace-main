<?php get_header(); ?>
<main id="content" class="inner-main blog-search-main">
  <section class="page-hero blog-hero">
    <div class="container">
      <span class="eyebrow">SEARCH / GOOD SPACE JOURNAL</span>
      <h1>ブログ記事を検索</h1>
      <p>「<?php echo esc_html(get_search_query()); ?>」の検索結果です。</p>
    </div>
  </section>
  <section class="section blog-index-section">
    <div class="container">
      <?php gs_blog_directory(false); ?>
      <div class="blog-results-heading">
        <div><span class="eyebrow">SEARCH RESULTS</span><h2>検索結果</h2></div>
        <span class="blog-results-count"><?php echo esc_html(number_format_i18n((int)($GLOBALS['wp_query']->found_posts ?? 0))); ?> 件</span>
      </div>
      <?php if (have_posts()): ?><div class="journal-grid journal-grid-archive">
        <?php while (have_posts()): the_post(); gs_card(); endwhile; ?>
      </div><div class="pagination"><?php the_posts_pagination(array('mid_size'=>1,'prev_text'=>'← 前へ','next_text'=>'次へ →')); ?></div>
      <?php else: ?><div class="empty-journal"><h3>該当する記事はありません</h3><p>別のキーワードやカテゴリーでもお試しください。</p></div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
