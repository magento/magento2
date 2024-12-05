<?php
/**
 * Unit test for customer adminhtml model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Customer\Model\Backend\Customer testing
 */
namespace Magento\Customer\Test\Unit\Model\Backend;

use Magento\Customer\Model\Backend\Customer;
use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\StoreManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    /** @var StoreManager|MockObject */
    protected $_storeManager;

    /** @var Customer */
    protected $_model;

    /**
     * Create model
     */
    protected function setUp(): void
    {
        $this->_storeManager = $this->createMock(StoreManager::class);
        $helper = new ObjectManager($this);
        $this->_model = $helper->getObject(
            Customer::class,
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
            $website = new DataObject(['store_ids' => [$websiteStoreId]]);
            $this->_storeManager->expects($this->once())->method('getWebsite')->willReturn($website);
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
    public static function getStoreDataProvider()
    {
        return [[1, 10, 5, 10], [0, 10, 5, 5]];
    }
}
