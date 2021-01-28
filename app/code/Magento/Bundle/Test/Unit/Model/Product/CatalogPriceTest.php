<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

class CatalogPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\CatalogPrice
     */
    protected $catalogPrice;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $commonPriceMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $coreRegistryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceModelMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $this->coreRegistryMock = $this->createMock(\Magento\Framework\Registry::class);
        $methods = ['getStoreId', 'getWebsiteId', 'getCustomerGroupId', 'getPriceModel', '__wakeup'];
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $methods);
        $this->priceModelMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\Price::class,
            ['getTotalPrices']
        );
        $this->catalogPrice = new \Magento\Bundle\Model\Product\CatalogPrice(
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
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
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
