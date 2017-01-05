<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Model\Cache\Type;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * System / Cache Management / Cache type "Web Services Configuration"
 */
class Webapi extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     * @param StoreManagerInterface $storeManager
     * @param UserContextInterface $userContext
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        StoreManagerInterface $storeManager,
        UserContextInterface $userContext
    ) {
        $this->storeManager = $storeManager;
        $this->userContext = $userContext;
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
