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
     * @param CacheItemPoolInterface $cachePool
     * @param string $tagDirectory Directory to store tag index files
     */
    public function __construct(CacheItemPoolInterface $cachePool, string $tagDirectory)
    {
        $this->cachePool = $cachePool;
        $this->tagDirectory = rtrim($tagDirectory, '/') . '/tags/';

        // Ensure tag directory exists with proper error handling
        if (!is_dir($this->tagDirectory)) {
            if (!@mkdir($this->tagDirectory, 0770, true) && !is_dir($this->tagDirectory)) {
                throw new \RuntimeException(
                    sprintf('Failed to create tag directory: %s', $this->tagDirectory)
                );
            }
        }
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
        if ($content === false || $content === '') {
            return [];
        }

        // IDs are stored one per line
        $ids = trim(substr($content, 0, strrpos($content, "\n") ?: strlen($content)));
        return $ids !== '' ? explode("\n", $ids) : [];
    }

    /**
     * Write IDs to a tag file
     *
     * @param string $tag
     * @param array $ids
     * @return void
     */
    private function setTagIds(string $tag, array $ids): void
    {
        $file = $this->getTagFile($tag);

        if (empty($ids)) {
            // Remove tag file if no IDs
            @unlink($file);
            return;
        }

        // Ensure directory exists before writing (defensive check)
        if (!is_dir($this->tagDirectory)) {
            @mkdir($this->tagDirectory, 0770, true);
        }

        // Write IDs, one per line, with trailing newline
        $content = implode("\n", $ids) . "\n";
        if (@file_put_contents($file, $content, LOCK_EX) === false) {
            throw new \RuntimeException(
                sprintf('Failed to write tag file: %s', $file)
            );
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
        $ids = $this->getTagIds($tag);
        if (!in_array($id, $ids, true)) {
            $ids[] = $id;
            $this->setTagIds($tag, $ids);
        }
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
        $ids = $this->getTagIds($tag);
        $key = array_search($id, $ids, true);

        if ($key !== false) {
            unset($ids[$key]);
            $this->setTagIds($tag, array_values($ids));
        }
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

        return $success;
    }

    /**
     * @inheritDoc
     *
     * Maintains tag-to-ID indices in filesystem
     */
    public function onSave(string $id, array $tags): void
    {
        if (empty($tags)) {
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
