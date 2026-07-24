#!/usr/bin/env bash
#
# Finding 3 — filesystem tag-index read-modify-write race.
#
# N workers each add M distinct ids to one fresh L1 tag file, all released at the same barrier.
# All N*M ids must survive. Repeats across trials because the race is probabilistic.
#
# Usage: ./run_test4.sh [frontend_id] [workers] [ids_per_worker] [trials]
# Defaults: stale_cache_enabled 8 100 5
#
set -u

DIR="$(cd "$(dirname "$0")" && pwd)"
PHP="${PHP_BIN:-php}"
FRONTEND="${1:-stale_cache_enabled}"
WORKERS="${2:-8}"
PERWORKER="${3:-100}"
TRIALS="${4:-5}"

EXPECTED=$((WORKERS * PERWORKER))
fail=0

echo "== Test 4: filesystem tag RMW race =="
echo "frontend=$FRONTEND workers=$WORKERS ids/worker=$PERWORKER expected/trial=$EXPECTED trials=$TRIALS"

for t in $(seq 1 "$TRIALS"); do
  TAG="ACP_T4_$(date +%s%N)_$t"          # fresh tag per trial => no cleanup needed
  START="$("$PHP" -r 'echo microtime(true) + 2.0;')"

  for w in $(seq 1 "$WORKERS"); do
    "$PHP" "$DIR/test4_worker.php" --mode=worker --frontend="$FRONTEND" \
      --tag="$TAG" --worker="$w" --count="$PERWORKER" --start="$START" &
  done
  wait

  if "$PHP" "$DIR/test4_worker.php" --mode=verify --frontend="$FRONTEND" \
      --tag="$TAG" --expected="$EXPECTED"; then
    echo "  trial $t: OK"
  else
    echo "  trial $t: LOST UPDATES"
    fail=$((fail + 1))
  fi
done

echo "-------------------------------------------"
if [ "$fail" -eq 0 ]; then
  echo "[PASS] no lost tag associations across $TRIALS trials"
  exit 0
else
  echo "[FAIL] $fail trial(s) lost tag associations"
  exit 1
fi
