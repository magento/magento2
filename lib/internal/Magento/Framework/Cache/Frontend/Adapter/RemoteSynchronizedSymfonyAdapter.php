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
     * @var int
     */
    private int $defaultLifetime;

    /**
     * Constructor
     *
     * @param ExtendedBackendInterface $backend RemoteSynchronizedCache backend
     * @param int $defaultLifetime Default cache lifetime
     */
    public function __construct(
        ExtendedBackendInterface $backend,
        int $defaultLifetime = 7200
    ) {
        $this->backend = $backend;
        $this->defaultLifetime = $defaultLifetime;
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
     * @inheritDoc
     */
    public function save($data, $identifier, $tags = [], $lifeTime = null)
    {
        $lifetime = $lifeTime ?? $this->defaultLifetime;
        return $this->backend->save($data, $identifier, $tags, $lifetime);
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
