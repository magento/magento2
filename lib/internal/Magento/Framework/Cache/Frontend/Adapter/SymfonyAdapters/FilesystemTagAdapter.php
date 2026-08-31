<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Filesystem-specific tag adapter
 */
class FilesystemTagAdapter implements TagAdapterInterface
{
    /**
     * Bound on reopen retries in mutateFileLocked() when the file is unlink()ed from under an
     * acquired lock by a concurrent worker. High enough to absorb realistic contention, finite so a
     * pathological churn can never spin forever.
     */
    private const MUTATE_MAX_ATTEMPTS = 50;

    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cachePool;

    /**
     * @var string
     */
    private string $tagDirectory;

    /**
     * Directory holding the id -> tags reverse index (one file per tagged id). Lets onRemove and
     * deleteByIds touch only the tag files a given id actually belongs to, instead of scanning the
     * whole tag directory. Mirrors the reverse index the Redis tier already keeps.
     *
     * @var string
     */
    private string $reverseDirectory;

    /**
     * Whether to maintain the on-disk tag index. Disabled for an L1 tier that sits behind a
     * Redis L2 (tags + :hash live in the remote, local self-heals on read); kept true for a
     * file-only cache where the file tag index is the sole invalidation source.
     *
     * @var bool
     */
    private bool $indexTags;

    /**
     * @param CacheItemPoolInterface $cachePool
     * @param string $tagDirectory Directory to store tag index files
     * @param bool $indexTags Whether to write/maintain the on-disk tag index
     */
    public function __construct(CacheItemPoolInterface $cachePool, string $tagDirectory, bool $indexTags = true)
    {
        $this->cachePool = $cachePool;
        $base = rtrim($tagDirectory, '/');
        $this->tagDirectory = $base . '/tags/';
        $this->reverseDirectory = $base . '/idtags/';
        $this->indexTags = $indexTags;
        // Directories are created lazily on the first real write, so a tier that never indexes tags
        // leaves no empty var/cache/symfony/{tags,idtags} directories behind.
    }

    /**
     * Reverse-index file path for a cache id
     *
     * @param string $id
     * @return string
     */
    private function getReverseFile(string $id): string
    {
        return $this->reverseDirectory . $id;
    }

    /**
     * Read the tags associated with a cache id from the reverse index
     *
     * @param string $id
     * @return array
     */
    private function getIdTags(string $id): array
    {
        $file = $this->getReverseFile($id);
        if (!file_exists($file)) {
            return [];
        }
        $content = @file_get_contents($file);
        return $content === false ? [] : $this->parseIds($content);
    }

    /**
     * Store the tags for a cache id in the reverse index (replace), or delete the file when empty
     *
     * @param string $id
     * @param array $tags
     * @return void
     */
    private function setIdTags(string $id, array $tags): void
    {
        $this->mutateFileLocked(
            $this->getReverseFile($id),
            $this->reverseDirectory,
            static fn() => array_values(array_unique($tags))
        );
    }

    /**
     * Delete the reverse-index entry for a cache id
     *
     * @param string $id
     * @return void
     */
    private function deleteIdTags(string $id): void
    {
        $this->mutateFileLocked(
            $this->getReverseFile($id),
            $this->reverseDirectory,
            static fn() => []
        );
    }

    /**
     * Get tag file path
     *
     * @param string $tag
     * @return string
     */
    private function getTagFile(string $tag): string
    {
        return $this->tagDirectory . $tag;
    }

    /**
     * Read IDs from a tag file
     *
     * @param string $tag
     * @return array
     */
    private function getTagIds(string $tag): array
    {
        $file = $this->getTagFile($tag);

        if (!file_exists($file)) {
            return [];
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return [];
        }

        return $this->parseIds($content);
    }

    /**
     * Parse the on-disk tag-file body (one id per line) into an array of ids
     *
     * @param string $content
     * @return array
     */
    private function parseIds(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $ids = trim(substr($content, 0, strrpos($content, "\n") ?: strlen($content)));
        return $ids !== '' ? explode("\n", $ids) : [];
    }

    /**
     * Read-modify-write a tag file while holding an exclusive lock for the whole cycle.
     *
     * Holding flock(LOCK_EX) across the whole cycle means concurrent PHP-FPM workers on the same
     * host can no longer lose an update (the previous getTagIds/setTagIds pair only locked the
     * write, leaving the read-modify-write racy). $transform receives the current ids and returns
     * the new ids, or null to signal "no change" (skips the rewrite).
     *
     * @param string $tag
     * @param callable $transform fn(array $ids): ?array
     * @return void
     */
    private function mutateTagFileLocked(string $tag, callable $transform): void
    {
        $this->mutateFileLocked($this->getTagFile($tag), $this->tagDirectory, $transform);
    }

