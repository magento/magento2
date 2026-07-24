#!/usr/bin/env bash
#
# ACP2E-5132 validation kit — run every check and summarize.
#
# Usage:
#   ./run_all.sh
# Env:
#   PHP_BIN         php binary (default: php)
#   STALE_FRONTEND  frontend id with use_stale_cache=true (default: stale_cache_enabled)
#   L2_FRONTEND     a plain symfony_l2 frontend id        (default: default)
#
set -u

DIR="$(cd "$(dirname "$0")" && pwd)"
PHP="${PHP_BIN:-php}"
STALE="${STALE_FRONTEND:-stale_cache_enabled}"
L2="${L2_FRONTEND:-default}"

pass=0
fail=0
run() { # <name> <cmd...>
  echo; echo "############################################################"
  echo "# $1"
  echo "############################################################"
  if "${@:2}"; then pass=$((pass+1)); echo ">> $1: PASS"; else fail=$((fail+1)); echo ">> $1: FAIL"; fi
}

run "Test 1 — compression opt-in"          "$PHP" "$DIR/test1_compression.php"
run "Test 2 — single regenerator"          bash  "$DIR/run_test2.sh" "$STALE" 8 20
run "Test 3 — lock release"                "$PHP" "$DIR/test3_lock_release.php" "$STALE"
run "Test 4 — filesystem tag race"         bash  "$DIR/run_test4.sh" "$STALE" 8 100 5
run "Test 5 — Valkey delete/save race"     bash  "$DIR/run_test5.sh" "$L2" 3 3 100
run "Test 6 — Lua clean index parity"      "$PHP" "$DIR/test6_lua_parity.php" "$L2"

echo; echo "==================== SUMMARY ===================="
echo "passed=$pass failed=$fail"
[ "$fail" -eq 0 ] && echo "ALL GREEN" || echo "SOME CHECKS FAILED"
exit "$fail"
