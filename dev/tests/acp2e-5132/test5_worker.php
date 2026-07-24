<?php
/**
 * Finding 4 (Valkey delete/save index race). Worker/verify process.
 *
 * Per trial: seed id X (data + tag index), then race S savers against D deleters on the SAME X at
 * a shared barrier. Whoever runs last, the END STATE must be internally consistent:
 *   - present:  data exists AND X in tag set AND X in all_ids            (a save won), or
 *   - absent:   data gone   AND X not in tag set AND X not in all_ids    (a delete won).
 * Any mixed state is the orphan bug (e.g. data present but dropped from the tag set), which a
 * later clean-by-tag can no longer evict. The atomic EVAL prune removes the read/write gap that
 * produced those mixed states.
 *
 * ids/tags use [A-Z0-9_] only, so the frontend's id/tag normalization is the identity and direct
 * adapter calls address exactly the keys the frontend wrote.
 *
 * Modes: --mode=setup|save|delete|verify --frontend=<id> --key=<X> --tag=<TAG> [--start=<epoch>]
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

require __DIR__ . '/_bootstrap.php';

$opt = getopt('', ['mode:', 'frontend:', 'key:', 'tag:', 'start::']);
$mode = $opt['mode'] ?? 'verify';
$frontendId = $opt['frontend'] ?? 'default';
$key = $opt['key'] ?? 'ACP_T5_ID';
$tag = $opt['tag'] ?? 'ACP_T5_TAG';

$backend = acp_backend($frontendId);
$remoteFrontend = $backend->getRemote();
$remoteAdapter = acp_remote_tag_adapter($backend);
$start = (float)($opt['start'] ?? microtime(true));

switch ($mode) {
    case 'setup':
        $remoteFrontend->save('seed', $key, [$tag], 3600); // data + forward/reverse/all_ids index
        exit(0);

    case 'save':
        acp_wait_barrier($start);
        $remoteFrontend->save('v', $key, [$tag], 3600);
        exit(0);

    case 'delete':
        acp_wait_barrier($start);
        $remoteAdapter->deleteByIds([$key]); // removes data item + prunes tag/reverse/all_ids
        exit(0);

    case 'verify':
    default:
        $data  = $remoteFrontend->load($key) !== false;
        $inTag = in_array($key, $remoteAdapter->getIdsMatchingAnyTags([$tag]), true);
        $inAll = in_array($key, $remoteAdapter->getIdsNotMatchingTags([]), true);

        $present   = $data && $inTag && $inAll;
        $absent    = !$data && !$inTag && !$inAll;
        $consistent = $present || $absent;

        acp_result(
            'valkey-index-race',
            $consistent,
            sprintf('data=%d inTag=%d inAll=%d => %s',
                $data, $inTag, $inAll,
                $present ? 'present' : ($absent ? 'absent' : 'ORPHAN/inconsistent'))
        );
        exit($consistent ? 0 : 1);
}
