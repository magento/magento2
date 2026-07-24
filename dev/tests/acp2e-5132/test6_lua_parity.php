<?php
/**
 * Finding 5 (Lua clean index parity). Sequential single-process test.
 *
 * The customer runs USE_LUA=false, so the live tag adapter never exercises the Lua clean path.
 * This test builds a RedisTagAdapter with Lua ENABLED bound to the SAME Valkey pool and namespace,
 * seeds tagged entries through the normal frontend, then cleans by tag via the Lua script.
 *
 * Asserts the fix: after a Lua clean-by-tag, the deleted ids are gone from the forward tag SETs
 * and from cache:all_ids (before the fix the Lua script only DEL'd data keys and left these index
 * entries orphaned, so a later clean-by-tag could not evict them).
 *
 * ids/tags use [A-Z0-9_] so frontend normalization is the identity.
 *
 * Run: php dev/tests/acp2e-5132/test6_lua_parity.php [frontend_id]
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Psr\Cache\CacheItemPoolInterface;

$frontendId = $argv[1] ?? 'default';
$backend = acp_backend($frontendId);
$remoteFrontend = $backend->getRemote();
$liveAdapter = acp_remote_tag_adapter($backend);

if (!$liveAdapter instanceof RedisTagAdapter) {
    echo "SKIP: remote tier is not RedisTagAdapter (got " . get_class($liveAdapter) . ")\n";
    exit(0);
}

// Extract the live namespace and the underlying cache pool via reflection (test-only).
$ns = (function (RedisTagAdapter $a): string {
    $p = (new ReflectionObject($a))->getProperty('namespace');
    $p->setAccessible(true);
    return (string)$p->getValue($a);
})($liveAdapter);

$llf = $remoteFrontend->getLowLevelFrontend();
$poolProp = (new ReflectionObject($llf))->getProperty('cache');
$poolProp->setAccessible(true);
/** @var CacheItemPoolInterface $pool */
$pool = $poolProp->getValue($llf);

try {
    $luaAdapter = new RedisTagAdapter($pool, $ns, true, false); // useLua = true
} catch (\Throwable $e) {
    echo "SKIP: could not build a Lua-enabled adapter: " . $e->getMessage() . "\n";
    exit(0);
}

if (!$luaAdapter->isLuaEnabled()) {
    echo "SKIP: Lua not available on this client (Predis, or scripting disabled). "
        . "To validate the Lua path, run against a phpredis/Valkey backend with scripting enabled.\n";
    exit(0);
}

$tag = 'ACP_T6_TAG';
$ids = ['ACP_T6_A', 'ACP_T6_B', 'ACP_T6_C'];

// Seed through the frontend so data + forward/reverse/all_ids index are populated normally.
foreach ($ids as $id) {
    $remoteFrontend->save('seed-' . $id, $id, [$tag], 3600);
}

$before = $liveAdapter->getIdsMatchingAnyTags([$tag]);
$seeded = count(array_intersect($ids, $before)) === count($ids);
$allOk = acp_result('seed present in tag set', $seeded, 'tagIds=' . implode(',', $before));

// Clean by tag via the Lua path.
$luaAdapter->cleanMatchingAnyTags([$tag]);

// Assertions: forward tag SET and all_ids must no longer reference the deleted ids.
$afterTag = $liveAdapter->getIdsMatchingAnyTags([$tag]);
$afterAll = $liveAdapter->getIdsNotMatchingTags([]); // all_ids members

$leakTag = array_values(array_intersect($ids, $afterTag));
$leakAll = array_values(array_intersect($ids, $afterAll));

$allOk = acp_result('tag set pruned by Lua clean', empty($leakTag),
    'orphaned in tag set: ' . (implode(',', $leakTag) ?: 'none')) && $allOk;
$allOk = acp_result('all_ids pruned by Lua clean', empty($leakAll),
    'orphaned in all_ids: ' . (implode(',', $leakAll) ?: 'none')) && $allOk;

// Informational: data-key deletion depends on the pre-existing Lua DEL key scheme, not this fix.
$dataLeft = array_values(array_filter($ids, static fn($id) => $remoteFrontend->load($id) !== false));
echo 'INFO: data keys still readable after clean: ' . (implode(',', $dataLeft) ?: 'none') . "\n";

echo $allOk ? "\nTEST 6 PASS\n" : "\nTEST 6 FAIL\n";
exit($allOk ? 0 : 1);
