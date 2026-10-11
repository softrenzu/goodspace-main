<?php get_header(); ?>
<main id="content" class="inner-main"><article class="blog-article">
<?php while (have_posts()): the_post(); ?>
<header class="article-header"><div class="container article-container"><a class="back-link" href="<?php echo esc_url(gs_blog_link()); ?>">← Journalに戻る</a><div class="journal-meta"><?php $cats=get_the_category(); if($cats): ?><span><?php echo esc_html($cats[0]->name); ?></span><?php endif; ?><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time></div><h1><?php the_title(); ?></h1><?php if (has_excerpt()): ?><p class="article-intro"><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?></div></header>
<div class="container article-container"><?php if(has_post_thumbnail()): ?><div class="article-cover"><?php the_post_thumbnail('large',array('loading'=>'eager')); ?></div><?php endif; ?><div class="article-body entry-content"><?php the_content(); wp_link_pages(); ?></div><div class="article-end"><a class="text-link" href="<?php echo esc_url(gs_blog_link()); ?>">記事一覧に戻る →</a><a class="text-link" href="<?php echo gs_link('/contact/'); ?>">運営について相談する ↗</a></div></div>
<?php endwhile; ?>
</article></main>
<?php get_footer(); ?>
