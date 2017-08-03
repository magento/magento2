<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Acl\Data;

/**
 * ACL data cache layer.
 * @package Magento\Framework\Acl\Data
 * @since 2.2.0
 */
class Cache implements CacheInterface
{
    /**
     * Acl Data cache tag.
     */
    const ACL_DATA_CACHE_TAG = 'acl_cache';

    /**
     * @var \Magento\Framework\Config\CacheInterface
     * @since 2.2.0
     */
    private $cache;

    /**
     * @var \Magento\Framework\Acl\Builder
     * @since 2.2.0
     */
    private $aclBuilder;

    /**
     * @var string
     * @since 2.2.0
     */
    private $cacheTag;

    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param string $cacheTag
     * @since 2.2.0
     */
    public function __construct(
        \Magento\Framework\Config\CacheInterface $cache,
        \Magento\Framework\Acl\Builder $aclBuilder,
        $cacheTag = self::ACL_DATA_CACHE_TAG
    ) {
        $this->cache = $cache;
        $this->aclBuilder = $aclBuilder;
        $this->cacheTag = $cacheTag;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function test($identifier)
    {
        return $this->cache->test($identifier);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function load($identifier)
    {
        return $this->cache->load($identifier);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->cache->save($data, $identifier, array_merge($tags, [$this->cacheTag]), $lifeTime);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function remove($identifier)
    {
        return $this->cache->remove($identifier);
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_MATCHING_TAG, array $tags = [])
    {
        $this->aclBuilder->resetRuntimeAcl();
        return $this->cache->clean($mode, array_merge($tags, [$this->cacheTag]));
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getBackend()
    {
        return $this->cache->getBackend();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getLowLevelFrontend()
    {
        return $this->cache->getLowLevelFrontend();
    }
}
