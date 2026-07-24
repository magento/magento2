<?php
/**
 * Finding 1 (atomic lock). Worker/setup process for the single-regenerator concurrency test.
 *
 * The election path in SymfonyL2Cache triggers when L1 has data but the remote :hash is gone
 * (L2 eviction while L1 stays warm). Setup reproduces exactly that state; each worker then races
 * to load the same id. Exactly ONE worker should be elected regenerator (load() === false); the
 * rest must be served stale. Orchestrated by run_test2.sh.
 *
 * Modes:
 *   --mode=setup  --frontend=<id> --id=<cacheId>
 *   --mode=worker --frontend=<id> --id=<cacheId> --start=<microtime float> --out=<resultsFile>
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$opt = getopt('', ['mode:', 'frontend:', 'id:', 'start::', 'out::']);
$mode = $opt['mode'] ?? 'worker';
$frontendId = $opt['frontend'] ?? 'stale_cache_enabled';
$id = $opt['id'] ?? 'ACP_T2_ID';

$frontend = acp_frontend($frontendId);
$backend = acp_backend($frontendId);

if ($mode === 'setup') {
    // Fresh value: warms L1 + L2 + :hash and marks the id valid.
    $frontend->remove($id);
    $frontend->save('payload-' . $id, $id, ['ACP_T2'], 3600);

    // Simulate L2 eviction of this entry while the stale L1 copy remains: drop only the remote
    // hash. This is the precise precondition for the regeneration-lock election branch.
    $backend->getRemote()->remove($id . ':hash');

    $l1 = $backend->getLocal()->load($id);
    $hash = $backend->getRemote()->load($id . ':hash');
    $ready = ($l1 !== false) && ($hash === false);
    fwrite(STDERR, sprintf("setup id=%s L1=%s remoteHash=%s => %s\n",
        $id, $l1 === false ? 'MISS' : 'HIT', $hash === false ? 'MISS' : 'HIT', $ready ? 'READY' : 'NOT-READY'));
    exit($ready ? 0 : 1);
}

// worker mode
$start = (float)($opt['start'] ?? microtime(true));
$out = $opt['out'] ?? (sys_get_temp_dir() . '/acp_t2_results');

acp_wait_barrier($start);
$result = $frontend->load($id);
acp_record($out, $result === false ? 'REGEN' : 'STALE');
exit(0);
