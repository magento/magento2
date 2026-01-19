<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters;

use Psr\Cache\CacheItemPoolInterface;

/**
 * Generic tag adapter for backends that don't support native tag-to-ID indices
 *
 * This is a fallback implementation for adapters like Memcached, APCu, Database, etc.
 * that don't have efficient tag-to-ID index capabilities like Redis or Filesystem.
 *
 * Uses namespace tag strategy for MATCHING_TAG mode:
 * - Generates composite tags for tag combinations
 * - Example: tags ['config', 'eav'] → saves with ['config', 'eav', 'NS_config|eav']
 * - MATCHING_TAG(['config', 'eav']) → invalidate 'NS_config|eav'
 *
 * Limitations:
 * - MATCHING_TAG only works if items were saved with those exact tags
 * - NOT_MATCHING_TAG is not efficiently supported (falls back to invalidating nothing)
 */
class GenericTagAdapter implements TagAdapterInterface
{
    private const NAMESPACE_PREFIX = 'NS_';
    private const NAMESPACE_SEPARATOR = '|';
    private const MAX_TAGS_FOR_NAMESPACE = 4; // Prevent combinatorial explosion

    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cachePool;

    /**
     * @var bool
     */
    private bool $isPageCache;

    /**
     * @param CacheItemPoolInterface $cachePool
     * @param bool $isPageCache Whether this is for page cache (FPC)
     */
    public function __construct(CacheItemPoolInterface $cachePool, bool $isPageCache = false)
    {
        $this->cachePool = $cachePool;
        $this->isPageCache = $isPageCache;
    }

    /**
     * Generate namespace tag for a combination of tags
     *
     * @param array $tags
     * @return string
     */
    public function generateNamespaceTag(array $tags): string
    {
        $tags = array_values(array_unique($tags));
        sort($tags); // Consistent ordering
        return self::NAMESPACE_PREFIX . implode(self::NAMESPACE_SEPARATOR, $tags);
    }

    /**
     * Check if we should use namespace tags for this combination
     *
     * @param array $tags
     * @return bool
     */
    private function shouldUseNamespaceTags(array $tags): bool
    {
        $count = count($tags);

        // For page cache, use namespace tags for 2-4 tags
        if ($this->isPageCache) {
            return $count >= 2 && $count <= self::MAX_TAGS_FOR_NAMESPACE;
        }

        // For application cache, don't use namespace tags
        return false;
    }

    /**
     * @inheritDoc
     *
     * Uses namespace tags for FPC, falls back to invalidating individual tags for application cache
     */
    public function getIdsMatchingTags(array $tags): array
    {
        // This method returns IDs, but we don't maintain explicit indices
        // Instead, we use it to determine what to invalidate

        // For generic adapters, we can't efficiently get IDs
        // This is handled in Symfony.php by using invalidateTags
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsMatchingAnyTags(array $tags): array
    {
        // For generic adapters, we can't efficiently get IDs
        // This is handled in Symfony.php by using invalidateTags
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getIdsNotMatchingTags(array $tags): array
    {
        // NOT_MATCHING_TAG is not efficiently supported for generic adapters
        return [];
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
     * For generic adapters, we don't maintain separate indices
     * Tags are stored directly with cache items by Symfony
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function onSave(string $id, array $tags): void
    {
        // Intentional no-op: Tags are handled by Symfony's TagAwareAdapter
        // (for Database, APCu, and Memcached backends that lack native tag support)
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock

    /**
     * @inheritDoc
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function onRemove(string $id): void
    {
        // Intentional no-op: No separate indices to update
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock

    /**
     * @inheritDoc
     */
    // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
    public function clearAllIndices(): void
    {
        // Intentional no-op: No separate indices exist
    }
    // phpcs:enable Magento2.CodeAnalysis.EmptyBlock

    /**
     * Get tags to save with cache item (including namespace tags if applicable)
     *
     * @param array $tags Original tags
     * @return array Tags including namespace tags if applicable
     */
    public function getTagsForSave(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Start with original tags
        $allTags = $tags;

        // Add namespace tag if applicable
        if ($this->shouldUseNamespaceTags($tags)) {
            $allTags[] = $this->generateNamespaceTag($tags);
        }

        return array_values(array_unique($allTags));
    }

    /**
     * Get tags to invalidate for MATCHING_TAG mode
     *
     * @param array $tags
     * @return array
     */
    public function getTagsForMatchingTag(array $tags): array
    {
        if (empty($tags)) {
            return [];
        }

        // Deduplicate and sort
        $uniqueTags = array_values(array_unique($tags));

        // If we use namespace tags, invalidate the namespace tag
        if ($this->shouldUseNamespaceTags($uniqueTags)) {
            sort($uniqueTags); // Must match save() logic
            return [$this->generateNamespaceTag($uniqueTags)];
        }

        // Otherwise, invalidate individual tags (OR logic, not perfect but best we can do)
        return $uniqueTags;
    }

    /**
     * Check if this adapter should use namespace tags for MATCHING_TAG
     *
     * @return bool
     */
    public function usesNamespaceTags(): bool
    {
        return $this->isPageCache;
    }
}
