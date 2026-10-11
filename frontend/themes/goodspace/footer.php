<footer class="site-footer">
  <div class="container">
    <div class="footer-head"><div>
      <span class="eyebrow eyebrow-light">LET'S TALK</span>
      <h2>空間の次の可能性を、<br>一緒に考えませんか。</h2>
      <p>運営代行や物件活用について、まずはご相談ください。</p>
    </div><a class="button button-accent" href="<?php echo gs_link('/contact/'); ?>">お問い合わせ <span aria-hidden="true">↗</span></a></div>
    <div class="footer-grid">
      <div class="footer-about"><a class="footer-brand" href="<?php echo esc_url(home_url('/')); ?>">Good Space<span>®</span></a><p>Space Operations & Design</p><address>株式会社Good Space<br>東京都新宿区大久保2丁目21-10-101号室</address></div>
      <nav aria-label="フッターメニュー"><strong>COMPANY</strong><a href="<?php echo gs_link('/'); ?>">ホーム</a><a href="<?php echo gs_link('/company/'); ?>">会社概要</a><a href="<?php echo gs_link('/contact/'); ?>">お問い合わせ</a></nav>
      <nav aria-label="サービスリンク"><strong>SERVICES</strong><a href="<?php echo gs_link('/service/minpaku/'); ?>">民泊運営代行</a><a href="<?php echo gs_link('/service/rental-space/'); ?>">レンタルスペース運営</a><a href="https://service.good-space.jp/" target="_blank" rel="noopener noreferrer">スペースを探す ↗</a></nav>
      <nav aria-label="ブログとポリシー"><strong>MORE</strong><a href="<?php echo esc_url(gs_blog_link()); ?>">ブログ</a><?php $privacy_page=get_page_by_path('privacy'); if ($privacy_page && get_post_status($privacy_page->ID) === 'publish'): ?><a href="<?php echo gs_link('/privacy/'); ?>">プライバシーポリシー</a><?php endif; ?><a href="mailto:info@good-space.jp">info@good-space.jp</a></nav>
    </div>
    <div class="footer-bottom"><small>© <?php echo esc_html(gmdate('Y')); ?> Good Space. All rights reserved.</small><a href="#top">BACK TO TOP ↑</a></div>
  </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
