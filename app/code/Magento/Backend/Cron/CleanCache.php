<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Cron;

/**
 * Backend event observer
 * @since 2.0.0
 */
class CleanCache
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     * @since 2.0.0
     */
    private $cacheFrontendPool;

    /**
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
    ) {
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    /**
     * Cron job method to clean old cache resources
     *
     * @return void
     * @since 2.0.0
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            // Magento cache frontend does not support the 'old' cleaning mode, that's why backend is used directly
            $cacheFrontend->getBackend()->clean(\Zend_Cache::CLEANING_MODE_OLD);
        }
    }
}
