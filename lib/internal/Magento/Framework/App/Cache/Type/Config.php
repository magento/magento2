<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System / Cache Management / Cache type "Configuration"
 */
namespace Magento\Framework\App\Cache\Type;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Config\CacheInterface;

class Config extends TagScope implements CacheInterface
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'config';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'CONFIG';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
