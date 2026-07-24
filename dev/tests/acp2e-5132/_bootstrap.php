<?php
/**
 * Shared bootstrap + helpers for the ACP2E-5132 validation kit.
 *
 * Boots Magento once and exposes accessors for the L2 cache internals so each test can drive the
 * real cache stack (SymfonyL2Cache + RedisTagAdapter + FilesystemTagAdapter) exactly as production
 * does. Not part of the shipped patch — a manual validation harness only.
 *
 * Copyright 2026 Adobe. All Rights Reserved.
 */
declare(strict_types=1);

use Magento\Framework\App\Bootstrap;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\Cache\Backend\SymfonyL2Cache;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;

// Repo root: dev/tests/acp2e-5132 -> up 3 levels. Override with MAGENTO_ROOT if run elsewhere.
$magentoRoot = getenv('MAGENTO_ROOT') ?: dirname(__DIR__, 3);
require $magentoRoot . '/app/bootstrap.php';

/**
 * @return \Magento\Framework\ObjectManagerInterface
 */
function acp_om()
{
    static $om = null;
    if ($om === null) {
        $bootstrap = Bootstrap::create(BP, $_SERVER);
        $om = $bootstrap->getObjectManager();
    }
    return $om;
}

/**
 * Return a configured cache frontend by its id in env.php (e.g. 'default', 'stale_cache_enabled').
 *
 * @param string $frontendId
 * @return \Magento\Framework\Cache\FrontendInterface
 */
function acp_frontend(string $frontendId)
{
    /** @var Pool $pool */
    $pool = acp_om()->get(Pool::class);
    return $pool->get($frontendId);
}

/**
 * Return the SymfonyL2Cache backend behind a frontend, or throw if the frontend is not L2.
 *
 * @param string $frontendId
 * @return SymfonyL2Cache
 */
function acp_backend(string $frontendId): SymfonyL2Cache
{
    $backend = acp_frontend($frontendId)->getBackend();
    if (!$backend instanceof SymfonyL2Cache) {
        throw new RuntimeException(
            "Frontend '$frontendId' is not backed by SymfonyL2Cache (got " . get_class($backend) . ")."
        );
    }
    return $backend;
}

/**
 * Tag adapter of the remote (L2 / Valkey) tier — a RedisTagAdapter in a redis-backed setup.
 *
 * @param SymfonyL2Cache $backend
 * @return TagAdapterInterface
 */
function acp_remote_tag_adapter(SymfonyL2Cache $backend): TagAdapterInterface
{
    return $backend->getRemote()->getLowLevelFrontend()->getTagAdapter();
}

/**
 * Tag adapter of the local (L1 / filesystem) tier — a FilesystemTagAdapter for a file L1.
 *
 * @param SymfonyL2Cache $backend
 * @return TagAdapterInterface
 */
function acp_local_tag_adapter(SymfonyL2Cache $backend): TagAdapterInterface
{
    return $backend->getLocal()->getLowLevelFrontend()->getTagAdapter();
}

/**
 * Busy-wait until an absolute microtime barrier so multi-process workers act in unison.
 *
 * @param float $startEpoch microtime(true) value to release at
 * @return void
 */
function acp_wait_barrier(float $startEpoch): void
{
    while (microtime(true) < $startEpoch) {
        usleep(200);
    }
}

/**
 * Append one result line to a shared file under an exclusive lock (safe across worker processes).
 *
 * @param string $file
 * @param string $line
 * @return void
 */
function acp_record(string $file, string $line): void
{
    $fp = fopen($file, 'a');
    if ($fp === false) {
        return;
    }
    if (flock($fp, LOCK_EX)) {
        fwrite($fp, rtrim($line, "\n") . "\n");
        fflush($fp);
        flock($fp, LOCK_UN);
    }
    fclose($fp);
}

/**
 * Print a PASS/FAIL banner and return the boolean for exit-code aggregation.
 *
 * @param string $name
 * @param bool $ok
 * @param string $detail
 * @return bool
 */
function acp_result(string $name, bool $ok, string $detail = ''): bool
{
    printf("[%s] %s%s\n", $ok ? 'PASS' : 'FAIL', $name, $detail !== '' ? " — $detail" : '');
    return $ok;
}
