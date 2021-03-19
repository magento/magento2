<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\RemoteStorage\Model\Storage;

/**
 * Cache and complete storage.
 */
class CacheStorage
{
    /**
     * @var array
     */
    private $cache = [];

    /**
     * @var array
     */
    private $complete = [];

    /**
     * Retrieve all cached data.
     *
     * @return array
     */
    public function getCacheData(): array
    {
        return $this->cache;
    }

    /**
     * Retrieve cached data by key.
     *
     * @param string $key
     * @return mixed
     */
    public function getCacheDataByKey(string $key)
    {
        return $this->cache[$key] ?? false;
    }

    /**
     * Set cache data.
     *
     * @param array $cache
     */
    public function setCacheData(array $cache): void
    {
        $this->cache = $cache;
    }

    /**
     * Remove data from cache.
     *
     * @param string $key
     */
    public function removeCacheDataByKey(string $key): void
    {
        unset($this->cache[$key]);
    }

    /**
     * Add cache data.
     *
     * @param string $key
     * @param mixed $data
     */
    public function setCacheDataByKey(string $key, $data): void
    {
        $this->cache[$key] = $data;
    }

    /**
     * Verify if cache data exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasCacheData(string $key): bool
    {
        return isset($this->cache[$key]);
    }

    /**
     * Remove all cache data.
     */
    public function flushCache(): void
    {
        $this->cache = [];
    }

    /**
     * Remove all complete data.
     */
    public function flushComplete(): void
    {
        $this->complete = [];
    }

    /**
     * Set complete data.
     *
     * @param array $complete
     */
    public function setCompleteData(array $complete): void
    {
        $this->complete = $complete;
    }

    /**
     * Add complete data by key.
     *
     * @param string $key
     * @param mixed $data
     * @return void
     */
    public function setCompleteDataByKey(string $key, $data): void
    {
        $this->complete[$key] = $data;
    }

    /**
     * Retrieve data from complete by key.
     *
     * @param string $key
     * @return mixed
     */
    public function getCompleteDataByKey(string $key)
    {
        return $this->complete[$key] ?? false;
    }

    /**
     * Remove data from complete by key.
     *
     * @param string $key
     */
    public function removeCompleteDataByKey(string $key): void
    {
        unset($this->complete[$key]);
    }

    /**
     * Verify if data exists in complete.
     *
     * @param string $key
     * @return bool
     */
    public function hasCompleteData(string $key): bool
    {
        return isset($this->complete[$key]);
    }

    /**
     * Retrieve complete data.
     *
     * @return array
     */
    public function getCompleteData(): array
    {
        return $this->complete;
    }
}
