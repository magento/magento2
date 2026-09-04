<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Backend\Cron;

use Magento\Framework\Cache\CacheConstants;

/**
 * Backend event observer
 */
class CleanCache
{
    /**
     * @var \Magento\Framework\App\Cache\Frontend\Pool
     */
    private $cacheFrontendPool;

    /**
     * @param \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool
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
     */
    public function execute()
    {
        /** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            // The cache frontend does not support the 'old' cleaning mode, so the backend is used
            // directly (legacy parity). For symfony_l2 this prunes expired L1 files + sweeps the L2
            // tag index; Redis data keys auto-expire via native TTL.
            $cacheFrontend->getBackend()->clean(CacheConstants::CLEANING_MODE_OLD);
        }
    }
}
