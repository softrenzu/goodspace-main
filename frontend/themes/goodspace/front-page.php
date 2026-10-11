<?php get_header(); ?>
<main id="content">
<section class="hero">
 <div class="container hero-layout">
  <div class="hero-copy">
   <div class="overline"><span class="signal"></span> SPACE IS POSSIBILITY <span class="rule"></span> GOOD SPACE</div>
   <h1>空間に、<br><em>新しい可能性</em>を。</h1>
   <p class="hero-lead">空いている部屋にも、使われなくなった場所にも。<br>まだ見つかっていない価値がある。<br>Good Spaceは、空間の企画から運営までを支えます。</p>
   <div class="hero-actions"><a class="button button-dark" href="#services">サービスを見る <span aria-hidden="true">↗</span></a><a class="button button-line" href="<?php echo gs_link('/contact/'); ?>">運営について相談する <span aria-hidden="true">→</span></a></div>
   <div class="hero-caption"><span class="tiny-mark"></span> MINPAKU / RENTAL SPACE / SPACE UTILIZATION</div>
  </div>
  <div class="hero-visual" aria-label="明るい室内空間のイメージイラスト"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/room-hero.svg'); ?>" alt="自然光が差し込むモダンな空間のイラスト" width="800" height="820"><div class="hero-visual-label"><strong>SPACE FOR<br>NEW IDEAS.</strong><span>01 — GOOD SPACE</span></div></div>
 </div>
 <div class="hero-tail container"><span>新しい価値のある空間へ。</span><a href="#intro">SCROLL TO EXPLORE <span aria-hidden="true">↓</span></a></div>
</section>

<section class="intro section" id="intro">
 <div class="container intro-layout">
  <div><span class="eyebrow">01 / OUR PHILOSOPHY</span><h2 class="display-heading">場所が変わると、<br><em>未来が変わる。</em></h2></div>
  <div class="intro-text"><p>いつもの部屋を、誰かの特別な時間に。使われていない物件を、新しい事業の起点に。</p><p>私たちは民泊とレンタルスペースの運営を通して、空間の可能性を考え、形にするパートナーです。</p><a class="text-link" href="<?php echo gs_link('/company/'); ?>">Good Spaceについて <span aria-hidden="true">↗</span></a></div>
 </div>
</section>

<section class="services section" id="services">
 <div class="container">
  <div class="section-heading"><div><span class="eyebrow">02 / WHAT WE DO</span><h2 class="display-heading">空間の価値を、<br>事業の力に。</h2></div><p>物件ごとの特性や目的に合わせて、運営方法を一緒に考えます。</p></div>
  <div class="service-tiles">
   <a class="service-tile" href="<?php echo gs_link('/service/minpaku/'); ?>"><div class="tile-art tile-minpaku" aria-hidden="true"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/room-stay.svg'); ?>" alt="" loading="lazy"></div><div class="tile-content"><div class="tile-top"><span>01 / HOSPITALITY</span><span class="circle-arrow" aria-hidden="true">↗</span></div><h3>民泊運営代行</h3><p>宿泊する人にも、運営する人にも。安心して続けられる宿泊空間を目指して。</p><span class="tile-link">VIEW SERVICE →</span></div></a>
   <a class="service-tile" href="<?php echo gs_link('/service/rental-space/'); ?>"><div class="tile-art tile-rental" aria-hidden="true"><img src="<?php echo esc_url(get_template_directory_uri().'/assets/room-meet.svg'); ?>" alt="" loading="lazy"></div><div class="tile-content"><div class="tile-top"><span>02 / SPACE MANAGEMENT</span><span class="circle-arrow" aria-hidden="true">↗</span></div><h3>レンタルスペース運営</h3><p>会議、撮影、イベント、さまざまな目的に。空間に人が集まる仕組みをつくります。</p><span class="tile-link">VIEW SERVICE →</span></div></a>
  </div>
  <a class="service-promo" href="https://service.good-space.jp/" target="_blank" rel="noopener noreferrer"><div><span class="eyebrow">FIND YOUR SPACE</span><strong>利用できるレンタルスペースをお探しの方へ</strong><p>エリアや用途に合わせて、スペースを検索できます。</p></div><span class="promo-arrow" aria-hidden="true">↗</span></a>
 </div>
</section>

<section class="approach section">
 <div class="container approach-layout">
  <div class="approach-visual"><div class="approach-mark">G<span>—</span>S</div><div class="approach-corner">BETTER USE OF SPACE<br>BETTER POSSIBILITIES</div></div>
  <div class="approach-content"><span class="eyebrow">03 / OUR APPROACH</span><h2 class="display-heading">空間の課題を、<br><em>運営の視点</em>から。</h2><p class="approach-lead">立ち上げるだけで終わらせない。利用者の体験と日々の運営を見据えて、空間活用を考えます。</p><div class="approach-list"><div><span>01</span><p>物件・立地・利用目的に合った活用方法を検討</p></div><div><span>02</span><p>集客・予約対応・運用を見据えた設計</p></div><div><span>03</span><p>運営開始後も見直しと改善を重ねる</p></div></div><a href="<?php echo gs_link('/contact/'); ?>" class="button button-outline">事業について相談する <span aria-hidden="true">↗</span></a></div>
 </div>
</section>

<section class="journal section" id="journal">
 <div class="container">
  <div class="section-heading journal-heading"><div><span class="eyebrow">04 / JOURNAL</span><h2 class="display-heading">空間づくりの<br>ヒントを届ける。</h2></div><a class="button button-outline" href="<?php echo esc_url(gs_blog_link()); ?>">すべての記事を見る <span aria-hidden="true">↗</span></a></div>
  <div class="journal-grid">
  <?php $posts = new WP_Query(array('posts_per_page'=>3,'post_status'=>'publish','ignore_sticky_posts'=>true));
  if ($posts->have_posts()): while ($posts->have_posts()): $posts->the_post(); gs_card(); endwhile; wp_reset_postdata();
  else: ?>
   <div class="empty-journal"><span class="eyebrow">COMING SOON</span><h3>Good Space Journal</h3><p>民泊運営、空室活用、レンタルスペース運営に関する記事を準備中です。</p><a class="text-link" href="<?php echo esc_url(gs_blog_link()); ?>">ブログへ <span aria-hidden="true">↗</span></a></div>
  <?php endif; ?>
  </div>
 </div>
</section>
</main>
<?php get_footer(); ?>
