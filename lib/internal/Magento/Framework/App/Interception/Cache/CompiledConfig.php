<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Interception\Cache;

use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\Config\CacheInterface;
use Magento\Framework\App\Cache\Type\FrontendPool;

/**
 * Class \Magento\Framework\App\Interception\Cache\CompiledConfig
 *
 * @since 2.0.0
 */
class CompiledConfig extends TagScope implements CacheInterface
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'compiled_config';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'COMPILED_CONFIG';

    /**
     * @param FrontendPool $cacheFrontendPool
     * @since 2.0.0
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }
}
