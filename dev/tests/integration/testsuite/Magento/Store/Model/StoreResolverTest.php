<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\TestFramework\Helper\CacheCleaner;

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

        $storeResolver = $this->objectManager->get(\Magento\Store\Model\StoreResolver::class);

        $storesDataRead = $methodReadStoresData->invoke($storeResolver);
        CacheCleaner::cleanAll();
        $storesData = $methodGetStoresData->invoke($storeResolver);
        $storesDataCached = $methodGetStoresData->invoke($storeResolver);
        $this->assertEquals($storesDataRead, $storesData);
        $this->assertEquals($storesDataRead, $storesDataCached);
    }
}