    /**
     * Read-modify-write a line-per-entry index file while holding an exclusive lock for the whole cycle.
     *
     * Used for both tag files (tag -> ids) and reverse-index files (id -> tags). Holding
     * flock(LOCK_EX) across the whole cycle means concurrent workers on the same host cannot lose
     * an update. $transform receives the current entries and returns the new entries, or null to
     * signal "no change" (skips the rewrite). An empty result deletes the file.
     *
     * @param string $file
     * @param string $dir Directory that must exist before writing
     * @param callable $transform fn(array $entries): ?array
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function mutateFileLocked(string $file, string $dir, callable $transform): void
    {
        if (!is_dir($dir)) {
            @mkdir($dir, 0770, true);
        }

        // Retry loop guarding the open()->flock() window. fopen() resolves $file to an inode before
        // we hold the lock; a concurrent worker that empties and unlink()s the file in that window
        // leaves our handle bound to an orphaned inode with no directory entry, so any write we make
        // is silently lost. After locking we confirm the handle still refers to the file on disk and,
        // if not, reopen and retry. The unlink of an emptied file therefore also stays inside the
        // lock, so it can never delete another worker's freshly written membership.
        for ($attempt = 0; $attempt < self::MUTATE_MAX_ATTEMPTS; $attempt++) {
            $fp = @fopen($file, 'c+');
            if ($fp === false) {
                return;
            }

            if (!flock($fp, LOCK_EX)) {
                fclose($fp);
                return;
            }

            // Our handle must still be the file currently linked at $file. If it was unlink()ed (and
            // possibly recreated) between fopen() and the lock, dev/ino diverge (or the path is gone);
            // drop the stale handle and retry with a fresh open under a fresh lock.
            clearstatcache(true, $file);
            $held = @fstat($fp);
            $onDisk = @stat($file);
            if ($held === false || $onDisk === false
                || $held['ino'] !== $onDisk['ino'] || $held['dev'] !== $onDisk['dev']) {
                flock($fp, LOCK_UN);
                fclose($fp);
                continue;
            }

            try {
                $content = stream_get_contents($fp);
                $current = $this->parseIds($content !== false ? $content : '');
                $new = $transform($current);

                if ($new === null) {
                    return; // no change; finally still unlocks/closes
                }

                if (empty($new)) {
                    ftruncate($fp, 0);
                    // Delete the now-empty file while still holding the lock and while our handle is
                    // confirmed to be the linked file, so we cannot drop a membership another worker
                    // writes after us.
                    @unlink($file);
                } else {
                    ftruncate($fp, 0);
                    rewind($fp);
                    fwrite($fp, implode("\n", $new) . "\n");
                    fflush($fp);
                }
            } finally {
                flock($fp, LOCK_UN);
                fclose($fp);
            }
            return;
        }
    }

    /**
     * Add ID to a tag file
     *
     * @param string $tag
     * @param string $id
     * @return void
     */
    private function addIdToTag(string $tag, string $id): void
    {
        $this->mutateTagFileLocked($tag, static function (array $ids) use ($id) {
            if (in_array($id, $ids, true)) {
                return null;
            }
            $ids[] = $id;
            return $ids;
        });
    }

    /**
     * Remove ID from a tag file
     *
     * @param string $tag
     * @param string $id
     * @return void
     */
    private function removeIdFromTag(string $tag, string $id): void
    {
        $this->mutateTagFileLocked($tag, static function (array $ids) use ($id) {
            $key = array_search($id, $ids, true);
            if ($key === false) {
                return null;
            }
            unset($ids[$key]);
            return array_values($ids);
        });
    }

    /**
     * @inheritDoc
     *
     * Uses array_intersect for true AND logic (similar to Colin Mollenhour's File backend)
     */
    public function getIdsMatchingTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Get IDs for first tag
        $tag = array_shift($tags);
        $ids = $this->getTagIds($tag);

        // Intersect with remaining tags (AND logic)
        foreach ($tags as $tag) {
            if (empty($ids)) {
                break; // Early termination optimization
            }
            $ids = array_intersect($ids, $this->getTagIds($tag));
        }

