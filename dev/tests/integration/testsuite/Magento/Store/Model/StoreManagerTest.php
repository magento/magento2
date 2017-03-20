<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model;

use Magento\Framework\ObjectManagerInterface as ObjectManager;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class StoreManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Class dependencies initialization
     *
     * @return void
     */
    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->storeManager = $this->objectManager->get(StoreManagerInterface::class);
    }

    /**
     * Check that behavior of setting and getting store into StoreManager is correct
     * Setting: Magento\Store\Model\StoreManagerInterface::setCurrentStore
     * Getting: Magento\Store\Model\StoreManagerInterface::getStore
     *
     * @return void
     */
    public function testDefaultStoreIdIsSetCorrectly()
    {
        $this->storeManager->setCurrentStore(Store::DEFAULT_STORE_ID);
        $this->assertEquals(Store::DEFAULT_STORE_ID, $this->storeManager->getStore()->getId());
    }
}
