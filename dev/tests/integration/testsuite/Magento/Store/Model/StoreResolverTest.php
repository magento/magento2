<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
namespace Magento\Store\Model;

use Magento\TestFramework\Helper\CacheCleaner;

class StoreResolverTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
    }

    public function testGetStoreData()
    {
        $methodGetStoresData = new \ReflectionMethod(\Magento\Store\Model\StoreResolver::class, 'getStoresData');
        $methodGetStoresData->setAccessible(true);
        $methodReadStoresData = new \ReflectionMethod(\Magento\Store\Model\StoreResolver::class, 'readStoresData');
        $methodReadStoresData->setAccessible(true);

        $storeResolver = $this->objectManager->get(\Magento\Store\Model\StoreResolver::class);

        $storesDataRead = $methodReadStoresData->invoke($storeResolver);
        $storesData = $methodGetStoresData->invoke($storeResolver);
        $storesDataCached = $methodGetStoresData->invoke($storeResolver);
        $this->assertEquals($storesDataRead, $storesData);
        $this->assertEquals($storesDataRead, $storesDataCached);
    }
}
