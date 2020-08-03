<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model\Product;

use Magento\Bundle\Model\Product\CatalogPrice;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Framework\Registry;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogPriceTest extends TestCase
{
    /**
     * @var CatalogPrice
     */
    protected $catalogPrice;

    /**
     * @var MockObject
     */
    protected $storeManagerMock;

    /**
     * @var MockObject
     */
    protected $commonPriceMock;

    /**
     * @var MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var MockObject
     */
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $priceModelMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $this->coreRegistryMock = $this->createMock(Registry::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getWebsiteId', 'getCustomerGroupId'])
            ->onlyMethods(['getStoreId', 'getPriceModel'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceModelMock = $this->getMockBuilder(Price::class)
            ->addMethods(['getTotalPrices'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->catalogPrice = new CatalogPrice(
            $this->storeManagerMock,
            $this->commonPriceMock,
            $this->coreRegistryMock
        );
    }

    public function testGetCatalogPriceWithCurrentStore()
    {
        $this->coreRegistryMock->expects($this->once())->method('unregister')->with('rule_data');
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $this->productMock->expects($this->once())->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->willReturn('group_id');
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPriceModel'
        )->willReturn(
            $this->priceModelMock
        );
        $this->priceModelMock->expects(
            $this->once()
        )->method(
            'getTotalPrices'
        )->with(
            $this->productMock,
            'min',
            false
        )->willReturn(
            15
        );
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithCustomStore()
    {
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->getMockForAbstractClass(StoreInterface::class);
        $currentStoreMock->expects($this->once())->method('getId')->willReturn('current_store_id');

        $this->coreRegistryMock->expects($this->once())->method('unregister')->with('rule_data');
        $this->productMock->expects($this->once())->method('getStoreId')->willReturn('store_id');
        $this->productMock->expects($this->once())->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->willReturn('group_id');
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPriceModel'
        )->willReturn(
            $this->priceModelMock
        );
        $this->priceModelMock->expects(
            $this->once()
        )->method(
            'getTotalPrices'
        )->with(
            $this->productMock,
            'min',
            true
        )->willReturn(
            15
        );

        $this->storeManagerMock->expects($this->at(0))->method('getStore')->willReturn($currentStoreMock);
        $this->storeManagerMock->expects($this->at(1))->method('setCurrentStore')->with('store_id');
        $this->storeManagerMock->expects($this->at(2))->method('setCurrentStore')->with('current_store_id');

        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    public function testGetCatalogRegularPrice()
    {
        $this->assertNull($this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
