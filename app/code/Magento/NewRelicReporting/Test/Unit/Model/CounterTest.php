<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\NewRelicReporting\Model\Counter;
use Magento\Catalog\Api\ProductManagementInterface;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Customer\Api\CustomerManagementInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use Magento\Store\Api\StoreManagementInterface;

/**
 * Class CounterTest
 */
class CounterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\NewRelicReporting\Model\Counter
     */
    protected $model;

    /**
     * @var ProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $productManagement;

    /**
     * @var ConfigurableProductManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $configurableManagement;

    /**
     * @var CategoryManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $categoryManagement;

    /**
     * @var CustomerManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerManagement;

    /**
     * @var WebsiteManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $websiteManagement;

    /**
     * @var StoreManagementInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagement;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp()
    {
        $this->productManagement = $this->getMockBuilder('Magento\Catalog\Api\ProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurableManagement = $this
            ->getMockBuilder('Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->categoryManagement = $this->getMockBuilder('Magento\Catalog\Api\CategoryManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerManagement = $this->getMockBuilder('Magento\Customer\Api\CustomerManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteManagement = $this->getMockBuilder('Magento\Store\Api\WebsiteManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeManagement = $this->getMockBuilder('Magento\Store\Api\StoreManagementInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Counter(
            $this->productManagement,
            $this->configurableManagement,
            $this->categoryManagement,
            $this->customerManagement,
            $this->websiteManagement,
            $this->storeManagement
        );
    }

    /**
     * Tests all products count will return int
     *
     * @return void
     */
    public function testGetAllProductsCount()
    {
        $this->productManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getAllProductsCount()
        );
    }

    /**
     * Tests all configurable products count will return int
     *
     * @return void
     */
    public function testGetConfigurableCount()
    {
        $this->configurableManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getConfigurableCount()
        );
    }

    /**
     * Tests all active products count will return int
     *
     * @return void
     */
    public function testGetActiveCatalogSize()
    {
        $this->productManagement->expects($this->once())
            ->method('getCount')
            ->with(1)
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getActiveCatalogSize()
        );
    }

    /**
     * Tests categories count will return int
     *
     * @return void
     */
    public function testGetCategoryCount()
    {
        $this->categoryManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getCategoryCount()
        );
    }

    /**
     * Tests customers count will return int
     *
     * @return void
     */
    public function testGetCustomerCount()
    {
        $this->customerManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getCustomerCount()
        );
    }

    /**
     * Tests websites count will return int
     *
     * @return void
     */
    public function testGetWebsiteCount()
    {
        $this->websiteManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getWebsiteCount()
        );
    }

    /**
     * Tests stores count will return int
     *
     * @return void
     */
    public function testGetStoreViewsCount()
    {
        $this->storeManagement->expects($this->once())
            ->method('getCount')
            ->willReturn(1);

        $this->assertInternalType(
            'int',
            $this->model->getStoreViewsCount()
        );
    }
}
