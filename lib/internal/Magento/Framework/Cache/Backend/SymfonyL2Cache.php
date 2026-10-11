<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\Exception\CacheException;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\RedisTagAdapter;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\ClearableInterface;
use Magento\Framework\Cache\MultiLoadInterface;
use Symfony\Component\Cache\PruneableInterface;

/**
 * Two-level Symfony cache backend with fast per-worker L1 and shared persistent L2 storage.
 * Uses the remote :hash marker to synchronize PSR-6-compatible local and remote values.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class SymfonyL2Cache extends AbstractBackend implements
    ExtendedBackendInterface,
    MultiLoadInterface,
    TwoTierBackendInterface
{
    use LockSignTrait;

    /**
     * Local backend cache (L1)
     *
     * @var FrontendInterface
     */
    private FrontendInterface $local;

    /**
     * Remote backend cache (L2)
     *
     * @var FrontendInterface
     */
    private FrontendInterface $remote;

    /**
     * Suffix for hash to compare data version in cache storage
     */
    private const HASH_SUFFIX = ':hash';

    /**
     * Default cleanup percentage for L1 cache
     */
    private const DEFAULT_CLEANUP_PERCENTAGE = 90;

    /**
     * Cleanup percentage threshold (when to trigger L1 cleanup)
     *
     * @var int
     */
    private int $cleanupPercentage;

    /**
     * Whether to use stale cache when remote (L2) is unavailable
     *
     * @var bool
     */
    private bool $useStaleCache;

    /**
     * Key prefix for tracking invalid entries in local cache
     */
    private const INVALID_KEY_PREFIX = '__invalid::';

    /**
     * TTL for invalid markers for 24 hours
     */
    private const INVALID_MARK_TTL = 86400;

    /**
     * Regeneration lock key prefix (stored in remote L2)
     */
    private const LOCK_PREFIX = '___stale_regen_lock_';

    /**
     * Regeneration lock lifetime in seconds (auto-expires if a regenerator dies)
     */
    private const LOCK_TTL = 10;

    /**
     * Per-process signature to confirm regeneration-lock ownership
     *
     * @var string
     */
    private string $lockSign;

    /**
     * Resolved Redis tag adapter from the remote frontend, used for atomic lock ops.
     * Null once resolution has run and the remote is not Redis-backed.
     *
     * @var RedisTagAdapter|null
     */
    private ?RedisTagAdapter $lockAdapter = null;

    /**
     * Whether lock-adapter resolution has been attempted (so a null result is not re-resolved)
     *
     * @var bool
     */
    private bool $lockAdapterResolved = false;

    /**
     * Cache ids for which this process currently holds the regeneration lock, so save() only
     * issues a release round-trip for locks it actually owns.
     *
     * @var array<string, true>
     */
    private array $heldLocks = [];

    /**
     * Absolute path of the L1 (file) cache directory, used to gauge disk fill for size-based
     * eviction. Null when the L1 is not file-backed (then eviction is disabled).
     *
     * @var string|null
     */
    private ?string $localCacheDir;

    /**
     * Constructor
     *
     * @param FrontendInterface $remote Remote cache (L2 - persistent, shared)
     * @param FrontendInterface $local Local cache (L1 - fast, per-worker)
     * @param array $options Additional options
     * @throws CacheException
     */
    public function __construct(
        FrontendInterface $remote,
        FrontendInterface $local,
        array $options = []
    ) {
        parent::__construct($options);

        $this->remote = $remote;
        $this->local = $local;
        $this->cleanupPercentage = (int)($options['cleanup_percentage'] ?? self::DEFAULT_CLEANUP_PERCENTAGE);
        $this->useStaleCache = (bool)($options['use_stale_cache'] ?? false);
        $this->localCacheDir = isset($options['local_cache_dir']) ? (string)$options['local_cache_dir'] : null;
        $this->lockSign = $this->generateLockSign();

        // Validate cleanup percentage
        if ($this->cleanupPercentage < 1 || $this->cleanupPercentage > 100) {
            throw new CacheException(__('cleanup_percentage must be between 1 and 100'));
        }
    }

    /**
     * @inheritDoc
     */
    public function load($id, $doNotTestCacheValidity = false)
    {
        // Try local cache first (fast path)
        $localData = $this->local->load($id);

        if ($this->isInvalid($id)) {
            return $this->handleInvalidKey($id);
        }

        if ($localData !== false) {
            $result = $this->validateLocalCache($id, $localData);
            if ($result !== null) {
                return $result;
            }
            // Local cache is stale, fall through to load from remote
        }

        return $this->loadFromRemoteOrFallback($id, $localData);
    }

    /**
     * Batch-load hot keys from the remote L2 in one round-trip for preloading.
     *
     * L1/hash validation is deferred to normal load(), since L2 is authoritative for warm-up data.
     *
     * @param string[] $ids
     * @return array<string, mixed>
     */
    public function loadMultiple(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }
        if ($this->remote instanceof MultiLoadInterface) {
            return $this->remote->loadMultiple($ids);
        }

        // loadMultiple() is a batch-preload optimization. When the remote cannot batch, falling back
        // to per-key loads defeats the single-round-trip purpose and only adds latency for no gain —
        // so skip it. The keys are simply not preloaded and load lazily on first real access.
        return [];
    }

    /**
     * @inheritDoc
     */
    public function test($id)
    {
        if ($this->useStaleCache) {
            // With stale cache, check local first for availability
            return $this->local->test($id) ?: $this->remote->test($id);
        }

        // Check remote cache (source of truth)
        return $this->remote->test($id);
    }

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function save($data, $id, $tags = [], $specificLifetime = null)
    {
        $hashSaved = false;

        try {
            $sameRemoteData = $this->isRemoteUpToDate($data, $id);
            // A tagged save must reach the remote adapter even when the payload is unchanged;
            // otherwise changed tag associations would be skipped with the data deduplication.
            if (empty($tags) && $sameRemoteData) {
                // Skip redundant data and hash writes when L2 already contains this exact value,
                // avoiding duplicate Redis traffic for repeated saves.
                $remoteSaved = true;
                $hashSaved = true;
            } else {
                if (!empty($tags) && $sameRemoteData) {
                    // Remove first so all previous remote tag memberships are cleared before re-save.
                    $this->remote->remove($id);
                }
                // Save data first to avoid hash pointing to non-existent data
                $remoteSaved = $this->remote->save($data, $id, $tags, $specificLifetime);

                if ($remoteSaved !== false) {
                    // Calculate and save hash to remote for synchronization
                    $hash = $this->getDataHash($data);
                    $hashSaved = $this->remote->save($hash, $id . self::HASH_SUFFIX, $tags, $specificLifetime);
                }
            }
        } catch (\Exception $e) {
            $remoteSaved = false;
            $hashSaved = false;
        }

        // If L1 disk usage is high, clear it before saving so the current value survives the flush.
        // clearLocal() is required because tag-scoped clean() cannot clear a tag-less L1.
        if ($this->shouldCheckLocalSpace() && $this->isLocalCacheSpaceExceeded()) {
            $this->clearLocal();
        }

        // L1 mirrors data only; tags go to L2 (keeps L1 free of any tag index), like legacy RSC.
        $this->local->save($data, $id, [], $specificLifetime);

        if ($remoteSaved !== false && $hashSaved !== false) {
            $this->markValid($id);
        } else {
            if ($this->useStaleCache) {
                $this->markInvalid($id);
            }
        }

        // Release the regeneration lock after storing the fresh L2 value, allowing immediate
        // re-invalidation to elect a new regenerator without waiting for the lock TTL.
        $this->releaseRegenLock($id);

        return $remoteSaved;
    }

    /**
     * @inheritDoc
     */
    public function remove($id)
    {
        try {
            // Remove hash from remote
            $hashRemoved = $this->remote->remove($id . self::HASH_SUFFIX);

            // Remove from remote
            $result = $this->remote->remove($id);
        } catch (\Exception $e) {
            $hashRemoved = false;
            $result = false;
        }

        // Only remove from local if NOT using stale cache (keep stale data for availability)
        if (!$this->useStaleCache) {
            $this->local->remove($id);
        }

        if ($result !== false && $hashRemoved !== false) {
            $this->markValid($id);
        } else {
            if ($this->useStaleCache) {
                $this->markInvalid($id);
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, $tags = [])
    {
        if ($mode === CacheConstants::CLEANING_MODE_ALL) {
            // Clear L1 directly because tag-scoped clean() cannot reach a tag-less pool;
            // otherwise cache:flush could leave stale local data behind.
            $this->clearLocal();
            return $this->remote->clean($mode, $tags);
        }
        if ($mode === CacheConstants::CLEANING_MODE_OLD) {
            // Prune expired entries from both tiers: L1 files require direct cleanup, while L2 clean(OLD)
            // sweeps its tag index and native TTL handles remote data keys.
            $this->pruneLocal();
            return $this->remote->clean($mode, $tags);
        }
        $this->local->clean($mode, $tags);
        return $this->remote->clean($mode, $tags);
    }

    /**
     * Prune expired entries from the local (L1) tier when its backend supports pruning.
     *
     * @return void
     */
    private function pruneLocal(): void
    {
        $localBackend = $this->local->getBackend();
        // Only backends that advertise the pruning contract are pruned. When the L1 backend is not
        // pruneable, doing nothing is intentional and correct: such backends reclaim expired entries
        // through their own native TTL / lazy expiry, so an explicit prune would have no effect.
        if ($localBackend instanceof PruneableInterface) {
            $localBackend->prune();
        }
    }

    /**
     * Fully wipe both cache tiers (L1 + L2), for direct callers of getBackend()->clear().
     *
     * Admin "Flush Cache Storage" (FlushAll) and `bin/magento cache:flush` do not call this
     * method; they call clean(CLEANING_MODE_ALL), which reaches the same result via a separate
     * code path (the CLEANING_MODE_ALL branch of clean() below).
     *
     * @return bool
     */
    public function clear(): bool
    {
        $this->clearLocal();
        return (bool)$this->remote->clean(CacheConstants::CLEANING_MODE_ALL);
    }

    /**
     * Empty the local (L1) tier completely, bypassing the tag-scoped clean() that cannot see a tag-less pool
     *
     * @return void
     */
    private function clearLocal(): void
    {
        $localBackend = $this->local->getBackend();
        // Symfony/PSR-6 L1 backends can wipe the whole pool directly and cheaply via clear();
        // legacy backends implement only the Zend clean() contract, so fall back to clean(ALL).
        if ($localBackend instanceof ClearableInterface) {
            $localBackend->clear();
        } else {
            $this->local->clean(CacheConstants::CLEANING_MODE_ALL);
        }
    }

    /**
     * Calculate hash of data for synchronization
     *
     * @param string $data
     * @return string
     */
    private function getDataHash(string $data): string
    {
        return hash('sha256', $data);
    }

    /**
     * Whether L2 already holds this exact value, allowing a redundant write to be skipped.
     *
     * Checks the cheap :hash first, then confirms the data still exists and matches it.
     *
     * @param string $data
     * @param string $id
     * @return bool
     */
    private function isRemoteUpToDate(string $data, string $id): bool
    {
        $remoteHash = $this->remote->load($id . self::HASH_SUFFIX);
        if ($remoteHash === false || $remoteHash !== $this->getDataHash($data)) {
            return false;
        }
        $remoteData = $this->remote->load($id);
        return $remoteData !== false && $remoteData === $data;
    }

    /**
     * @inheritDoc
     */
    public function getIds()
    {
        // Return IDs from remote (source of truth)
        // Note: This may not be supported by all Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getTags()
    {
        // Return tags from remote
        // Note: This may not be supported by all Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsMatchingTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsNotMatchingTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsMatchingAnyTags($tags = [])
    {
        // Not supported by Symfony adapters
        return [];
    }

    /**
     * @inheritDoc
     *
     * Return the remote L2 storage usage, kept distinct from the local disk safety check.
     * Return 0 when the remote adapter cannot report usage or is unavailable.
     */
    public function getFillingPercentage()
    {
        try {
            // No tag adapter (e.g. legacy Zend) => no fill metric => 0.
            $tagAdapter = $this->remote->getLowLevelFrontend()->getTagAdapter();

            return $tagAdapter instanceof TagAdapterInterface ? $tagAdapter->getFillingPercentage() : 0;
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Return the L1 file-cache directory usage for disk-full protection;
     *
     * Return 0 when unavailable or when L1 is not file-backed.
     *
     * @return int
     */
    private function getLocalFillingPercentage(): int
    {
        if ($this->localCacheDir === null || !is_dir($this->localCacheDir)) {
            return 0;
        }

        $free = @disk_free_space($this->localCacheDir);
        $total = @disk_total_space($this->localCacheDir);
        if ($free === false || $total === false || $total <= 0 || $free >= $total) {
            return 0;
        }

        return (int)(100.0 * ($total - $free) / $total);
    }

    /**
     * Whether the L1 partition has reached the configured cleanup threshold.
     *
     * @return bool
     */
    private function isLocalCacheSpaceExceeded(): bool
    {
        return $this->getLocalFillingPercentage() >= $this->cleanupPercentage;
    }

    /**
     * Throttle disk-space checks to avoid a filesystem stat on every save;
     *
     * The protected seam enables deterministic eviction-path tests.
     *
     * @return bool
     */
    protected function shouldCheckLocalSpace(): bool
    {
        try {
            // Probabilistically throttle the disk-space check (~1 in 101 saves). random_int avoids the
            // insecure-function static warning; a crypto-grade source is not required here but is fine.
            return random_int(0, 100) === 0;
        } catch (\Throwable $e) {
            // A failing CSPRNG must never break a cache save; just skip the check this time.
            return false;
        }
    }

    /**
     * @inheritDoc
     */
    public function getMetadatas($id)
    {
        return $this->remote->getMetadatas($id);
    }

    /**
     * @inheritDoc
     */
    public function touch($id, $extraLifetime)
    {
        // Extend the existing remaining lifetime, matching the legacy Redis backend.
        $metadata = $this->remote->getMetadatas($id);
        if ($metadata === false || !isset($metadata['expire']) || $metadata['expire'] === false
            || $metadata['expire'] === null) {
            return false;
        }

        $remainingLifetime = max(0, (int)$metadata['expire'] - time());
        $lifetime = $remainingLifetime + (int)$extraLifetime;
        $data = $this->remote->load($id);

        if ($data === false) {
            return false;
        }

        // Write directly to L2 so touch() extends the TTL even when the data is unchanged, then
        // refresh the matching L1 entry; save() would skip the remote write.
        try {
            $remoteSaved = $this->remote->save($data, $id, [], $lifetime);
            if ($remoteSaved === false) {
                return false;
            }
            $hash = $this->getDataHash($data);
            $this->remote->save($hash, $id . self::HASH_SUFFIX, [], $lifetime);
        } catch (\Exception $e) {
            return false;
        }

        $this->local->save($data, $id, [], $lifetime);
        $this->markValid($id);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function getCapabilities()
    {
        return [
            'automatic_cleaning' => false,
            'tags' => true,
            'expired_read' => false,
            'priority' => false,
            'infinite_lifetime' => true,
            'get_list' => false,
        ];
    }

    /**
     * Get remote cache frontend
     *
     * @return FrontendInterface
     */
    public function getRemote(): FrontendInterface
    {
        return $this->remote;
    }

    /**
     * Get local cache frontend
     *
     * @return FrontendInterface
     */
    public function getLocal(): FrontendInterface
    {
        return $this->local;
    }

    /**
     * Check if a cache key was modified while remote was unavailable
     *
     * @param string $id
     * @return bool
     */
    private function isInvalid(string $id): bool
    {
        return $this->local->load(self::INVALID_KEY_PREFIX . $id) !== false;
    }

    /**
     * Mark a cache key as invalid (modified while remote was unavailable)
     *
     * @param string $id
     * @return void
     */
    private function markInvalid(string $id): void
    {
        $this->local->save('1', self::INVALID_KEY_PREFIX . $id, [], self::INVALID_MARK_TTL);
    }

    /**
     * Mark a cache key as synchronized with L2, removing its L1 invalid marker only when present.
     *
     * This avoids an unnecessary L1 remove and tag-adapter callback on every successful save.
     *
     * @param string $id
     * @param bool $knownInvalid
     * @return void
     */
    private function markValid(string $id, bool $knownInvalid = true): void
    {
        if ($knownInvalid && $this->isInvalid($id)) {
            $this->local->remove(self::INVALID_KEY_PREFIX . $id);
        }
    }

    /**
     * Clean an invalid key from remote cache
     *
     * @param string $id
     * @return bool
     */
    private function cleanInvalidFromRemote(string $id): bool
    {
        try {
            $this->remote->remove($id . self::HASH_SUFFIX);
            $this->remote->remove($id);
            return true;
        } catch (\Exception $e) { // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock
            // If remote is still unavailable, the invalid marker will be cleared anyway
            return false;
        }
    }

    /**
     * Handle invalid key by cleaning from remote and local
     *
     * @param string $id
     * @return false
     */
    private function handleInvalidKey(string $id)
    {
        $remoteCleanSuccess = $this->cleanInvalidFromRemote($id);
        $this->local->remove($id);

        if ($remoteCleanSuccess) {
            // Skip markValid()'s isInvalid() guard: this method only ever runs after load() has
            // already confirmed the marker exists, so the re-check is guaranteed true and wasted.
            $this->local->remove(self::INVALID_KEY_PREFIX . $id);
        }
        return false;
    }

    /**
     * Validate local cache data against remote hash
     *
     * @param string $id
     * @param string $localData
     * @return string|false|null Returns data if valid, false if invalid, null if stale (should try remote)
     */
    private function validateLocalCache(string $id, string $localData)
    {
        $remoteHash = $this->remote->load($id . self::HASH_SUFFIX);

        if ($remoteHash === false && $this->useStaleCache) {
            // Elect one regenerator: lock winner returns a miss (rebuilds + repopulates L2),
            // others serve stale L1 without waiting.
            if ($this->tryLock($id)) {
                return false;
            }
            return $localData;
        }

        $localHash = $this->getDataHash($localData);

        if ($remoteHash === $localHash) {
            return $localData;
        }

        return null;
    }

    /**
     * Try to acquire the non-blocking regeneration lock; exactly one reader wins when Redis is used.
     *
     * Uses atomic SET NX EX on Redis and a best-effort fallback for non-Redis remotes.
     *
     * @param string $id
     * @return bool
     */
    private function tryLock(string $id): bool
    {
        $adapter = $this->getLockAdapter();

        if ($adapter !== null) {
            if ($adapter->acquireLock($id, $this->lockSign, self::LOCK_TTL)) {
                $this->heldLocks[$id] = true;
                return true;
            }
            return false;
        }

        return $this->tryLockFallback($id);
    }

    /**
     * Release the regeneration lock for $id if this process acquired it (ownership-safe, no-op otherwise).
     *
     * Cheap: only issues a call when this process is recorded as the lock holder.
     *
     * @param string $id
     * @return void
     */
    private function releaseRegenLock(string $id): void
    {
        if (!isset($this->heldLocks[$id])) {
            return;
        }

        $adapter = $this->getLockAdapter();
        if ($adapter !== null) {
            $adapter->releaseLock($id, $this->lockSign);
        } else {
            // Fallback path stored the lock via remote->save(); remove it so the lock is
            // released immediately instead of lingering until LOCK_TTL expires.
            $this->remote->remove(self::LOCK_PREFIX . $id);
        }
        unset($this->heldLocks[$id]);
    }

    /**
     * Best-effort, non-atomic lock used only when the remote is not Redis-backed.
     *
     * @param string $id
     * @return bool
     */
    private function tryLockFallback(string $id): bool
    {
        $lockKey = self::LOCK_PREFIX . $id;

        if ($this->remote->load($lockKey) !== false) {
            return false;
        }

        $this->remote->save($this->lockSign, $lockKey, [], self::LOCK_TTL);

        if ($this->remote->load($lockKey) === $this->lockSign) {
            $this->heldLocks[$id] = true;
            return true;
        }

        return false;
    }

    /**
     * Lazily resolve the remote frontend's Redis tag adapter for atomic lock operations.
     *
     * Reaches through any frontend decorators via getLowLevelFrontend(); returns null (once)
     * when the remote is not Redis-backed, in which case the best-effort fallback is used.
     *
     * @return RedisTagAdapter|null
     */
    private function getLockAdapter(): ?RedisTagAdapter
    {
        if ($this->lockAdapterResolved) {
            return $this->lockAdapter;
        }

        $this->lockAdapterResolved = true;

        try {
            $lowLevel = $this->remote->getLowLevelFrontend();
            $adapter = $lowLevel->getTagAdapter();
            if ($adapter instanceof RedisTagAdapter) {
                $this->lockAdapter = $adapter;
            }
        } catch (\Throwable $e) {
            $this->lockAdapter = null;
        }

        return $this->lockAdapter;
    }

    /**
     * Load from remote cache or fallback to stale local data
     *
     * @param string $id
     * @param string|false $localData
     * @return string|false
     */
    private function loadFromRemoteOrFallback(string $id, $localData)
    {
        $remoteData = $this->remote->load($id);

        if ($remoteData !== false) {
            $this->local->save($remoteData, $id);
            return $remoteData;
        }

        if ($localData && $this->useStaleCache) {
            return $localData;
        }

        return false;
    }
}
