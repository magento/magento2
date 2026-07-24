# ACP2E-5132 — Symfony L2 cache validation kit

Manual harness to validate the revision-6 fixes for the Symfony L2 cache. It drives the **real**
cache stack (`SymfonyL2Cache`, `RedisTagAdapter`, `FilesystemTagAdapter`) through the configured
frontends and asserts against live Valkey / `/dev/shm` state.

> Not part of the shipped patch. It lives under `dev/tests/` as an operator tool. Nothing here is
> loaded by the application at runtime.

## What each test maps to

| Test | Finding it validates | Fix under test |
|------|----------------------|----------------|
| `test1_compression.php`   | Compression default | `Factory::isCompressionEnabled` reverted to opt-in |
| `run_test2.sh`            | #1 non-atomic lock / multiple regenerators | `RedisTagAdapter::acquireLock` (SET NX EX) |
| `test3_lock_release.php`  | #2 lock never released / #4 stale-until-TTL | `RedisTagAdapter::releaseLock` + release in `SymfonyL2Cache::save` |
| `run_test4.sh`            | #3 filesystem tag RMW race | `FilesystemTagAdapter::mutateTagFileLocked` (flock) |
| `run_test5.sh`            | #4 Valkey delete/save index race | `RedisTagAdapter` atomic EVAL prune |
| `test6_lua_parity.php`    | #5 Lua clean skips index cleanup | index maintenance added to the Lua clean scripts |

## Prerequisites

- Run on the **Linux** local/staging box (uses `flock`, `redis-cli` semantics, synchronized
  multi-process workers). Do **not** run on the Windows dev box.
- The patch applied and `bin/magento setup:di:compile` run.
- `CACHE_CONFIGURATION` with two L2 frontends, matching your staging config:
  - a plain `symfony_l2` frontend (default: `default`)
  - a `use_stale_cache: true` frontend (default: `stale_cache_enabled`)
- `php` on `PATH` (or set `PHP_BIN`).

## Running

```bash
cd <magento-root>
chmod +x dev/tests/acp2e-5132/*.sh

# everything, with a summary:
PHP_BIN=php STALE_FRONTEND=stale_cache_enabled L2_FRONTEND=default \
  bash dev/tests/acp2e-5132/run_all.sh

# or individually:
php  dev/tests/acp2e-5132/test1_compression.php
bash dev/tests/acp2e-5132/run_test2.sh stale_cache_enabled 8 20
php  dev/tests/acp2e-5132/test3_lock_release.php stale_cache_enabled
bash dev/tests/acp2e-5132/run_test4.sh stale_cache_enabled 8 100 5
bash dev/tests/acp2e-5132/run_test5.sh default 3 3 100
php  dev/tests/acp2e-5132/test6_lua_parity.php default
```

If your repo root is not three levels up from this folder, set `MAGENTO_ROOT`:
```bash
MAGENTO_ROOT=/var/www/html php dev/tests/acp2e-5132/test1_compression.php
```

## Expected results (fix applied)

- **Test 1** — every case PASS; `unset`, `'0'`, `0`, `'false'` → off; `'1'`, `1` → on.
- **Test 2** — `total_regenerators == trials` and `trials_with_multiple_regenerators == 0`
  (exactly one regenerator per trial). On the old code you saw ~25% of trials with >1.
- **Test 3** — first reader elected (MISS); after `save()`, an immediate re-invalidation elects a
  new regenerator in a few ms (`<< 10 000 ms` TTL). On the old code the second load returned stale
  and no new election happened until the TTL expired.
- **Test 4** — every trial reports `expected == present`; no lost tag associations. Old code lost
  a fraction of ids under contention.
- **Test 5** — `inconsistent_end_states == 0`: every trial ends fully present or fully absent. Old
  code left data-present / index-missing orphans.
- **Test 6** — `tag set pruned` and `all_ids pruned` both PASS (SKIP if Lua isn't available on the
  client). Old code left the deleted ids orphaned in the tag SETs and `all_ids`.

## A/B against the current (pre-fix) build

To show the fixes matter, run the same kit on the `-5` build (or `git stash` the patch), capture
the numbers, then re-run on `-6`. Tests 2, 4, 5 are probabilistic — raise the trial/worker counts
(e.g. `run_test2.sh <f> 16 50`) if you want a tighter signal on the old build.

## Notes / limitations

- Tests use ids/tags in `[A-Z0-9_]` so Magento's id/tag normalization is the identity and direct
  adapter calls address exactly the keys the frontend wrote. Don't change them to mixed case.
- Test 5's atomic prune path is active on **phpredis standalone**; on Predis/cluster the adapter
  falls back to the pipelined prune and a narrow residual race remains (documented in the patch).
- Test 6 reflects into private members (`namespace`, `cache`) to build a Lua-enabled adapter on the
  live pool. That is a test-only shortcut, not something the application does.
- These tests write throwaway keys/files (`ACP_T*`). They clean up after themselves on a best-effort
  basis; a `bin/magento cache:flush` afterwards clears any residue.
```
