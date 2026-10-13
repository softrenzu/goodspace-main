#!/usr/bin/env bash
# Scheduled Good Space AI one-post-per-JST-day check. No other Docker project is touched.
set -Eeuo pipefail
cd /srv/goodspace-main
mkdir -p /var/log/goodspace
timestamp="$(date -Is)"
if output="$(flock -n /run/lock/goodspace-ai-daily.lock \
    docker compose --profile maintenance run --rm -T wpcli wp gsai daily 2>&1)"; then
  printf '%s %s\n' "$timestamp" "$output" >> /var/log/goodspace/daily-publish.log
else
  code=$?
  printf '%s ERROR(exit=%s): %s\n' "$timestamp" "$code" "$output" >> /var/log/goodspace/daily-publish.log
  exit "$code"
fi
