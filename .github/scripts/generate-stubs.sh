#!/usr/bin/env bash
#
# Copyright 2026 Adobe
# All Rights Reserved.
#
# Generates PHPStan stub files from use statements in the given files.
# Called by the CI workflow with the list of changed PHP files.
#
# Usage: .github/scripts/generate-stubs.sh <file-list>
#   <file-list>: path to a text file with one PHP file path per line
#
# Output: /tmp/magento-stubs.php

set -euo pipefail

FILE_LIST="${1:?Usage: generate-stubs.sh <file-list>}"
STUB_FILE="/tmp/magento-stubs.php"

if [ ! -f "$FILE_LIST" ]; then
  echo "File list not found: $FILE_LIST"
  exit 1
fi

# Extract all 'use Magento\...' from the changed files only, strip aliases
use_classes=$(cat "$FILE_LIST" | while IFS= read -r f; do
  [ -n "$f" ] && [ -f "$f" ] && grep -h '^use Magento\\' "$f" 2>/dev/null || true
done | sed -E 's/^use[[:space:]]+//;s/[[:space:]]+as[[:space:]].*//;s/[[:space:]]*;[[:space:]]*$//' | sort -u)

# Also extract fully-qualified parent classes from 'extends' clauses.
# Catches cases like: class Foo extends \Magento\Catalog\...\Bar
# where no matching 'use' statement exists in the changed file.
extends_classes=$(cat "$FILE_LIST" | while IFS= read -r f; do
  [ -n "$f" ] && [ -f "$f" ] && grep -hE '\bextends\b' "$f" 2>/dev/null | grep -oE '\\?Magento(\\[A-Za-z0-9_]+)+' | sed 's/^\\//' || true
done | sort -u)

# Also extract Magento interfaces from 'implements' clauses — stubs for these
# prevent "implements unknown interface Magento\..." errors in sparse clone.
implements_classes=$(cat "$FILE_LIST" | while IFS= read -r f; do
  [ -n "$f" ] && [ -f "$f" ] && grep -hE '\bimplements\b' "$f" 2>/dev/null | grep -oE '\\?Magento(\\[A-Za-z0-9_]+)+' | sed 's/^\\//' || true
done | sort -u)

classes=$(printf '%s\n%s\n%s\n' "$use_classes" "$extends_classes" "$implements_classes" | grep -v '^$' | sort -u)

if [ -z "$classes" ]; then
  echo "No Magento class references found in changed files."
  echo "<?php" > "$STUB_FILE"
  exit 0
fi

cat > "$STUB_FILE" << 'HEADER'
<?php
/**
 * Auto-generated Magento class stubs for PHPStan.
 * Contains minimal declarations so PHPStan can resolve
 * Magento framework classes without the full vendor/ tree.
 */
HEADER

# Phrase class must come first (used by __ function)
cat >> "$STUB_FILE" << 'PHRASE'

namespace Magento\Framework {
    class Phrase { public function __construct(string $text, array $args = []) {} public function __toString(): string { return ''; } }
}

namespace {
    if (!function_exists('__')) {
        function __($text, ...$args): \Magento\Framework\Phrase { return new \Magento\Framework\Phrase($text, $args); }
    }
}

PHRASE

# Track already declared FQCNs to avoid duplicate class/interface declarations
# (e.g. predeclared stubs or repeated use statements).
declare -A declared_fqcns
declared_fqcns["Magento\\Framework\\Phrase"]=1

current_ns=""
while IFS= read -r fqcn; do
  [ -z "$fqcn" ] && continue

  if [ -n "${declared_fqcns[$fqcn]:-}" ]; then
    continue
  fi
  declared_fqcns["$fqcn"]=1

  # Extract namespace and short class name
  short="${fqcn##*\\}"
  ns="${fqcn%\\$short}"

  # Start new namespace block if changed
  if [ "$ns" != "$current_ns" ]; then
    [ -n "$current_ns" ] && echo "}" >> "$STUB_FILE" && echo "" >> "$STUB_FILE"
    echo "namespace $ns {" >> "$STUB_FILE"
    current_ns="$ns"
  fi

  # Determine type by naming convention
  if echo "$short" | grep -qE 'Interface$'; then
    echo "    interface $short {}" >> "$STUB_FILE"
  elif echo "$short" | grep -qE 'Exception$'; then
    echo "    class $short extends \\Exception {}" >> "$STUB_FILE"
  elif echo "$short" | grep -qE 'Factory$'; then
    echo "    class $short { public function create(array \$data = []) {} }" >> "$STUB_FILE"
  else
    echo "    class $short {}" >> "$STUB_FILE"
  fi

done <<< "$classes"

# Close last namespace
[ -n "$current_ns" ] && echo "}" >> "$STUB_FILE"


count=$(grep -cE '^\s+(class|interface|abstract)' "$STUB_FILE")
echo "Generated $STUB_FILE with $count stub(s) from changed files."
