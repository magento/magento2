<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

/** @var $cachePool \Magento\Framework\App\Cache\Frontend\Pool */
$cachePool = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Framework\App\Cache\Frontend\Pool');
/** @var $cacheFrontend \Magento\Framework\Cache\FrontendInterface */
foreach ($cachePool as $cacheFrontend) {
    $cacheFrontend->getBackend()->save('non-application cache data', 'NON_APPLICATION_FIXTURE', ['SOME_TAG']);
}
