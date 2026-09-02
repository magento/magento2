<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\LowLevelFrontendInterface;
use Magento\Framework\Cache\FrontendInterface;

/**
 * Low-level frontend wrapper for a remote-synchronized Symfony frontend.
 */
class RemoteSynchronizedLowLevelFrontend implements LowLevelFrontendInterface
{
    /**
     * @param FrontendInterface $frontend
     */
    public function __construct(private FrontendInterface $frontend)
    {
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = 'all', $tags = []): bool
    {
        return $this->frontend->clean($mode, $tags);
    }

    /**
     * Load a cache entry through the Magento frontend contract.
     */
    public function load(string $id)
    {
        return $this->frontend->load($id);
    }

    /**
     * Save a cache entry through the Magento frontend contract.
     */
    public function save($data, string $id, array $tags = [], $lifetime = false): bool
    {
        return $this->frontend->save($data, $id, $tags, $lifetime);
    }

    /**
     * Remove a cache entry through the Magento frontend contract.
     */
    public function remove(string $id): bool
    {
        return $this->frontend->remove($id);
    }

    /**
     * Return metadata through the wrapped frontend when supported.
     */
    public function getMetadatas(string $id)
    {
        return method_exists($this->frontend, 'getMetadatas')
            ? $this->frontend->getMetadatas($id)
            : false;
    }
}
