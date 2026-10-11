# Good Space WordPress 全面リニューアル 開発版

**状態: 本番未検証・公開不可。** 2026年10月11日作成。サイト実機への導入・DNS切替・本番API接続は未実施です。

## 同梱内容

- `frontend/themes/goodspace/` — 新規オリジナルWordPressテーマ。トップページ、事業ページ、ブログ、会社案内、問い合わせ窓口、スマートフォン対応。
- `frontend/plugins/goodspace-ai-editor/` — WordPress管理画面で確認済み情報を入力し、OpenRouter経由で記事を**下書き**生成するプラグイン。**自動公開機能はありません**。
- `docker-compose.yml` — MariaDB 11.4、WordPress PHP8.3、メンテナンス用WordPress CLI。
- `scripts/initialize_site.sh` — 初回ページ作成、URL設定、テーマ/プラグイン有効化。
- `scripts/backup_local.sh` — DBと投稿画像をローカル保存する簡易バックアップ。
- `preview/デザインプレビュー.html` — 画像・文言の見た目を確認する静的モック（WordPress本体ではありません）。

## Ubuntu 22.04/24.04/26.04 想定の導入

**推奨:** インターネットにつながるUbuntuで `sudo bash scripts/install_ubuntu.sh` を実行すると、Docker・DB・WordPress・初期サイトまで一括導入できます。手動導入する場合は以下を参照。

1. テスト用UbuntuにDocker EngineとDocker Compose pluginを導入。ポート80/443の公開・TLS終端を担当するnginx等は別途必要です。
2. ZIPを展開して、作業ディレクトリに入る。
3. `cp .env.example .env` を実行し、DBの二種類のパスワード、WP管理者パスワード、管理者メール、テスト環境の`WP_SITE_URL`を設定。**`.env`は外部公開しないこと。**
4. `docker compose up -d db wordpress`
5. `bash scripts/initialize_site.sh`
6. `http://localhost:8080/`（Ubuntu自身からアクセスした場合）で確認。Dockerの公開ポートはローカルホストのみに制限しています。リモートからはSSHトンネル等を使用してください。
7. 管理画面の「Good Space AI」でOpenRouter APIキーとモデルを設定。記事テーマと確認済み事実、実際の参考URLを登録して「AI記事を下書き生成」を押す。
8. WordPress「投稿」から内容確認・修正後に手動公開。初期設定では毎朝の自動生成も無効です。必要ならプラグイン画面で有効化。ただし作るのは下書きのみです。

### 本番公開で必要な設定

- HTTPS終端（nginx / Caddy 等）、証明書、ドメイン、リバースプロキシ、`WP_SITE_URL` およびWordPress `home`/`siteurl` の整合性。リバースプロキシ配下でHTTPSを検知できるWordPress設定。
- DNSの切替前に本番ドメインを使わないステージングでテストし、現行サイトの完全なバックアップを取得。別の予約サイト`service.good-space.jp`は切り替えない。
- 既存URL一覧の取得、301リダイレクト、Search Console/sitemap、タイトル・内部リンク・画像alt・Canonicalを確認。
- 正式な会社情報・写真・ロゴ・実績・料金・利用規約・個人情報保護方針を確認。**同梱プライバシーポリシーは下書きで作成され、正式文書の代わりではありません。**
- 問い合わせはメール・電話窓口のみです。Webフォームを追加する場合は、スパム対策・同意取得・メール配信・個人情報の保管先を設計。
- バックアップは別リージョン/別ストレージにコピーし、復元テストを行う。`backup_local.sh` はローカル保存までです。
- 管理画面の2要素認証、WordPress/PHP/プラグイン更新、管理者権限制限、監査ログ、WAF、APIキーのローテーション。

## AI下書き作成の仕様

- OpenRouter Chat Completions APIを使用。APIキーの登録と利用料金が必要です。モデルの提供状況・応答形式は別途確認が必要です。
- AIはニュース/法令/公式サイトのURLを**自動取得しません**。管理者が入力した確認済みの事実を文章化し、参考URLを末尾に付けます。
- WordPress管理画面の「Good Space AI」から記事テーマ・確認済み事実・参考URLを登録します。最低30文字の根拠記述とHTTPS URLが必要です。
- 同一テーマから再生成しないよう管理します。WordPressの下書き保存が成功した場合に処理済みにします。
- 任意で毎日JST 7:00ごろに未処理テーマから1件を下書き作成します。WordPress Cronはアクセス依存のため時刻保証なし。
- 法規制、料金、補助金、実績、収益、施設データはAIが誤り得ます。目視確認と一次資料の照合なしに公開しないでください。

## 参考元と権利

`HP-wordpress-main.zip` のWordPress・Docker・記事執筆の構成を参考にした**新規実装**です。JOE AI社のテーマ・画像・文章・APIキー・データベース・本番設定は流用していません。Good Spaceの公開事業情報は2026年10月時点に確認できた `https://good-space.jp/` と `https://service.good-space.jp/` の公開ページを参考にしました。素材SVGは今回新規制作した説明用イラストです。現行ロゴ/写真そのものではありません。

## 制限事項

このZIPは「開発版」です。別Ubuntuにコピーしても既存サイトの投稿・画像・ページのDBが移植されるわけではありません。また、Dockerを起動してWordPressインストールまでの実環境テスト、公開サイトとの画面比較、外部APIの実接続試験は実施していません。プレビューは静的モックです。
