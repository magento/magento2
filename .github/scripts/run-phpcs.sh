#!/usr/bin/env bash
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
# Usage: run-phpcs.sh <label> <file-list> [phpcs-flags...]
set -euo pipefail

LABEL="$1"
FILE_LIST="$2"
shift 2

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
NOISE_FILTER_FILE="$SCRIPT_DIR/phpcs-noise-filter.txt"

if [ ! -s "$FILE_LIST" ]; then
  echo "No $LABEL files to check, skipping phpcs."
  exit 0
fi

phpcs_tmp=$(mktemp)
phpcs_exit=0
mcs/vendor/bin/phpcs \
  --standard=Magento2 \
  "$@" \
  --report=full \
  --no-colors \
  --file-list="$FILE_LIST" > "$phpcs_tmp" 2>&1 || phpcs_exit=$?

if [ $phpcs_exit -eq 0 ] && grep -qE "^FOUND [0-9]+ (ERROR|WARNING)" "$phpcs_tmp"; then phpcs_exit=1; fi
grep -vEf "$NOISE_FILTER_FILE" "$phpcs_tmp" || true
rm -f "$phpcs_tmp"
exit $phpcs_exit
