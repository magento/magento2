<?php
/**
 * Finding 3 (filesystem tag RMW race). Worker/verify process.
 *
 * N synchronized workers each add M distinct ids to the SAME L1 tag file (onSave -> addIdToTag,
 * a read-modify-write). With the flock fix the file mutation is serialized, so all N*M ids
 * survive. Without it, concurrent RMW cycles clobber each other and ids go missing.
 *
 * Modes:
 *   --mode=worker --frontend=<id> --tag=<tag> --worker=<n> --count=<M> --start=<epoch>
 *   --mode=verify --frontend=<id> --tag=<tag> --expected=<N*M>
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$opt = getopt('', ['mode:', 'frontend:', 'tag:', 'worker::', 'count::', 'start::', 'expected::']);
$mode = $opt['mode'] ?? 'worker';
$frontendId = $opt['frontend'] ?? 'stale_cache_enabled';
$tag = $opt['tag'] ?? 'ACP_T4_RACE';

$backend = acp_backend($frontendId);
$local = acp_local_tag_adapter($backend);

if ($mode === 'verify') {
    $expected = (int)($opt['expected'] ?? 0);
    $ids = $local->getIdsMatchingTags([$tag]);
    $actual = count(array_unique($ids));
    $ok = $actual === $expected;
    acp_result("fs-tag-race:$tag", $ok, "expected=$expected present=$actual");
    exit($ok ? 0 : 1);
}

// worker mode
$w = (int)($opt['worker'] ?? 0);
$count = (int)($opt['count'] ?? 50);
$start = (float)($opt['start'] ?? microtime(true));

acp_wait_barrier($start);
for ($i = 0; $i < $count; $i++) {
    $local->onSave(sprintf('%s_%d_%d', $tag, $w, $i), [$tag]);
}
exit(0);
