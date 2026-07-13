<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

/** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
$cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create(\Magento\Framework\App\Cache\Frontend\Pool::class);
/** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
foreach ($cachePool as $cacheFrontend) {
    $cacheFrontend->getBackend()->save('non-application cache data', 'NON_APPLICATION_FIXTURE', ['SOME_TAG']);
}
