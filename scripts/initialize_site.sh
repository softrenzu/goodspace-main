#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."
if [[ ! -f .env ]]; then echo 'ERROR: .env を作成してください' >&2; exit 1; fi
set -a; source .env; set +a
if [[ "${WP_ADMIN_PASSWORD:-}" == CHANGE_* || "${WP_DB_PASSWORD:-}" == CHANGE_* || "${WP_DB_ROOT_PASSWORD:-}" == CHANGE_* ]]; then
  echo 'ERROR: .env の初期パスワードを変更してください' >&2; exit 1
fi
if [[ ! "${WP_SITE_URL:-}" =~ ^https?:// ]]; then echo 'ERROR: WP_SITE_URL を設定してください' >&2; exit 1; fi
if [[ "${WP_ADMIN_EMAIL:-}" == *example.invalid ]]; then echo 'ERROR: 管理者メールアドレスを実際のものに変更してください' >&2; exit 1; fi
wpcli() { docker compose --profile maintenance run --rm wpcli "$@"; }
docker compose up -d db wordpress
if ! wpcli core is-installed >/dev/null 2>&1; then
  wpcli core install --url="$WP_SITE_URL" --title='Good Space' --admin_user="$WP_ADMIN_USER" --admin_password="$WP_ADMIN_PASSWORD" --admin_email="$WP_ADMIN_EMAIL" --skip-email
fi
wpcli theme activate goodspace
wpcli plugin activate goodspace-ai-editor
wpcli option update timezone_string Asia/Tokyo
wpcli option update permalink_structure '/blog/%postname%/'
wpcli rewrite flush
create_page() {
  local slug="$1" title="$2" status="$3" parent="${4:-0}" id
  id="$(wpcli post list --post_type=page --post_status=any --field=ID --name="$slug" --format=ids | awk '{print $1}')"
  if [[ -z "$id" ]]; then
    id="$(wpcli post create --post_type=page --post_status="$status" --post_name="$slug" --post_title="$title" --post_parent="$parent" --porcelain)"
  fi
  printf '%s' "$id"
}
front_id="$(create_page home 'ホーム' publish)"
blog_id="$(create_page blog 'ブログ' publish)"
service_id="$(create_page service '事業内容' publish)"
create_page minpaku '民泊運営代行' publish "$service_id" >/dev/null
create_page rental-space 'レンタルスペース運営' publish "$service_id" >/dev/null
create_page company '会社概要' publish >/dev/null
create_page contact 'お問い合わせ' publish >/dev/null
# 正式な個人情報保護方針が確認されるまで下書きのまま保持。
create_page privacy 'プライバシーポリシー' draft >/dev/null
wpcli option update show_on_front page
wpcli option update page_on_front "$front_id"
wpcli option update page_for_posts "$blog_id"
wpcli option update blogdescription '空間の可能性を、事業の価値へ。'
wpcli rewrite flush
printf '\n設定が完了しました。管理画面: %s/wp-admin/\n' "${WP_SITE_URL%/}"
echo '※公開前にプライバシーポリシー・会社情報・DNS・SSL・表示確認が必要です。'
