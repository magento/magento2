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
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cachePool;

    /**
     * @var string
     */
    private string $tagDirectory;

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
        $this->tagDirectory = rtrim($tagDirectory, '/') . '/tags/';
        $this->indexTags = $indexTags;
        // Directory is created lazily on the first real tag write (see mutateTagFileLocked), so a
        // tier that never indexes tags leaves no empty var/cache/symfony/tags directory behind.
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
     * flock(LOCK_EX) spans the read and the write, so concurrent PHP-FPM workers on the same host
     * can no longer lose an update (the previous getTagIds/setTagIds pair only locked the write,
     * leaving the read-modify-write racy). $transform receives the current ids and returns the new
     * ids, or null to signal "no change" (skips the rewrite).
     *
     * @param string $tag
     * @param callable $transform fn(array $ids): ?array
     * @return void
     */
    private function mutateTagFileLocked(string $tag, callable $transform): void
    {
        $file = $this->getTagFile($tag);

        if (!is_dir($this->tagDirectory)) {
            @mkdir($this->tagDirectory, 0770, true);
        }

        $fp = @fopen($file, 'c+');
        if ($fp === false) {
            return;
        }

        if (!flock($fp, LOCK_EX)) {
            fclose($fp);
            return;
        }

        $emptied = false;
        try {
            $content = stream_get_contents($fp);
            $current = $this->parseIds($content !== false ? $content : '');
            $new = $transform($current);

            if ($new === null) {
                return; // no change; finally still unlocks/closes
            }

            if (empty($new)) {
                ftruncate($fp, 0);
                $emptied = true;
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

        if ($emptied) {
            @unlink($file);
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

        // Prune the deleted ids from the on-disk tag index so it does not outlive its data.
        // clean(ALL) is rewritten to a tag-clean by TagScope before reaching this adapter, so a
        // flush arrives here as deleteByIds(all-ids-of-scope); pruning them empties every tag
        // file (removed when empty), giving the same clean slate as clearAllIndices().
        if ($this->indexTags) {
            $this->pruneIdsFromIndex($ids);
        }

        return $success;
    }

    /**
     * Remove the given ids from every tag file in a single pass; delete files left empty.
     *
     * @param array $ids
     * @return void
     */
    private function pruneIdsFromIndex(array $ids): void
    {
        $tagFiles = glob($this->tagDirectory . '*');
        if ($tagFiles === false) {
            return;
        }

        $idSet = array_flip($ids);
        foreach ($tagFiles as $file) {
            if (!is_file($file)) {
                continue;
            }
            $tag = basename($file);
            $this->mutateTagFileLocked($tag, static function (array $current) use ($idSet) {
                $remaining = array_values(array_filter($current, static fn($id) => !isset($idSet[$id])));
                return count($remaining) === count($current) ? null : $remaining;
            });
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

        // Add ID to each tag file
        foreach ($tags as $tag) {
            $this->addIdToTag($tag, $id);
        }
    }

    /**
     * @inheritDoc
     *
     * Removes ID from all tag files
     */
    public function onRemove(string $id): void
    {
        // We need to scan all tag files and remove this ID
        $tagFiles = glob($this->tagDirectory . '*');

        if ($tagFiles === false) {
            return;
        }

        foreach ($tagFiles as $file) {
            if (is_file($file)) {
                $tag = basename($file);
                $this->removeIdFromTag($tag, $id);
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function clearAllIndices(): void
    {
        // Remove all tag files
        $tagFiles = glob($this->tagDirectory . '*');

        if ($tagFiles === false) {
            return;
        }

        foreach ($tagFiles as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}
