# Good Space WordPress / Ubuntu 一括導入

Good Spaceの全面リニューアル用WordPressテーマ、AI記事下書きプラグイン、MariaDBをUbuntuで一括セットアップする開発版です。

**本番未検証。現行good-space.jpには未反映です。**

## インストール

Ubuntu 22.04 / 24.04 / 26.04、インターネット接続、sudo権限が必要です。

```bash
git clone https://github.com/softrenzu/goodspace-main.git
cd goodspace-main
bash bootstrap.sh
```

`bootstrap.sh`は同梱の圧縮ソース一式を展開し、`scripts/install_ubuntu.sh`を実行します。Dockerがなければインストールし、WordPress/PHP8.3、MariaDB11.4、WP-CLIを公式Dockerイメージから取得して初期化します。既存WordPressやDBは不要です。

管理画面は初期状態でローカルの `http://localhost:8080/wp-admin/` に限定され、ユーザー名・初期パスワードは `初期ログイン情報.txt` に生成されます。このファイルおよび `.env` はGitHubに含まれません。

**重要**: これはオフラインパッケージではありません。WordPress本体とMariaDB本体のコンテナイメージは、セットアップ時にネットからダウンロードします。また既存サイトの投稿・画像・顧客情報などは含まれません。

AI記事は管理画面で確認済み情報から**下書きだけ**作成し、自動公開しません。本番公開にはDNS/HTTPS設定、正式な会社情報への差替え、動作・セキュリティ検証、既存サイトバックアップが必要です。
