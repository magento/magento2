<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Cache\Frontend;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Config\CacheInterface;

/**
 * Stale config cache instance
 *
 */
class StaleCache implements CacheInterface
{
    /** @var FrontendPool */
    private $cacheFrontendPool;

    /** @var FrontendInterface */
    private $frontend;

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

    public function save($data, $identifier, array $tags = [], $lifeTime = null)
    {
        $tags[] = $this->cacheTag;

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
        return $this->findFrontend()->test($this->formatCacheIdentifier($identifier));
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
        if ($mode === \Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG) {
            $result = true;
            foreach ($tags as $tag) {
                $result = $this->findFrontend()
                        ->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [$this->cacheTag, $tag]) && $result;
            }

            return $result;
        }


        $tags[] = $this->cacheTag;
        return $this->findFrontend()->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
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
     * Finds cache frontend by cache type in frontend pool.
     */
    private function findFrontend(): FrontendInterface
    {
        if (!$this->frontend) {
            $this->frontend = $this->cacheFrontendPool->get($this->cacheType);
        }

        return $this->frontend;
    }

    /**
     * @param $identifier
     * @return string
     */
    private function formatCacheIdentifier($identifier): string
    {
        return sprintf($this->identifierFormat, $identifier);
    }
}
