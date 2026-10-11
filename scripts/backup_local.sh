#!/usr/bin/env bash
# Local backup: database + WordPress content volume; store offsite separately.
set -euo pipefail
cd "$(dirname "$0")/.."
set -a; source .env; set +a
umask 077
stamp="$(date +%Y%m%d_%H%M%S)"
dest="backups/${stamp}"
mkdir -p "$dest"
docker compose exec -T db sh -c 'mariadb-dump --single-transaction -u"$MARIADB_USER" -p"$MARIADB_PASSWORD" "$MARIADB_DATABASE"' > "$dest/database.sql"
docker compose exec -T wordpress sh -c 'cd /var/www/html && tar -czf - wp-content/uploads' > "$dest/uploads.tar.gz"
sha256sum "$dest/database.sql" "$dest/uploads.tar.gz" > "$dest/checksums.sha256"
echo "ローカルバックアップ: $dest"
echo '注意: オフサイト保管と復元テストは別途必要です。'
