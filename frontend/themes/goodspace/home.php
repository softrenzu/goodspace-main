<?php get_header(); ?>
<main id="content" class="inner-main"><section class="page-hero"><div class="container"><span class="eyebrow">JOURNAL / INSIGHTS</span><h1>Good Space Journal</h1><p>民泊運営、レンタルスペース、空室活用。実務に役立つヒントを発信します。</p></div></section>
<section class="section"><div class="container">
 <div class="blog-toolbar"><span>ALL ARTICLES</span><span><?php echo esc_html((string)($GLOBALS['wp_query']->found_posts ?? 0)); ?> ARTICLES</span></div>
 <?php if (have_posts()): ?><div class="journal-grid journal-grid-archive"><?php while (have_posts()): the_post(); gs_card(); endwhile; ?></div><div class="pagination"><?php the_posts_pagination(array('mid_size'=>1,'prev_text'=>'← 前へ','next_text'=>'次へ →')); ?></div>
 <?php else: ?><div class="empty-journal"><span class="eyebrow">COMING SOON</span><h2>記事を準備中です。</h2><p>Good Spaceのブログは、WordPressから記事を追加すると表示されます。</p></div><?php endif; ?>
</div></section></main>
<?php get_footer(); ?>
