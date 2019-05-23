<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Frontend;

use Magento\Framework\App\Cache\Type\Config;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Config\CacheInterface;

/**
 * Stale config cache instance
 *
 */
class StaleCache implements CacheInterface
{
    const TYPE_IDENTIFIER = Config::TYPE_IDENTIFIER;

    /** @var FrontendPool */
    private $cacheFrontendPool;

    /** @var FrontendInterface */
    private $decoratedFrontend;

    /** @var string */
    private $identifierFormat;
    /**
     * @var string
     */
    private $cacheType;
    /**
     * @var string
     */
    private $cacheTag;


    public function __construct(
        FrontendPool $cacheFrontendPool,
        string $cacheType,
        string $identifierFormat,
        string $cacheTag
    ) {
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->identifierFormat = $identifierFormat;
        $this->cacheType = $cacheType;
        $this->cacheTag = $cacheTag;
    }

    /**
     * Find frontend to use for cache storage
     *
     * @return FrontendInterface
     */
    private function findFrontend()
    {
        if (!$this->decoratedFrontend) {
            $this->decoratedFrontend = $this->cacheFrontendPool->get(self::TYPE_IDENTIFIER);
        }

        return $this->decoratedFrontend;
    }

    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        return $this->findFrontend()->save(
            $data,
            $this->formatCacheIdentifier($identifier),
            $tags,
            $lifeTime
        );
    }

    /**
     * {@inheritDoc}
     */
    public function test($identifier)
    {

    }

    /**
     * {@inheritDoc}
     */
    public function load($identifier)
    {
        return $this->findFrontend()->load($this->formatCacheIdentifier($identifier));
    }

    /**
     * {@inheritDoc}
     */
    public function remove($identifier)
    {
        return $this->findFrontend()->remove($this->formatCacheIdentifier($identifier));
    }

    /**
     * {@inheritDoc}
     */
    public function clean($mode = \Zend_Cache::CLEANING_MODE_ALL, array $tags = [])
    {
        // TODO: Implement clean() method.
    }

    /**
     * Retrieve backend instance
     *
     * @return \Zend_Cache_Backend_Interface
     */
    public function getBackend()
    {
        return $this->findFrontend()->getBackend();
    }

    /**
     * Retrieve frontend instance compatible with Zend Locale Data setCache() to be used as a workaround
     *
     * @return \Zend_Cache_Core
     */
    public function getLowLevelFrontend()
    {
        return $this->findFrontend()->getLowLevelFrontend();
    }

    /**
     * @param $identifier
     * @return string
     */
    private function formatCacheIdentifier($identifier): string
    {
        $identifier = sprintf($this->identifierFormat, $identifier);

        return $identifier;
    }
}
