<?php get_header(); ?>
<main id="content" class="inner-main blog-index-main">
  <section class="page-hero blog-hero">
    <div class="container">
      <span class="eyebrow">GOOD SPACE JOURNAL</span>
      <h1>民泊・レンタルスペースの<br>運営に役立つ情報を。</h1>
      <p>始め方から集客・運営改善まで。気になるカテゴリーから、必要な情報へすぐにアクセスできます。</p>
    </div>
  </section>
  <section class="section blog-index-section">
    <div class="container">
      <?php gs_blog_directory(); ?>
      <div class="blog-results-heading">
        <div><span class="eyebrow">LATEST ARTICLES</span><h2>新着記事</h2></div>
        <span class="blog-results-count"><?php echo esc_html(number_format_i18n((int)($GLOBALS['wp_query']->found_posts ?? 0))); ?> 記事</span>
      </div>
      <?php if (have_posts()): ?>
        <div class="journal-grid journal-grid-archive">
          <?php while (have_posts()): the_post(); gs_card(); endwhile; ?>
        </div>
        <div class="pagination"><?php the_posts_pagination(array('mid_size'=>1,'prev_text'=>'← 前へ','next_text'=>'次へ →')); ?></div>
      <?php else: ?>
        <div class="empty-journal"><h3>記事を準備中です</h3><p>カテゴリーを選んで、気になるテーマから探してください。</p></div>
      <?php endif; ?>
    </div>
  </section>
</main>
<?php get_footer(); ?>
