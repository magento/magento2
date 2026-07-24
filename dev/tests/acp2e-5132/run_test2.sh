#!/usr/bin/env bash
#
# Finding 1 — single-regenerator concurrency test.
#
# For each trial: reproduce the "L1 warm, remote :hash gone" state, then release N synchronized
# worker processes that all load the same id at the same microtime barrier. Exactly one worker
# should be elected regenerator (load() === false). Reports how many trials elected >1.
#
# Usage: ./run_test2.sh [frontend_id] [workers] [trials]
# Defaults: stale_cache_enabled 8 20
#
set -u

DIR="$(cd "$(dirname "$0")" && pwd)"
PHP="${PHP_BIN:-php}"
FRONTEND="${1:-stale_cache_enabled}"
WORKERS="${2:-8}"
TRIALS="${3:-20}"

multi=0
total_regen=0

echo "== Test 2: single regenerator =="
echo "frontend=$FRONTEND workers=$WORKERS trials=$TRIALS"

for t in $(seq 1 "$TRIALS"); do
  ID="ACP_T2_$(date +%s%N)_$t"
  OUT="$(mktemp)"

  # Reproduce the election precondition.
  if ! "$PHP" "$DIR/test2_worker.php" --mode=setup --frontend="$FRONTEND" --id="$ID" >/dev/null 2>&1; then
    echo "  trial $t: SETUP FAILED (skipping)"
    rm -f "$OUT"
    continue
  fi

  # Barrier ~2s in the future gives every worker time to boot before releasing together.
  START="$("$PHP" -r 'echo microtime(true) + 2.0;')"

  for w in $(seq 1 "$WORKERS"); do
    "$PHP" "$DIR/test2_worker.php" --mode=worker --frontend="$FRONTEND" \
      --id="$ID" --start="$START" --out="$OUT" &
  done
  wait

  regen="$(grep -c '^REGEN$' "$OUT" || true)"
  total_regen=$((total_regen + regen))
  if [ "$regen" -ne 1 ]; then
    multi=$((multi + 1))
    echo "  trial $t: regenerators=$regen  <-- VIOLATION (expected 1)"
  fi
  rm -f "$OUT"
done

echo "-------------------------------------------"
echo "trials=$TRIALS  total_regenerators=$total_regen  expected=$TRIALS"
echo "trials_with_multiple_regenerators=$multi"
if [ "$multi" -eq 0 ]; then
  echo "[PASS] exactly one regenerator in every trial"
  exit 0
else
  echo "[FAIL] $multi trial(s) elected more than one regenerator"
  exit 1
fi
