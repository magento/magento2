#!/usr/bin/env bash
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
# Usage: read-pkg-constraint.sh <composer.json> <package/name>
# Prints the version constraint for the package from require or require-dev.
set -euo pipefail

FILE="$1"
PKG="$2"

[ -f "$FILE" ] || exit 1

php -r '
  $j = json_decode(file_get_contents($argv[1]), true) ?: [];
  $p = $argv[2];
  $v = $j["require"][$p] ?? $j["require-dev"][$p] ?? "";
  echo is_string($v) ? $v : "";
' "$FILE" "$PKG"
