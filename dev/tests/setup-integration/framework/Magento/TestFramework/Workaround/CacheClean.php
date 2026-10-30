<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

namespace Magento\TestFramework\Workaround;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Deployment config handler.
 *
 * @package Magento\TestFramework\Workaround
 */
class CacheClean
{
    /**
     * Start test.
     *
     * @return void
     */
    public function endTest()
    {
        /** @var \Magento\Framework\App\Cache\Manager $cacheManager */
        $cacheManager = Bootstrap::getObjectManager()->get(\Magento\Framework\App\Cache\Manager::class);
        $types = $cacheManager->getAvailableTypes();
        $cacheManager->clean($types);
    }
}
