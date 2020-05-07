<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\NewRelicReporting\Test\Unit\Model;

use Magento\Catalog\Api\CategoryManagementInterface;
use Magento\Catalog\Api\ProductManagementInterface;
use Magento\ConfigurableProduct\Api\ConfigurableProductManagementInterface;
use Magento\Customer\Api\CustomerManagementInterface;
use Magento\NewRelicReporting\Model\Counter;
use Magento\Store\Api\StoreManagementInterface;
use Magento\Store\Api\WebsiteManagementInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CounterTest extends TestCase
{
    /**
     * @var Counter
     */
    protected $model;

    /**
     * @var ProductManagementInterface|MockObject
     */
    protected $productManagement;

    /**
     * @var ConfigurableProductManagementInterface|MockObject
     */
    protected $configurableManagement;

    /**
     * @var CategoryManagementInterface|MockObject
     */
    protected $categoryManagement;

    /**
     * @var CustomerManagementInterface|MockObject
     */
    protected $customerManagement;

    /**
     * @var WebsiteManagementInterface|MockObject
     */
    protected $websiteManagement;

    /**
     * @var StoreManagementInterface|MockObject
     */
    protected $storeManagement;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->productManagement = $this->getMockBuilder(ProductManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->configurableManagement = $this
            ->getMockBuilder(ConfigurableProductManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->categoryManagement = $this->getMockBuilder(CategoryManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->customerManagement = $this->getMockBuilder(CustomerManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->websiteManagement = $this->getMockBuilder(WebsiteManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeManagement = $this->getMockBuilder(StoreManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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

        $this->assertIsInt($this->model->getAllProductsCount());
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

        $this->assertIsInt($this->model->getConfigurableCount());
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

        $this->assertIsInt($this->model->getActiveCatalogSize());
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

        $this->assertIsInt($this->model->getCategoryCount());
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

        $this->assertIsInt($this->model->getCustomerCount());
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

        $this->assertIsInt($this->model->getWebsiteCount());
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

        $this->assertIsInt($this->model->getStoreViewsCount());
    }
}
