<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Acl\Data;

/**
 * ACL data cache layer.
 * @package Magento\Framework\Acl\Data
 */
class Cache implements CacheInterface
{
    /**
     * Acl Data cache tag.
     */
    const ACL_DATA_CACHE_TAG = 'acl_cache';

    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    private $aclBuilder;

    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\Config\CacheInterface $cache
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Acl\Builder\Proxy $aclBuilder
    ) {
        $this->cache = $cache;
        $this->aclBuilder = $aclBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function test($identifier)
    {
        return $this->cache->test($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function load($identifier)
    {
        return $this->cache->load($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->cache->save($data, $identifier, $tags + [self::ACL_DATA_CACHE_TAG], $lifeTime);
    }

    /**
     * {@inheritdoc}
     */
    public function remove($identifier)
    {
        return $this->cache->remove($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        $this->aclBuilder->clearCachedAcl();
        return $this->cache->clean($mode, $tags + [self::ACL_DATA_CACHE_TAG]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBackend()
    {
        return $this->cache->getBackend();
    }

    /**
     * {@inheritdoc}
     */
    public function getLowLevelFrontend()
    {
        return $this->cache->getLowLevelFrontend();
    }
}