        return array_values(array_unique($ids));
    }

    /**
     * @inheritDoc
     *
     * Uses array_merge for OR logic
     */
    public function getIdsMatchingAnyTags(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        $ids = [];
        foreach ($tags as $tag) {
            // phpcs:ignore Magento2.Performance.ForeachArrayMerge
            $ids = array_merge($ids, $this->getTagIds($tag));
        }

        return array_values(array_unique($ids));
    }

    /**
     * @inheritDoc
     *
     * Gets all IDs and removes those matching any of the given tags
     */
    public function getIdsNotMatchingTags(array $tags): array
    {
        if (empty($tags)) {
            // Return all IDs
            return $this->getAllIds();
        }

        // Get all IDs
        $allIds = $this->getAllIds();

        // Get IDs matching any tag
        $matchingIds = $this->getIdsMatchingAnyTags($tags);

        // Return difference
        return array_values(array_diff($allIds, $matchingIds));
    }

    /**
     * Get all cache IDs from all tag files
     *
     * @return array
     */
    private function getAllIds(): array
    {
        $allIds = [];
        $tagFiles = glob($this->tagDirectory . '*');

        if ($tagFiles === false) {
            return [];
        }

        foreach ($tagFiles as $file) {
            if (is_file($file)) {
                $tag = basename($file);
                $ids = $this->getTagIds($tag);
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $allIds = array_merge($allIds, $ids);
            }
        }

        return array_values(array_unique($allIds));
    }

    /**
     * @inheritDoc
     */
    public function deleteByIds(array $ids): bool
    {
        if (empty($ids)) {
            return true;
        }

        $success = $this->cachePool->deleteItems($ids);

        // Ensure changes are committed immediately (matches Zend behavior)
        if (method_exists($this->cachePool, 'commit')) {
            $this->cachePool->commit();
        }

        // Prune the deleted ids from the on-disk tag index so it does not outlive its data. Uses
        // the id -> tags reverse index to touch only the affected tag files (grouped so each tag
        // file is rewritten once), instead of scanning the whole tag directory.
        if ($this->indexTags) {
            $this->pruneIdsFromIndex($ids);
        }

        return $success;
    }

    /**
     * Remove the given ids from their tag files using the reverse index; delete files left empty.
     *
     * @param array $ids
     * @return void
     */
    private function pruneIdsFromIndex(array $ids): void
    {
        // Build tag -> [ids] from the reverse index so each tag file is rewritten at most once.
        $tagToIds = [];
        $reverseToDelete = [];
        foreach ($ids as $id) {
            $tags = $this->getIdTags($id);
            if (empty($tags)) {
                continue;
            }
            foreach ($tags as $tag) {
                $tagToIds[$tag][$id] = true;
            }
            $reverseToDelete[] = $id;
        }

        foreach ($tagToIds as $tag => $idSet) {
            $this->mutateTagFileLocked($tag, static function (array $current) use ($idSet) {
                $remaining = array_values(array_filter($current, static fn($id) => !isset($idSet[$id])));
                return count($remaining) === count($current) ? null : $remaining;
            });
        }

        foreach ($reverseToDelete as $id) {
            $this->deleteIdTags($id);
        }
    }

    /**
     * @inheritDoc
     *
     * Maintains tag-to-ID indices in filesystem
     */
    public function onSave(string $id, array $tags): void
    {
        if (!$this->indexTags || empty($tags)) {
            return;
        }

        // Retag cleanup: drop the id from the forward file of any tag it no longer carries, so a
        // re-save with a different tag set (e.g. [A,B] then [C]) does not leave the old A/B
        // memberships dangling once the reverse index is replaced below. Mirrors the legacy
        // Cm_Cache_Backend_Redis save() which array_diffs the previous tags and removes them.
        foreach (array_diff($this->getIdTags($id), $tags) as $removedTag) {
            $this->removeIdFromTag($removedTag, $id);
        }

        // Forward index: add the id to each tag file.
        foreach ($tags as $tag) {
            $this->addIdToTag($tag, $id);
        }

        // Reverse index: record this id's tags so onRemove/deleteByIds need not scan every tag file.
        $this->setIdTags($id, $tags);
    }

    /**
     * @inheritDoc
     *
     * Removes ID from only the tag files it belongs to, via the reverse index.
     */
    public function onRemove(string $id): void
    {
        if (!$this->indexTags) {
            return;
        }

        // No reverse entry => the id was saved without tags (e.g. an L2 invalid marker) or is not
        // indexed. Either way there is nothing to prune, so this is O(1) rather than a full scan.
        $tags = $this->getIdTags($id);
        if (empty($tags)) {
            return;
        }

        foreach ($tags as $tag) {
            $this->removeIdFromTag($tag, $id);
        }

        $this->deleteIdTags($id);
    }

    /**
     * @inheritDoc
     */
    public function clearAllIndices(): void
    {
        // Remove all forward tag files and all reverse-index files.
        foreach ([$this->tagDirectory, $this->reverseDirectory] as $dir) {
            $files = glob($dir . '*');
            if ($files === false) {
                continue;
            }
            foreach ($files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
    }

    /**
     * @inheritDoc
     *
     * No-op: the file backend has no Redis-style tag SETs that accumulate orphaned members, and the
     * memory-exhaustion concern this addresses is specific to the Redis/Valkey tag index.
     */
    public function garbageCollect(int $batchSize = 1000): int
    {
        return 0;
    }

    /**
     * @inheritDoc
     *
     * No-op: the file backend has no server-memory concept analogous to Redis maxmemory. Local disk
     * usage is tracked separately (SymfonyL2Cache's own L1 safety valve), not through this adapter.
     */
    public function getFillingPercentage(): int
    {
        return 0;
    }
}
