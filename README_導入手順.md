# Good Space WordPress 全面リニューアル 開発版

**状態: 本番未検証・公開不可。** 2026年10月11日作成。サイト実機への導入・DNS切替・本番API接続は未実施です。

## 同梱内容

- `frontend/themes/goodspace/` — 新規オリジナルWordPressテーマ。トップページ、事業ページ、ブログ、会社案内、問い合わせ窓口、スマートフォン対応。
- `frontend/plugins/goodspace-ai-editor/` — WordPress管理画面で確認済み情報を入力し、OpenRouter経由で記事を**下書き**生成するプラグイン。テーマごとに登録した確認済み事実に基づいて生成し、品質チェックを通過した記事は自動公開されます。定期生成は初期設定OFFです。
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
8. 品質チェックを通った記事は自動的に公開されます。必要に応じてWordPress「投稿」から内容を点検・修正してください。毎朝7時ごろの自動生成・公開は初期状態OFFで、プラグイン画面で有効化します。実行には未処理の登録済みテーマが必要です。

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
- 同一テーマから再生成しないよう管理します。WordPressで公開処理が成功した場合に処理済みにします。
- 任意で毎日JST 7:00ごろに未処理テーマから1件を生成・公開します。WordPress Cronはアクセス依存のため時刻保証なし。
- 法規制、料金、補助金、実績、収益、施設データはAIが誤り得ます。記事テーマの登録前に一次資料を照合し、公開後も内容を確認してください。生成文は自動公開されるため、法令・料金・実績の不正確な情報が混入するリスクがあります。

## 参考元と権利

`HP-wordpress-main.zip` のWordPress・Docker・記事執筆の構成を参考にした**新規実装**です。JOE AI社のテーマ・画像・文章・APIキー・データベース・本番設定は流用していません。Good Spaceの公開事業情報は2026年10月時点に確認できた `https://good-space.jp/` と `https://service.good-space.jp/` の公開ページを参考にしました。素材SVGは今回新規制作した説明用イラストです。現行ロゴ/写真そのものではありません。

## 制限事項

このZIPは「開発版」です。別Ubuntuにコピーしても既存サイトの投稿・画像・ページのDBが移植されるわけではありません。また、Dockerを起動してWordPressインストールまでの実環境テスト、公開サイトとの画面比較、外部APIの実接続試験は実施していません。プレビューは静的モックです。


## 毎日1記事の公開・自動リトライ（2026-10追加）

管理画面「Good Space AI」の定期生成がONなら、毎日JST 7時以降に当日公開済み記事の有無を調べます。当日すでに通常記事が公開されていれば追加しません。未公開なら、登録済みの未使用テーマを優先し、ない場合はサービス業務の編集カレンダーから自動でテーマを作成します。AI APIが失敗した場合は、数値や架空実績を含めない一般的な運営チェックノートを代わりに公開します。API障害と代替記事発行はWordPressに記録します。

Ubuntu側の定期実行は別途 `sudo bash scripts/install_daily_schedule.sh` で有効化します。JST 07:05/07:20/07:35/07:50および08:05〜23:05毎時に確認し、失敗時に再試行します。実行ログは `/var/log/goodspace/daily-publish.log`。手動確認は `docker compose --profile maintenance run --rm -T wpcli wp gsai status`。この設定はVPSへの導入後にのみ有効になります。

**重要:** サーバー・DB・DNSの停止やネットワーク障害まで含めた「絶対に1日も欠かさない」保証はできません。代替記事も実務向けの定型記事であるため、事後確認と定期的な品質レビューが必要です。自動公開は自社サイトに誤りが出るリスクを伴います。
## 記事テーマの自動更新（2026年10月13日追加）

今後14日分の記事テーマを毎日0:15（JST）に自動補充します。管理画面の「AI記事テーマ」で予定候補を確認でき、Good Space AI編集画面の「記事テーマを今すぐ補充」でも即時作成できます。期限が過ぎた未使用の自動テーマは下書き状態に退避しますが、管理者が手入力したテーマは保持します。記事テーマは民泊・レンタルスペース運営の一般的な実務領域と改善観点をローテーションして作成するもので、外部の最新ニュースや制度改正を検証したものではありません。

既存VPSでの更新は、変更対象のPHPとスクリプトをバックアップしてからGitHub最新版を取得し、`bash scripts/install_daily_schedule.sh` を再実行してください。日次公開用のCronだけでなく、0:15の日次テーマ補充Cronもインストールされます。手動テストは `docker compose --profile maintenance run --rm -T wpcli wp gsai refill`、状況確認は `docker compose --profile maintenance run --rm -T wpcli wp gsai status` です。

## カテゴリー別ブログの反映（2026年10月15日）

WordPressテーマが有効なら、新しいブログ一覧 `/blog/` に8つのカテゴリーと検索が表示されます。各カテゴリーの一覧ページとスマートフォンの展開メニューから記事へ直接移動できます。

既存サーバーのテーマPHP/CSSとAIプラグインPHPだけをGitHubから更新し、既存の `.env`・`docker-compose.yml`・Nginx設定・データベースは変更しないでください。テーマのカテゴリーは初回アクセス時に作成されます。

未分類の記事だけをカテゴリー分けする場合は、記事・DBバックアップを取得し、`docker compose --profile maintenance run --rm -T -v "$PWD/scripts:/tmp/goodspace-scripts:ro" wpcli wp eval-file /tmp/goodspace-scripts/assign_blog_categories.php` を一度実行します。管理者が設定した既存カテゴリーは保持します。

新しいAI記事は登録されたテーマのカテゴリーかタイトルから推定して分類されます。定期投稿・代替投稿にもカテゴリーを割り当てます。
