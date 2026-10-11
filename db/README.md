# MariaDBデータベースの構成

`docker-compose.yml`の`mariadb:11.4`を初回導入時に自動ダウンロードして起動します。MariaDBは`db_data`というDocker永続ボリュームに保存されます。

WordPressテーブルと初期データは`scripts/initialize_site.sh`の`wp core install`で自動作成します。既存のgood-space.jpのDBは含まれません。

このプロジェクトは**オンラインインストール**です。WordPress本体やMariaDBのDockerイメージそのものをZIPには保存していません。UbuntuサーバーからDocker Hub等にインターネットアクセスが必要です。稼働後はWP本体/DB/画像がDockerボリューム内に保持されます。

DBの3306番ポートは外部へ公開しません。WordPressのWebポートも初期状態では127.0.0.1のみにバインドします。
