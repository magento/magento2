#!/usr/bin/env bash
#
# Finding 4 — Valkey delete/save index race.
#
# Per trial: seed X, then release S savers and D deleters on the same X at one barrier and assert
# the end state is internally consistent (fully present or fully absent — never a data-present /
# index-missing orphan). Runs many trials because the interleaving is probabilistic.
#
# Usage: ./run_test5.sh [frontend_id] [savers] [deleters] [trials]
# Defaults: default 3 3 100
#
set -u

DIR="$(cd "$(dirname "$0")" && pwd)"
PHP="${PHP_BIN:-php}"
FRONTEND="${1:-default}"
SAVERS="${2:-3}"
DELETERS="${3:-3}"
TRIALS="${4:-100}"

orphans=0

echo "== Test 5: Valkey delete/save index race =="
echo "frontend=$FRONTEND savers=$SAVERS deleters=$DELETERS trials=$TRIALS"

for t in $(seq 1 "$TRIALS"); do
  KEY="ACP_T5_$(printf '%d' "$t")_$(date +%N)"   # [A-Z0-9_] only (uppercase base + digits)
  KEY="$(echo "$KEY" | tr '[:lower:]' '[:upper:]')"
  TAG="ACP_T5_TAG"

  "$PHP" "$DIR/test5_worker.php" --mode=setup --frontend="$FRONTEND" --key="$KEY" --tag="$TAG" >/dev/null 2>&1

  START="$("$PHP" -r 'echo microtime(true) + 1.5;')"
  for s in $(seq 1 "$SAVERS"); do
    "$PHP" "$DIR/test5_worker.php" --mode=save --frontend="$FRONTEND" --key="$KEY" --tag="$TAG" --start="$START" &
  done
  for d in $(seq 1 "$DELETERS"); do
    "$PHP" "$DIR/test5_worker.php" --mode=delete --frontend="$FRONTEND" --key="$KEY" --tag="$TAG" --start="$START" &
  done
  wait

  if ! "$PHP" "$DIR/test5_worker.php" --mode=verify --frontend="$FRONTEND" --key="$KEY" --tag="$TAG" >/dev/null; then
    orphans=$((orphans + 1))
    echo "  trial $t: ORPHAN/inconsistent"
  fi

  # best-effort cleanup
  "$PHP" "$DIR/test5_worker.php" --mode=delete --frontend="$FRONTEND" --key="$KEY" --tag="$TAG" --start="$(${PHP} -r 'echo microtime(true);')" >/dev/null 2>&1
done

echo "-------------------------------------------"
echo "trials=$TRIALS  inconsistent_end_states=$orphans"
if [ "$orphans" -eq 0 ]; then
  echo "[PASS] every trial ended in a consistent (fully present / fully absent) state"
  exit 0
else
  echo "[FAIL] $orphans trial(s) left a data-present / index-missing orphan"
  exit 1
fi
