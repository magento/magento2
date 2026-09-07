<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\CacheConstants;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Frontend adapter for RemoteSynchronizedCache with Symfony backends
 *
 * This adapter implements FrontendInterface and wraps a RemoteSynchronizedCache backend,
 * allowing L2 cache to work seamlessly with Symfony cache backends.
 */
class RemoteSynchronizedSymfonyAdapter implements FrontendInterface
{
    /**
     * @var ExtendedBackendInterface
     */
    private ExtendedBackendInterface $backend;

    /**
     * Constructor
     *
     * The default lifetime is NOT applied here: save() forwards the lifetime through untouched
     * (including null => no expiry, matching legacy), and the actual default TTL is applied downstream
     * by the underlying Symfony adapter. The $defaultLifetime parameter is kept only for backward-
     * compatible DI wiring (the frontend Factory passes it) and is intentionally unused in this class.
     *
     * @param ExtendedBackendInterface $backend RemoteSynchronizedCache backend
     * @param int $defaultLifetime Kept for DI wiring; applied by the underlying Symfony adapter, not here
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExtendedBackendInterface $backend,
        int $defaultLifetime = 7200
    ) {
        $this->backend = $backend;
    }

    /**
     * @inheritDoc
     */
    public function test($identifier)
    {
        return $this->backend->test($identifier);
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        return $this->backend->load($identifier);
    }

    /**
     * Batched multi-load (used by the preloading wrapper).
     *
     * Delegates to the L2 backend's loadMultiple() when available (one round-trip to the remote
     * tier); otherwise falls back to per-key loads.
     *
     * @param string[] $identifiers
     * @return array<string, mixed>
     */
    public function loadMultiple(array $identifiers): array
    {
        if (method_exists($this->backend, 'loadMultiple')) {
            return $this->backend->loadMultiple($identifiers);
        }
        $out = [];
        foreach ($identifiers as $id) {
            $value = $this->backend->load($id);
            if ($value !== false) {
                $out[$id] = $value;
            }
        }
        return $out;
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        // Pass the lifetime through untouched (including null) so the underlying Symfony adapter's
        // calculateActualLifetime() is the single place that applies legacy semantics: null => no
        // expiration, false/0 => default lifetime. Coercing null to the default here forced a TTL on
        // L2 entries that legacy stored permanently.
        return $this->backend->save($data, $identifier, $tags, $lifeTime);
    }

    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        return $this->backend->remove($identifier);
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, $tags = [])
    {
        return $this->backend->clean($mode, $tags);
    }

    /**
     * Get the underlying backend
     *
     * @return ExtendedBackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Get low-level frontend (for backward compatibility)
     *
     * @return mixed
     */
    public function getLowLevelFrontend()
    {
        // Return self as we are the frontend
        return $this;
    }
}
