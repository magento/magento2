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
use Magento\Framework\Cache\MultiLoadInterface;

/**
 * Frontend adapter for RemoteSynchronizedCache with Symfony backends
 *
 * This adapter implements FrontendInterface and wraps a RemoteSynchronizedCache backend,
 * allowing L2 cache to work seamlessly with Symfony cache backends.
 */
class RemoteSynchronizedSymfonyAdapter implements
    FrontendInterface,
    MultiLoadInterface
{
    /**
     * @var ExtendedBackendInterface
     */
    private ExtendedBackendInterface $backend;

    /**
     * @var RemoteSynchronizedLowLevelFrontendFactory
     */
    private RemoteSynchronizedLowLevelFrontendFactory $lowLevelFrontendFactory;

    /**
     * @var RemoteSynchronizedLowLevelFrontend|null
     */
    private ?RemoteSynchronizedLowLevelFrontend $lowLevelFrontend = null;

    /**
     * Keeps $defaultLifetime only for backward-compatible DI wiring;
     *
     * Actual TTL handling is delegated to the underlying Symfony adapter.
     * save() forwards the lifetime unchanged, including null for no expiry, matching legacy behavior.
     *
     * @param ExtendedBackendInterface $backend RemoteSynchronizedCache backend
     * @param RemoteSynchronizedLowLevelFrontendFactory $lowLevelFrontendFactory Factory for the low-level view
     * @param int $defaultLifetime Kept for DI wiring; applied by the underlying Symfony adapter, not here
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        ExtendedBackendInterface $backend,
        RemoteSynchronizedLowLevelFrontendFactory $lowLevelFrontendFactory,
        int $defaultLifetime = 7200
    ) {
        $this->backend = $backend;
        $this->lowLevelFrontendFactory = $lowLevelFrontendFactory;
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
     * Batched multi-load; delegates to the backend's MultiLoadInterface, else returns [] (no per-key emulation).
     *
     * @param string[] $identifiers
     * @return array<string, mixed>
     */
    public function loadMultiple(array $identifiers): array
    {
        return $this->backend instanceof MultiLoadInterface
            ? $this->backend->loadMultiple($identifiers)
            : [];
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        // Passes the lifetime unchanged so the Symfony adapter alone applies legacy
        // expiration semantics, including `null` for no expiry.
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
     * Get cache entry metadata (Zend compatibility)
     *
     * @param string $id
     * @return array|false
     */
    public function getMetadatas($id)
    {
        return $this->backend->getMetadatas($id);
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, $tags = []): bool
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
        return $this->lowLevelFrontend ??= $this->lowLevelFrontendFactory->create(['backend' => $this->backend]);
    }
}
