# Good Space WordPress + MariaDB + AIブログ

**開発版・本番未検証**。企業サイトの新規WordPressテーマ、ブログ、AI下書き作成プラグインと、Ubuntu上でWordPressとMariaDBを構築するスクリプトをまとめたプロジェクトです。

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

WordPressの「投稿」機能で手動記事を作れます。管理画面「Good Space AI」からOpenRouter APIキー・モデル、記事テーマ、確認済み事実、出典URLを登録し、AI記事を「下書き」保存します。AI下書きの自動生成は初期状態OFFであり、自動公開は行いません。

## 注意事項

- 配布物に既存good-space.jpのWordPress本体、DB、画像、問い合わせデータ等のコピーは含まれません。WordPress/MariaDBは初回導入時に**公式Dockerイメージをオンラインで取得**します。
- インターネット接続がないUbuntuではそのままインストールできません。Dockerイメージを含めたオフライン配布物ではありません。
- デザイン素材は開発用です。現行サイトの正式な会社情報・ロゴ・写真・個人情報保護方針の確認が必要です。
- `.env`、`初期ログイン情報.txt`、`backups/`は機密情報です。GitHubにアップロードしないでください。
- WordPressのフル起動試験や外部AI接続試験は実施していません。静的コード検査とアーカイブ整合検査のみです。
- 対応するWordPressのバージョンはイメージ更新により変わります。アップデート時はバックアップが必要です。

詳しい導入・本番作業は`README_導入手順.md`、`docs/公開前チェックリスト.md`を参照してください。
