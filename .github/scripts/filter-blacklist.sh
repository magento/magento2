#!/usr/bin/env bash
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
# Usage: filter-blacklist.sh <blacklist_dir> <file_list> [label]
# Reads file paths from <file_list>, removes entries matching any pattern in
# <blacklist_dir>/*.txt, and writes surviving paths to stdout one per line.
# Skipped files are reported to stderr as "<label> skip (blacklist): <file>".
set -euo pipefail

BLACKLIST_DIR="$1"
FILE_LIST="$2"
LABEL="${3:-skip}"

mapfile -t patterns < <(
  [ -d "$BLACKLIST_DIR" ] || exit 0
  cat "$BLACKLIST_DIR"/*.txt 2>/dev/null \
    | sed 's/[[:space:]]*$//' \
    | grep -vE '^[[:space:]]*$|^[[:space:]]*#' || true
)

while IFS= read -r f; do
  [ -n "$f" ] && [ -f "$f" ] || continue
  skip=false
  for p in "${patterns[@]+"${patterns[@]}"}"; do
    if [[ "$f" == $p ]] || [[ "$f" == *"$p"* ]]; then
      skip=true
      break
    fi
  done
  if [ "$skip" = true ]; then
    echo "$LABEL skip (blacklist): $f" >&2
  else
    echo "$f"
  fi
done < "$FILE_LIST"
