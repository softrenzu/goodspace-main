<?php get_header(); ?>
<main id="content" class="inner-main"><section class="page-hero"><div class="container"><span class="eyebrow">GOOD SPACE</span><h1><?php the_title(); ?></h1></div></section><section class="section"><div class="container narrow-copy"><?php while (have_posts()): the_post(); the_content(); endwhile; ?></div></section></main>
<?php get_footer(); ?>
