#!/usr/bin/env bash
# Install daily AI publishing watchdog without affecting other sites or crontabs.
set -Eeuo pipefail
if [[ $(id -u) -ne 0 ]]; then echo 'Run as root' >&2; exit 1; fi
cd /srv/goodspace-main
if ! command -v cron >/dev/null 2>&1; then
  echo 'cron is not installed. Run apt-get update && apt-get install -y cron, then rerun.' >&2
  exit 1
fi
if ! systemctl is-active --quiet cron; then
  echo 'cron.service is not active. Investigate before enabling it; schedule NOT installed.' >&2
  exit 1
fi
if [[ $(date +%Z) != JST ]]; then
  echo "Server timezone is $(date +%Z), not JST. Verify timezone before installing schedule." >&2
  exit 1
fi
docker compose --profile maintenance config -q
docker compose --profile maintenance run --rm -T wpcli wp help gsai >/dev/null
docker compose --profile maintenance run --rm -T wpcli wp help gsai refill >/dev/null
install -m 0750 scripts/goodspace_daily_run.sh /usr/local/sbin/goodspace-ai-daily
cat > /etc/cron.d/goodspace-ai-daily <<'CRON'
# Good Space only; retry during the publication day, in server-local JST.
SHELL=/bin/bash
PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin
15 0 * * * root /usr/local/sbin/goodspace-ai-daily refill
5,20,35,50 7 * * * root /usr/local/sbin/goodspace-ai-daily
5 8-23 * * * root /usr/local/sbin/goodspace-ai-daily
CRON
chmod 644 /etc/cron.d/goodspace-ai-daily
cat > /etc/logrotate.d/goodspace-ai-daily <<'ROTATE'
/var/log/goodspace/daily-publish.log {
    daily
    rotate 14
    compress
    missingok
    notifempty
    create 0600 root root
}
ROTATE
echo 'Good Space daily publishing schedule installed.'
echo 'Theme refill schedule: 00:15 JST (rolling 14-day queue).'
echo 'Schedule: 07:05, 07:20, 07:35, 07:50; then 08:05-23:05 (JST).'
echo 'Posts created today prevent duplicates; no existing Docker application is restarted.'
