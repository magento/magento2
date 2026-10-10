<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\MultiLoadInterface;

/**
 * Preloads frequently accessed Symfony cache keys into local PHP memory to avoid Redis network roundtrips.
 *
 */
class PreloadingSymfonyAdapter implements FrontendInterface
{
    /**
     * @var FrontendInterface
     */
    private FrontendInterface $adapter;

    /**
     * @var array
     */
    private array $localCache = [];

    /**
     * @var array
     */
    private array $preloadKeys;

    /**
     * Provides fast-path lookup using canonical cache IDs, ensuring preload keys match runtime IDs
     * while preserving original keys for diagnostics.
     *
     * @var array
     */
    private array $normalizedIndex = [];

    /**
     * Normalized forms of $preloadKeys (computed once), for membership checks in save().
     *
     * @var array
     */
    private array $normalizedPreloadKeys = [];

    /**
     * Whether the one-time preload has run yet (lazy, on first load).
     *
     * @var bool
     */
    private bool $preloaded = false;

    /**
     * Constructor
     *
     * @param FrontendInterface $adapter Underlying cache adapter
     * @param array $preloadKeys List of cache key identifiers to preload
     * @param string $idPrefix Backend id_prefix, so pre-prefixed config keys (e.g. "069_EAV_ENTITY_TYPES")
     *        are normalized to the raw id the application actually loads ("EAV_ENTITY_TYPES")
     */
    public function __construct(
        FrontendInterface $adapter,
        array $preloadKeys = [],
        string $idPrefix = ''
    ) {
        $this->adapter = $adapter;
        // Normalizes raw and pre-prefixed keys to prevent double-prefixing and ensure cache lookup hits.
        $this->preloadKeys = ($idPrefix !== '')
            ? array_map(
                static fn (string $key): string => str_starts_with($key, $idPrefix)
                    ? substr($key, strlen($idPrefix))
                    : $key,
                $preloadKeys
            )
            : $preloadKeys;
        $this->normalizedPreloadKeys = array_map(
            fn (string $key): string => $this->normalizeIdentifier($key),
            $this->preloadKeys
        );
    }

    /**
     * Normalizes identifiers to a canonical form, ensuring preload keys match the corresponding runtime cache IDs.
     *
     * @param string $identifier
     * @return string
     */
    private function normalizeIdentifier(string $identifier): string
    {
        // Single source of truth (shared with Symfony::cleanIdentifier) so the preload fast-path key
        // form cannot drift from the store-path key form.
        return Symfony\IdentifierNormalizer::normalize($identifier);
    }

    /**
     * Preload configured keys in one batched request via the adapter's MultiLoadInterface.
     *
     * Adapters without it skip preload (load() still serves per key). Keys are runtime IDs, no id_prefix.
     *
     * @return void
     */
    private function ensurePreloaded(): void
    {
        if ($this->preloaded) {
            return;
        }
        $this->preloaded = true;

        if (empty($this->preloadKeys)) {
            return;
        }

        if ($this->adapter instanceof MultiLoadInterface) {
            // one batched round-trip for all keys
            $this->localCache = $this->adapter->loadMultiple($this->preloadKeys);
        }

        // Builds a normalized fast-path index so preload keys match runtime IDs regardless of case or separators.
        $this->normalizedIndex = [];
        foreach ($this->localCache as $key => $value) {
            $this->normalizedIndex[$this->normalizeIdentifier((string)$key)] = $value;
        }
    }

    /**
     * @inheritDoc
     *
     * Checks the preload cache first (populated in one batched round-trip) before delegating to Redis.
     */
    public function load($identifier)
    {
        $this->ensurePreloaded();

        // Skips identifier normalization when no preload entries exist, avoiding unnecessary work on the hot path.
        if ($this->normalizedIndex === []) {
            return $this->adapter->load($identifier);
        }

        // Fast path: served from the in-process preload cache (no Redis round-trip). Look up by the
        // normalized id so configured preload keys match the runtime ids regardless of case/separators.
        $normalized = $this->normalizeIdentifier((string)$identifier);
        if (isset($this->normalizedIndex[$normalized])) {
            return $this->normalizedIndex[$normalized];
        }

        // Slow path: fetch from Redis
        return $this->adapter->load($identifier);
    }

    /**
     * @inheritDoc
     *
     * Writes through to Redis (bypasses local cache to avoid stale data)
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        // Write through to Redis
        $result = $this->adapter->save($data, $identifier, $tags, $lifeTime);

        // Keeps preloaded cache values updated after saves when the ID matches a configured preload key.
        if ($result && $this->normalizedPreloadKeys !== []) {
            $normalized = $this->normalizeIdentifier((string)$identifier);
            if (in_array($normalized, $this->normalizedPreloadKeys, true)) {
                $this->localCache[$identifier] = $data;
                $this->normalizedIndex[$normalized] = $data;
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter
     */
    public function test($identifier)
    {
        return $this->adapter->test($identifier);
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears from local cache
     */
    public function remove($identifier)
    {
        // Remove from local cache if present (both the original-keyed view and the normalized index).
        // Only pay for normalization when something was actually preloaded.
        unset($this->localCache[$identifier]);
        if ($this->normalizedIndex !== []) {
            unset($this->normalizedIndex[$this->normalizeIdentifier((string)$identifier)]);
        }

        return $this->adapter->remove($identifier);
    }

    /**
     * @inheritDoc
     *
     * Delegates to underlying adapter and clears local cache
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = [])
    {
        // Drop the preload cache and arm a lazy re-preload on the next load().
        $this->localCache = [];
        $this->normalizedIndex = [];
        $this->preloaded = false;

        return $this->adapter->clean($mode, $tags);
    }

    /**
     * @inheritDoc
     */
    public function getBackend()
    {
        return $this->adapter->getBackend();
    }

    /**
     * Delegate metadata lookup so backend operations such as touch() can preserve expiration.
     *
     * @param string $id
     * @return array|false
     */
    public function getMetadatas($id)
    {
        return $this->adapter->getMetadatas($id);
    }

    /**
     * @inheritDoc
     */
    public function getLowLevelFrontend()
    {
        return $this->adapter->getLowLevelFrontend();
    }

    /**
     * Get statistics about preloaded keys
     *
     * Useful for monitoring and debugging
     *
     * @return array
     */
    public function getPreloadStats(): array
    {
        return [
            'preload_keys_configured' => count($this->preloadKeys),
            'preload_keys_cached' => count($this->localCache),
            'cached_keys' => array_keys($this->localCache),
        ];
    }
}
