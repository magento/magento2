<?php
/**
 * Findings 2 & 4-stale (lock release + no stale-until-TTL). Sequential single-process test.
 *
 * Flow:
 *   1. Reproduce election precondition (L1 warm, remote :hash gone).
 *   2. load() -> expect MISS (this reader is elected regenerator; it now holds the lock).
 *   3. Regenerate by save() -> must release the lock (ownership-safe) immediately.
 *   4. Reproduce the precondition again (immediate re-invalidation).
 *   5. load() -> expect MISS again, i.e. a NEW regenerator is elected right away.
 *
 * Before the fix, step 5 returned stale data because the lock lingered until the 10s TTL. The
 * whole sequence runs in milliseconds, so a MISS at step 5 proves the lock was released rather
 * than expiring.
 *
 * Run: php dev/tests/acp2e-5132/test3_lock_release.php [frontend_id]
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$frontendId = $argv[1] ?? 'stale_cache_enabled';
$frontend = acp_frontend($frontendId);
$backend = acp_backend($frontendId);
$id = 'ACP_T3_' . getmypid() . '_' . (int)(microtime(true) * 1000);

/**
 * Put the id into the "L1 warm, remote hash missing" state that triggers lock election.
 */
$prime = static function () use ($frontend, $backend, $id): bool {
    $frontend->save('payload-' . $id, $id, ['ACP_T3'], 3600);
    $backend->getRemote()->remove($id . ':hash');
    return $backend->getLocal()->load($id) !== false
        && $backend->getRemote()->load($id . ':hash') === false;
};

$allOk = true;

// Step 1-2: first election
$allOk &= acp_result('precondition #1', $prime());
$first = $frontend->load($id);
$allOk &= acp_result('first reader elected (MISS)', $first === false,
    'load=' . var_export($first, true));

// Step 3: regenerate — should release the lock
$t0 = microtime(true);
$frontend->save('payload-' . $id . '-regen', $id, ['ACP_T3'], 3600);

// Step 4-5: immediate re-invalidation must elect a new regenerator (not serve stale)
$allOk &= acp_result('precondition #2 (re-invalidate)', $prime());
$second = $frontend->load($id);
$elapsedMs = (microtime(true) - $t0) * 1000;

$reElected = ($second === false);
$allOk &= acp_result(
    'new regenerator elected immediately after save',
    $reElected,
    sprintf('load=%s elapsed=%.1fms (LOCK_TTL=10000ms)', var_export($second, true), $elapsedMs)
);
$allOk &= acp_result('re-election well under TTL', $reElected && $elapsedMs < 9000,
    sprintf('%.1fms', $elapsedMs));

$frontend->remove($id);

echo $allOk ? "\nTEST 3 PASS\n" : "\nTEST 3 FAIL\n";
exit($allOk ? 0 : 1);
