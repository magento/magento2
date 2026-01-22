<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Acl\Data;

use Magento\Framework\Cache\CacheConstants;

/**
 * ACL data cache layer.
 */
class Cache implements CacheInterface
{
    /**
     * Acl Data cache tag.
     */
    public const ACL_DATA_CACHE_TAG = 'acl_cache';

    /**
     * @var \Magento\Framework\Config\CacheInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\Acl\Builder
     */
    private $aclBuilder;

    /**
     * @var string
     */
    private $cacheTag;

    /**
     * Cache constructor.
     *
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param \Magento\Framework\Acl\Builder $aclBuilder
     * @param string $cacheTag
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
     * @inheritDoc
     */
    public function test($identifier)
    {
        return $this->cache->test($identifier);
    }

    /**
     * @inheritDoc
     */
    public function load($identifier)
    {
        return $this->cache->load($identifier);
    }

    /**
     * @inheritDoc
     */
    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->cache->save($data, $identifier, array_merge($tags, [$this->cacheTag]), $lifeTime);
    }

    /**
     * @inheritDoc
     */
    public function remove($identifier)
    {
        return $this->cache->remove($identifier);
    }

    /**
     * @inheritDoc
     */
    public function clean($mode = CacheConstants::CLEANING_MODE_MATCHING_TAG, array $tags = [])
    {
        $this->aclBuilder->resetRuntimeAcl();
        return $this->cache->clean($mode, array_merge($tags, [$this->cacheTag]));
    }

    /**
     * @inheritDoc
     */
    public function getBackend()
    {
        return $this->cache->getBackend();
    }

    /**
     * @inheritDoc
     */
    public function getLowLevelFrontend()
    {
        return $this->cache->getLowLevelFrontend();
    }
}
