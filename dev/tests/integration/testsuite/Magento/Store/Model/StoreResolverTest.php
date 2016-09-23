<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

class StoreResolverTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(\Magento\Directory\Block\Data::class);
    }

    public function testGetStoreData()
    {
        $methodGetStoresData = new \ReflectionMethod(\Magento\Store\Model\StoreResolver::class, 'getStoresData');
        $methodGetStoresData->setAccessible(true);
        $methodReadStoresData = new \ReflectionMethod(\Magento\Store\Model\StoreResolver::class, 'readStoresData');
        $methodReadStoresData->setAccessible(true);

        $storeResover = $this->objectManager->get(\Magento\Store\Model\StoreResolver::class);

        $storesDataRead = $methodReadStoresData->invoke($storeResover);
        $this->cleanAllCache();
        $storesData = $methodGetStoresData->invoke($storeResover);
        $storesDataCached = $methodGetStoresData->invoke($storeResover);
        $this->assertEquals($storesDataRead, $storesData);
        $this->assertEquals($storesDataRead, $storesDataCached);
    }

    private function cleanAllCache()
    {
        /** @var \Magento\Framework\App\Cache\Frontend\Pool $cachePool */
        $cachePool = $this->objectManager->get(\Magento\Framework\App\Cache\Frontend\Pool::class);
        /** @var \Magento\Framework\Cache\FrontendInterface $cacheType */
        foreach ($cachePool as $cacheType) {
            $cacheType->getBackend()->clean();
        }
    }
}
