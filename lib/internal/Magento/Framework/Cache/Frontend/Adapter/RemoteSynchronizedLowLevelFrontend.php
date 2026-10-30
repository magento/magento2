<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Frontend\Adapter;

use Magento\Framework\Cache\Backend\ExtendedBackendInterface;
use Magento\Framework\Cache\Backend\TwoTierBackendInterface;
use Magento\Framework\Cache\Frontend\Adapter\SymfonyAdapters\TagAdapterInterface;
use Magento\Framework\Cache\LowLevelFrontendInterface;

/**
 * Low-level frontend for a two-tier backend. Depends downward on the backend, not on FrontendInterface.
 */
class RemoteSynchronizedLowLevelFrontend implements LowLevelFrontendInterface
{
    /**
     * @param ExtendedBackendInterface $backend
     */
    public function __construct(private ExtendedBackendInterface $backend)
    {
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = 'all', $tags = []): bool
    {
        return $this->backend->clean($mode, $tags);
    }

    /**
     * Load a cache entry through the backend contract.
     *
     * @param string $id
     * @return mixed
     */
    public function load(string $id)
    {
        return $this->backend->load($id);
    }

    /**
     * Save a cache entry through the backend contract.
     *
     * @param mixed $data
     * @param string $id
     * @param array $tags
     * @param int|false|null $lifetime
     * @return bool
     */
    public function save($data, string $id, array $tags = [], $lifetime = false): bool
    {
        return $this->backend->save($data, $id, $tags, $lifetime);
    }

    /**
     * Remove a cache entry through the backend contract.
     *
     * @param string $id
     * @return bool
     */
    public function remove(string $id): bool
    {
        return $this->backend->remove($id);
    }

    /**
     * Return metadata through the backend contract (guaranteed by ExtendedBackendInterface).
     *
     * @param string $id
     * @return array|false
     */
    public function getMetadatas(string $id)
    {
        return $this->backend->getMetadatas($id);
    }

    /**
     * Get a cache option (e.g. 'cache_id_prefix', 'lifetime') from the remote (L2) tier's low-level frontend.
     *
     * @param string $name
     * @return mixed
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
     * Reach through to the remote (L2) tier's tag adapter.
     *
     * @return TagAdapterInterface|null
     */
    public function getTagAdapter(): ?TagAdapterInterface
    {
        $lowLevel = $this->getRemoteLowLevelFrontend();

        return $lowLevel instanceof LowLevelFrontendInterface ? $lowLevel->getTagAdapter() : null;
    }

    /**
     * Reach the remote (L2) tier's low-level frontend via the two-tier backend contract.
     *
     * @return mixed|null
     */
    private function getRemoteLowLevelFrontend()
    {
        if (!$this->backend instanceof TwoTierBackendInterface) {
            return null;
        }

        return $this->backend->getRemote()->getLowLevelFrontend();
    }
}
