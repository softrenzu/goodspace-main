#!/usr/bin/env bash
# Scheduled Good Space AI one-post-per-JST-day check. No other Docker project is touched.
set -Eeuo pipefail
cd /srv/goodspace-main
mkdir -p /var/log/goodspace
timestamp="$(date -Is)"
mode="${1:-daily}"
if [[ "$mode" != "daily" && "$mode" != "refill" ]]; then
  echo "Unknown mode: $mode" >&2
  exit 2
fi
if output="$(flock -n /run/lock/goodspace-ai-daily.lock \
    docker compose --profile maintenance run --rm -T wpcli wp gsai "$mode" 2>&1)"; then
  printf '%s [%s] %s\n' "$timestamp" "$mode" "$output" >> /var/log/goodspace/daily-publish.log
else
  code=$?
  printf '%s [%s] ERROR(exit=%s): %s\n' "$timestamp" "$mode" "$code" "$output" >> /var/log/goodspace/daily-publish.log
  exit "$code"
fi
