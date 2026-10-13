# Good Space WordPress + MariaDB + AIブログ

**開発版・本番未検証**。企業サイトの新規WordPressテーマ、ブログ、AI記事自動公開プラグインと、Ubuntu上でWordPressとMariaDBを構築するスクリプトをまとめたプロジェクトです。

## Ubuntuで一括セットアップ

Ubuntu 22.04 / 24.04 / 26.04（x86_64またはARM64）、インターネット接続、`sudo`権限が必要です。

```bash
# GitHubからclone、または配布ZIPを解凍したプロジェクト内で
sudo bash scripts/install_ubuntu.sh
```

この1回の実行でDocker Engine/Composeの導入（未導入の場合）、ランダムなDB/WordPress管理者パスワード生成、WordPress PHP 8.3イメージとMariaDB 11.4イメージのダウンロード、データベース作成、WordPress初期セットアップ、オリジナルテーマとAIプラグインの有効化、ブログとサービスページ作成まで実施します。

管理画面URLはデフォルトで `http://localhost:8080/wp-admin/`。IDとパスワードは`初期ログイン情報.txt`に保存します（管理者専用、Git管理対象外）。

**リモートUbuntuから閲覧するときはSSHトンネルでlocalhost:8080を転送してください。** インターネットへWeb管理画面をHTTPで直接公開しない設計です。本番ドメインに切り替える前にHTTPS・DNS・既存サイトバックアップ・既存URLリダイレクトを設定してください。

### オプション

```bash
# インストール前にステージングURLと通知先メールを指定
sudo GOODSPACE_URL=https://staging.example.jp GOODSPACE_ADMIN_EMAIL=your-real-email@example.jp bash scripts/install_ubuntu.sh
```

上記はWordPress内部のURL設定です。HTTPS公開用のリバースプロキシとDNS設定は自動では作成しません。URLを指定してもWeb公開されるわけではありません。

### 操作

```bash
docker compose ps                  # 起動状態
docker compose logs -f wordpress   # WordPressログ
sudo bash scripts/backup_local.sh  # DBと画像をローカルバックアップ
docker compose down                # 停止（DBの永続ボリュームは保持）
docker compose up -d               # 再起動
```

`docker compose down -v`は**DBとWordPressの全データを消去**するため実行しないでください。

## ブログとAI記事

WordPressの「投稿」機能で手動記事を作れます。管理画面「Good Space AI」からOpenRouter APIキー・モデル、記事テーマ、確認済み事実、出典URLを登録し、AI記事を品質チェック後にWordPressへ公開します。毎朝07:00ごろの自動生成・公開は初期状態OFFなので、Good Space AI管理画面で有効化し、確認済みテーマを登録してください。記事テーマがなくなると新しい記事は生成されません。

## 注意事項

- 配布物に既存good-space.jpのWordPress本体、DB、画像、問い合わせデータ等のコピーは含まれません。WordPress/MariaDBは初回導入時に**公式Dockerイメージをオンラインで取得**します。
- インターネット接続がないUbuntuではそのままインストールできません。Dockerイメージを含めたオフライン配布物ではありません。
- デザイン素材は開発用です。現行サイトの正式な会社情報・ロゴ・写真・個人情報保護方針の確認が必要です。
- `.env`、`初期ログイン情報.txt`、`backups/`は機密情報です。GitHubにアップロードしないでください。
- WordPressのフル起動試験や外部AI接続試験は実施していません。静的コード検査とアーカイブ整合検査のみです。
- 対応するWordPressのバージョンはイメージ更新により変わります。アップデート時はバックアップが必要です。

詳しい導入・本番作業は`README_導入手順.md`、`docs/公開前チェックリスト.md`を参照してください。


## 毎日1記事の公開・自動リトライ（2026-10追加）

管理画面「Good Space AI」の定期生成がONなら、毎日JST 7時以降に当日公開済み記事の有無を調べます。当日すでに通常記事が公開されていれば追加しません。未公開なら、登録済みの未使用テーマを優先し、ない場合はサービス業務の編集カレンダーから自動でテーマを作成します。AI APIが失敗した場合は、数値や架空実績を含めない一般的な運営チェックノートを代わりに公開します。API障害と代替記事発行はWordPressに記録します。

記事テーマはWordPressのGood Space AI画面で14日分の候補を確認できます。JST 00:15に自動補充し、古い未使用の自動テーマを退避します。手入力したテーマは消しません。CLIで即時補充する場合は `docker compose --profile maintenance run --rm -T wpcli wp gsai refill` を実行します。

Ubuntu側の定期実行は別途 `sudo bash scripts/install_daily_schedule.sh` で有効化します。JST 07:05/07:20/07:35/07:50および08:05〜23:05毎時に確認し、失敗時に再試行します。実行ログは `/var/log/goodspace/daily-publish.log`。手動確認は `docker compose --profile maintenance run --rm -T wpcli wp gsai status`。この設定はVPSへの導入後にのみ有効になります。

**重要:** サーバー・DB・DNSの停止やネットワーク障害まで含めた「絶対に1日も欠かさない」保証はできません。代替記事も実務向けの定型記事であるため、事後確認と定期的な品質レビューが必要です。自動公開は自社サイトに誤りが出るリスクを伴います。