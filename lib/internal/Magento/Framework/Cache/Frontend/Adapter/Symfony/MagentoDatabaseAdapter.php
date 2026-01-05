<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter\Symfony;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Cache\Backend\Database;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Serialize\Serializer\Serialize;
use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;

/**
 * PSR-6 adapter for Magento's Database backend
 *
 * Wraps Magento\Framework\Cache\Backend\Database to make it PSR-6 compliant.
 * Allows using Magento's existing cache/cache_tag tables with Symfony architecture.
 */
class MagentoDatabaseAdapter implements AdapterInterface
{
    /**
     * @var Database
     */
    private Database $backend;

    /**
     * @var Serialize PHP native serializer (required for binary tag versions)
     */
    private Serialize $serializer;

    /**
     * @var array Deferred items to save
     */
    private array $deferred = [];

    /**
     * @var string Namespace prefix for cache keys
     */
    private string $namespace;

    /**
     * @var int Default lifetime in seconds
     */
    private int $defaultLifetime;

    /**
     * Constructor
     *
     * @param ResourceConnection $resource
     * @param Serialize $serializer PHP native serializer
     * @param string $namespace
     * @param int $defaultLifetime
     */
    public function __construct(
        ResourceConnection $resource,
        Serialize $serializer,
        string $namespace = '',
        int $defaultLifetime = 0
    ) {
        $this->serializer = $serializer;
        $this->namespace = $namespace;
        $this->defaultLifetime = $defaultLifetime;

        // Create Database backend with Magento's resource connection
        $this->backend = new Database([
            'adapter' => $resource->getConnection(),
            'data_table' => $resource->getTableName('cache'),
            'tags_table' => $resource->getTableName('cache_tag'),
            'store_data' => true
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getItem(mixed $key): CacheItem
    {
        $prefixedKey = $this->getPrefixedKey($key);
        $serializedData = $this->backend->load($prefixedKey);

        // Database backend returns serialized strings - we need to unserialize them
        $value = null;
        $isHit = false;
        $tagVersions = [];
        $expiry = null;

        if ($serializedData !== false) {
            // Unserialize the data structure using Magento's serializer
            $unserialized = $this->serializer->unserialize($serializedData);

            if ($unserialized !== false && is_array($unserialized)) {
                // New format with tag_versions
                if (isset($unserialized['data'])) {
                    $value = $unserialized['data'];
                    $tagVersions = $unserialized['tag_versions'] ?? [];
                    if (isset($unserialized['expire'])) {
                        $expiry = (float)$unserialized['expire'];
                    }
                    $isHit = true;
                } else {
                    // Fallback for old format (backward compatibility)
                    $value = $unserialized;
                    if (isset($unserialized['tags'])) {
                        // Old format stores tags, create tag name => tag name mapping
                        $tags = is_array($unserialized['tags']) ? $unserialized['tags'] : [];
                        $tagVersions = array_combine($tags, $tags);
                    }
                    if (isset($unserialized['expire'])) {
                        $expiry = (float)$unserialized['expire'];
                    }
                    $isHit = true;
                }
            } else {
                // Simple value (non-array)
                $value = $unserialized;
                $isHit = true;
            }
        }

        $item = new CacheItem();
        $this->setCacheItemState($item, $key, $value, $isHit, $tagVersions, $expiry);

        return $item;
    }

    /**
     * @inheritDoc
     */
    public function getItems(array $keys = []): iterable
    {
        $items = [];
        foreach ($keys as $key) {
            $items[$key] = $this->getItem($key);
        }
        return $items;
    }

    /**
     * @inheritDoc
     */
    public function hasItem(string $key): bool
    {
        $prefixedKey = $this->getPrefixedKey($key);
        return $this->backend->test($prefixedKey) !== false;
    }

    /**
     * @inheritDoc
     */
    public function clear(string $prefix = ''): bool
    {
        return $this->backend->clean(CacheConstants::CLEANING_MODE_ALL);
    }

    /**
     * @inheritDoc
     */
    public function deleteItem(string $key): bool
    {
        $prefixedKey = $this->getPrefixedKey($key);
        return $this->backend->remove($prefixedKey) !== false;
    }

    /**
     * @inheritDoc
     */
    public function deleteItems(array $keys): bool
    {
        $success = true;
        foreach ($keys as $key) {
            $success = $this->deleteItem($key) && $success;
        }
        return $success;
    }

    /**
     * @inheritDoc
     */
    public function save(CacheItemInterface $item): bool
    {
        $key = $item->getKey();
        $prefixedKey = $this->getPrefixedKey($key);

        // Get value
        $value = $this->getCacheItemValue($item);

        // Get tag versions from newMetadata (set by TagAwareAdapter)
        // TagAwareAdapter stores actual tag versions in newMetadata, not metadata
        $tagVersions = $this->extractTagVersions($item);

        // Create data structure with value, tags, and expiry
        $expiration = $this->getCacheItemExpiration($item);
        $lifetime = $expiration !== null ? ($expiration - time()) : $this->defaultLifetime;
        $expiryTime = $lifetime ? (time() + $lifetime) : 0;

        $dataStructure = [
            'data' => $value,
            'tags' => array_keys($tagVersions), // Tag names for Database backend
            'tag_versions' => $tagVersions,      // Actual tag versions with random bytes
            'mtime' => time(),
            'expire' => $expiryTime
        ];

        // Serialize the complete structure using Magento's serializer
        $serializedData = $this->serializer->serialize($dataStructure);

        // Save to database backend
        return $this->backend->save($serializedData, $prefixedKey, array_keys($tagVersions), $lifetime);
    }

    /**
     * @inheritDoc
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        $this->deferred[$item->getKey()] = $item;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function commit(): bool
    {
        $success = true;
        foreach ($this->deferred as $item) {
            $success = $this->save($item) && $success;
        }
        $this->deferred = [];
        return $success;
    }

    /**
     * Get Magento's Database backend
     *
     * @return Database
     */
    public function getBackend(): Database
    {
        return $this->backend;
    }

    /**
     * Get prefixed cache key
     *
     * @param string $key
     * @return string
     */
    private function getPrefixedKey(string $key): string
    {
        return $this->namespace !== '' ? $this->namespace . $key : $key;
    }

    /**
     * Extract tag versions from CacheItem's newMetadata
     *
     * TagAwareAdapter stores actual tag versions (random bytes) in newMetadata,
     * not in the metadata returned by getMetadata()
     *
     * @param CacheItemInterface $item
     * @return array Tag versions in format ['TAG1' => 'version_bytes', 'TAG2' => 'version_bytes']
     */
    private function extractTagVersions(CacheItemInterface $item): array
    {
        if (!$item instanceof CacheItem) {
            return [];
        }

        try {
            $reflection = new \ReflectionClass($item);

            // Try newMetadata first (set by TagAwareAdapter during save)
            if ($reflection->hasProperty('newMetadata')) {
                $newMetadataProperty = $reflection->getProperty('newMetadata');
                $newMetadataProperty->setAccessible(true);
                $newMetadata = $newMetadataProperty->getValue($item);

                if (isset($newMetadata[CacheItem::METADATA_TAGS]) && is_array($newMetadata[CacheItem::METADATA_TAGS])) {
                    return $newMetadata[CacheItem::METADATA_TAGS];
                }
            }

            // Fallback to regular metadata
            $metadata = $item->getMetadata();
            if (isset($metadata[CacheItem::METADATA_TAGS]) && is_array($metadata[CacheItem::METADATA_TAGS])) {
                return $metadata[CacheItem::METADATA_TAGS];
            }
            // phpcs:disable Magento2.CodeAnalysis.EmptyBlock
        } catch (\ReflectionException $e) {
            // Unable to access metadata - silently fail and return empty array
            // This can happen if CacheItem structure changes in future Symfony versions
        }
        // phpcs:enable Magento2.CodeAnalysis.EmptyBlock

        return [];
    }

    /**
     * Set cache item state using reflection
     *
     * @param CacheItem $item
     * @param string $key
     * @param mixed $value
     * @param bool $isHit
     * @param array $tagVersions Tag versions in format ['TAG1' => 'version_bytes']
     * @param float|null $expiry Expiration timestamp
     */
    private function setCacheItemState(
        CacheItem $item,
        string $key,
        $value,
        bool $isHit,
        array $tagVersions = [],
        ?float $expiry = null
    ): void {
        $reflection = new \ReflectionClass($item);

        // Set key
        $keyProperty = $reflection->getProperty('key');
        $keyProperty->setAccessible(true);
        $keyProperty->setValue($item, $key);

        // Set value
        $valueProperty = $reflection->getProperty('value');
        $valueProperty->setAccessible(true);
        $valueProperty->setValue($item, $value);

        // Set isHit
        $isHitProperty = $reflection->getProperty('isHit');
        $isHitProperty->setAccessible(true);
        $isHitProperty->setValue($item, $isHit);

        // Set expiry
        if ($expiry !== null && $expiry > 0) {
            $expiryProperty = $reflection->getProperty('expiry');
            $expiryProperty->setAccessible(true);
            $expiryProperty->setValue($item, $expiry);
        }

        // Set metadata with tag versions for TagAwareAdapter compatibility
        // TagAwareAdapter expects metadata[METADATA_TAGS] = ['TAG1' => 'version', 'TAG2' => 'version']
        if (!empty($tagVersions)) {
            $metadataProperty = $reflection->getProperty('metadata');
            $metadataProperty->setAccessible(true);

            // Store tag versions exactly as provided (with actual version bytes)
            $metadata = [
                \Symfony\Component\Cache\CacheItem::METADATA_TAGS => $tagVersions
            ];
            $metadataProperty->setValue($item, $metadata);
        }
    }

    /**
     * Get cache item value using reflection
     *
     * @param CacheItemInterface $item
     * @return mixed
     */
    private function getCacheItemValue(CacheItemInterface $item)
    {
        if ($item instanceof CacheItem) {
            $reflection = new \ReflectionClass($item);
            $valueProperty = $reflection->getProperty('value');
            $valueProperty->setAccessible(true);
            return $valueProperty->getValue($item);
        }
        return $item->get();
    }

    /**
     * Get cache item expiration using reflection
     *
     * @param CacheItemInterface $item
     * @return int|null
     */
    private function getCacheItemExpiration(CacheItemInterface $item): ?int
    {
        if ($item instanceof CacheItem) {
            $reflection = new \ReflectionClass($item);
            $expiryProperty = $reflection->getProperty('expiry');
            $expiryProperty->setAccessible(true);
            $expiry = $expiryProperty->getValue($item);
            return $expiry !== null ? (int)$expiry : null;
        }
        return null;
    }
}
