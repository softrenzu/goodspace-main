<?php get_header(); ?>
<main id="content" class="inner-main"><section class="page-hero"><div class="container"><span class="eyebrow">JOURNAL</span><h1><?php the_archive_title(); ?></h1><?php the_archive_description('<p>','</p>'); ?></div></section><section class="section"><div class="container"><div class="journal-grid journal-grid-archive"><?php while(have_posts()): the_post(); gs_card(); endwhile; ?></div><div class="pagination"><?php the_posts_pagination(); ?></div></div></section></main>
<?php get_footer(); ?>
