<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MediaStorage\Model\Cache\Type;

use Magento\Store\Model\StoreManagerInterface;

/****
 * Class MediaStorage
 *
 * @package Magento\MediaStorage\Model\Cache\Type
 */
class MediaStorage extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'config';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'MEDIA_STORAGE';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     * @param StoreManagerInterface                          $storeManager
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

    /**
     * Generate cache ID using current context: user permissions and store
     *
     * @param  string $prefix Prefix is used by hashing function
     * @return string
     */
    public function generateCacheIdUsingContext($prefix)
    {
        return hash(
            'md5',
            $prefix . $this->storeManager->getStore()->getCode()
        );
    }
}
