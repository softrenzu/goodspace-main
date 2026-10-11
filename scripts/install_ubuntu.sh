#!/usr/bin/env bash
# Good Space WordPress - Ubuntu one-command installer (requires Internet)
set -Eeuo pipefail
umask 077
cd "$(dirname "${BASH_SOURCE[0]}")/.."
PROJECT_DIR="$(pwd -P)"
ENV_FILE="$PROJECT_DIR/.env"
CRED_FILE="$PROJECT_DIR/初期ログイン情報.txt"

log() { printf '\n[goodspace] %s\n' "$*"; }
die() { printf '[goodspace] ERROR: %s\n' "$*" >&2; exit 1; }

if [[ "$(id -u)" -ne 0 ]]; then die 'sudo bash scripts/install_ubuntu.sh で実行してください'; fi
source /etc/os-release
[[ "${ID:-}" == "ubuntu" ]] || die '対応OSはUbuntu 22.04 / 24.04 / 26.04（Docker対応の64bit環境）です'
[[ "${VERSION_ID:-}" == "22.04" || "${VERSION_ID:-}" == "24.04" || "${VERSION_ID:-}" == "26.04" ]] || die 'Ubuntuのバージョンを確認してください（22.04 / 24.04 / 26.04対応）'
[[ "$(uname -m)" == "x86_64" || "$(uname -m)" == "aarch64" ]] || die 'CPUはx86_64 / ARM64を使用してください'
command -v curl >/dev/null || { apt-get update -qq; DEBIAN_FRONTEND=noninteractive apt-get install -y curl ca-certificates; }
if ! command -v docker >/dev/null || ! docker compose version >/dev/null 2>&1; then
  log 'Docker EngineとDocker Composeを公式APTリポジトリからインストールします'
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get install -y ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  . /etc/os-release
  arch="$(dpkg --print-architecture)"
  printf 'Types: deb\nURIs: https://download.docker.com/linux/ubuntu\nSuites: %s\nComponents: stable\nArchitectures: %s\nSigned-By: /etc/apt/keyrings/docker.asc\n' "$VERSION_CODENAME" "$arch" > /etc/apt/sources.list.d/docker.sources
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
fi
systemctl enable --now docker

if [[ ! -f "$ENV_FILE" ]]; then
  log '初期DB・WordPress管理者パスワードを自動生成します'
  command -v openssl >/dev/null || { apt-get update -qq; DEBIAN_FRONTEND=noninteractive apt-get install -y openssl; }
  rand() { openssl rand -hex 24; }
  port="${GOODSPACE_PORT:-8080}"
  [[ "$port" =~ ^[0-9]+$ ]] && ((port>=1024&&port<=65535)) || die 'GOODSPACE_PORTは1024〜65535の整数にしてください'
  site_url="${GOODSPACE_URL:-http://localhost:$port}"
  [[ "$site_url" =~ ^https?://[^[:space:]]+$ ]] || die 'GOODSPACE_URLにはhttp(s)のURLを指定してください'
  admin_email="${GOODSPACE_ADMIN_EMAIL:-admin@good-space.jp}"
  [[ "$admin_email" =~ ^[^[:space:]@]+@[^[:space:]@]+\.[^[:space:]@]+$ ]] || die 'GOODSPACE_ADMIN_EMAILの形式が不正です'
  admin_user='goodspace_admin'
  admin_pass="$(rand)"; db_pass="$(rand)"; root_pass="$(rand)"
  cat > "$ENV_FILE" <<EOV
COMPOSE_PROJECT_NAME=goodspace_wp
WP_DB_NAME=goodspace
WP_DB_USER=goodspace
WP_DB_PASSWORD=$db_pass
WP_DB_ROOT_PASSWORD=$root_pass
WP_LOCAL_PORT=$port
WP_SITE_URL=$site_url
WP_ADMIN_USER=$admin_user
WP_ADMIN_PASSWORD=$admin_pass
WP_ADMIN_EMAIL=$admin_email
EOV
  chmod 600 "$ENV_FILE"
  cat > "$CRED_FILE" <<EOV
Good Space WordPress 初期ログイン情報
作成日時: $(date -Is)
管理画面URL: ${site_url%/}/wp-admin/
ユーザー名: $admin_user
パスワード: $admin_pass

このファイルと.envには機密情報が含まれます。
GitHub、メール、Web公開ディレクトリに保存しないでください。
テスト環境の初期パスワードは本番切替前に変更してください。
EOV
  chmod 600 "$CRED_FILE"
else
  log '.envがあるため既存のデータベースと初期認証情報を変更せず再利用します'
fi

log 'Compose設定を検証し、WordPressとMariaDBの公式イメージを取得します（インターネット必須）'
docker compose config -q
docker compose --profile maintenance pull db wordpress wpcli
docker compose up -d db wordpress

log 'WordPress本体の初期展開を確認します'
ready=0
for i in $(seq 1 90); do
  if docker compose exec -T wordpress test -s /var/www/html/wp-includes/version.php >/dev/null 2>&1; then ready=1; break; fi
  sleep 2
done
[[ "$ready" == 1 ]] || die 'WordPressのファイル展開がタイムアウトしました。docker compose logs wordpress を確認してください'

log 'WordPress初期セットアップとGood Spaceテーマ/AIプラグインの有効化'
bash scripts/initialize_site.sh

log 'サービス状態'
docker compose ps
printf '\n[goodspace] 導入完了。初期ログイン情報: %s\n' "$CRED_FILE"
printf '[goodspace] localhostのみ公開しています。外部PCからはSSHトンネルでアクセスしてください:\n'
printf '  ssh -L %s:127.0.0.1:%s ubuntu@<サーバーIP>\n' "${GOODSPACE_PORT:-8080}" "${GOODSPACE_PORT:-8080}"
printf '[goodspace] 本番公開には別途DNS・HTTPS・セキュリティ設定と事前テストが必要です。\n'
