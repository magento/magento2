<?php
/**
 * Unit test for customer adminhtml model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Customer\Model\Backend\Customer testing
 */
namespace Magento\Customer\Test\Unit\Model\Backend;

class CustomerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Store\Model\StoreManager|\PHPUnit_Framework_MockObject_MockObject */
    protected $_storeManager;

    /** @var \Magento\Customer\Model\Backend\Customer */
    protected $_model;

    /**
     * Create model
     */
    protected function setUp()
    {
        $this->_storeManager = $this->getMock(\Magento\Store\Model\StoreManager::class, [], [], '', false);
        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_model = $helper->getObject(
            \Magento\Customer\Model\Backend\Customer::class,
            ['storeManager' => $this->_storeManager]
        );
    }

    /**
     * @dataProvider getStoreDataProvider
     * @param $websiteId
     * @param $websiteStoreId
     * @param $storeId
     * @param $result
     */
    public function testGetStoreId($websiteId, $websiteStoreId, $storeId, $result)
    {
        if ($websiteId * 1) {
            $this->_model->setWebsiteId($websiteId);
            $website = new \Magento\Framework\DataObject(['store_ids' => [$websiteStoreId]]);
            $this->_storeManager->expects($this->once())->method('getWebsite')->will($this->returnValue($website));
        } else {
            $this->_model->setStoreId($storeId);
            $this->_storeManager->expects($this->never())->method('getWebsite');
        }
        $this->assertEquals($result, $this->_model->getStoreId());
    }

    /**
     * Data provider for testGetStoreId
     * @return array
     */
    public function getStoreDataProvider()
    {
        return [[1, 10, 5, 10], [0, 10, 5, 5]];
    }
}
