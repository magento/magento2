<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Cache\LowLevelFrontendInterface;

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

    /**
     * Get a cache option (e.g. 'cache_id_prefix', 'lifetime') from the remote (L2) tier's low-level frontend.
     */
    public function getOption(string $name)
    {
        $lowLevel = $this->getRemoteLowLevelFrontend();

        return ($lowLevel !== null && method_exists($lowLevel, 'getOption'))
            ? $lowLevel->getOption($name)
            : null;
    }

    /**
     * Get cache ids matching the given tags via the remote (L2) tier's tag adapter.
     *
     * @param string[] $tags
     * @return string[]
     */
    public function getIdsMatchingTags(array $tags = []): array
    {
        $lowLevel = $this->getRemoteLowLevelFrontend();

        return ($lowLevel !== null && method_exists($lowLevel, 'getIdsMatchingTags'))
            ? $lowLevel->getIdsMatchingTags($tags)
            : [];
    }

    /**
     * Get the remote (L2) tier's backend, for backward compatibility with code that expects direct backend access.
     *
     * @return mixed|null
     */
    public function getBackend()
    {
        $lowLevel = $this->getRemoteLowLevelFrontend();

        return ($lowLevel !== null && method_exists($lowLevel, 'getBackend'))
            ? $lowLevel->getBackend()
            : null;
    }

    /**
     * Reach through the RemoteSynchronizedLowLevelFrontend backend to the remote (L2) tier's low-level frontend.
     *
     * @return mixed|null
     */
    private function getRemoteLowLevelFrontend()
    {
        if (!method_exists($this->frontend, 'getBackend')) {
            return null;
        }

        $backend = $this->frontend->getBackend();
        if (!method_exists($backend, 'getRemote')) {
            return null;
        }

        $remote = $backend->getRemote();

        return method_exists($remote, 'getLowLevelFrontend') ? $remote->getLowLevelFrontend() : null;
    }
}
