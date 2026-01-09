<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Cache;

use Magento\Framework\Cache\Backend\BackendInterface;
use Magento\Framework\Cache\CacheConstants;

/**
 * Interface of a cache frontend - an ultimate publicly available interface to an actual cache storage
 *
 * @api
 * @since 100.0.2
 */
interface FrontendInterface
{
    /**
     * Test if a cache is available for the given id
     *
     * @param string $identifier Cache id
     * @return int|bool Last modified time of cache entry if it is available, false otherwise
     */
    public function test($identifier);

    /**
     * Load cache record by its unique identifier
     *
     * @param string $identifier
     * @return string|bool
     */
    public function load($identifier);

    /**
     * Save cache record
     *
     * @param string $data
     * @param string $identifier
     * @param array $tags
     * @param int|bool|null $lifeTime
     * @return bool
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null);

    /**
     * Remove cache record by its unique identifier
     *
     * @param string $identifier
     * @return bool
     */
    public function remove($identifier);

    /**
     * Clean cache records matching specified tags
     *
     * @param string $mode
     * @param array $tags
     * @return bool
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_ALL, array $tags = []);

    /**
     * Retrieve backend instance
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getBackend();

    /**
     * Retrieve low-level frontend instance for compatibility
     *
     * @return \Psr\Cache\CacheItemPoolInterface
     */
    public function getLowLevelFrontend();
}
