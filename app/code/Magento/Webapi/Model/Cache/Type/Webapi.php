<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Cache\Type;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Store\Model\StoreManagerInterface;

/**
 * System / Cache Management / Cache type "Web Services Configuration"
 */
class Webapi extends TagScope
{
    /**
     * Cache type code unique among all cache types
     */
    const TYPE_IDENTIFIER = 'config_webservice';

    /**
     * Cache tag used to distinguish the cache type from all other cache
     */
    const CACHE_TAG = 'WEBSERVICE';

    /**
     * @param FrontendPool $cacheFrontendPool
     * @param StoreManagerInterface $storeManager
     * @param UserContextInterface $userContext
     */
    public function __construct(
        FrontendPool $cacheFrontendPool,
        protected readonly StoreManagerInterface $storeManager,
        protected readonly UserContextInterface $userContext
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::CACHE_TAG);
    }

    /**
     * Generate cache ID using current context: user permissions and store
     *
     * @param string $prefix Prefix is used by hashing function
     * @return string
     */
    public function generateCacheIdUsingContext($prefix)
    {
        return hash(
            'md5',
            $prefix . $this->storeManager->getStore()->getCode()
            . $this->userContext->getUserType() . $this->userContext->getUserId()
        );
    }
}
