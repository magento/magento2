<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Model\Product;

class CatalogPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\CatalogPrice
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
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceModelMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $associatedProductMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(\Magento\Store\Model\StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $productMethods = ['getWebsiteId', 'getCustomerGroupId', '__wakeup', 'getTypeInstance', 'setTaxClassId'];
        $this->productMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $productMethods);
        $methods = ['setWebsiteId', 'isSalable', '__wakeup', 'setCustomerGroupId', 'getTaxClassId'];
        $this->associatedProductMock = $this->createPartialMock(\Magento\Catalog\Model\Product::class, $methods);
        $this->priceModelMock = $this->createPartialMock(
            \Magento\Catalog\Model\Product\Type\Price::class,
            ['getTotalPrices']
        );
        $this->productTypeMock = $this->createMock(\Magento\GroupedProduct\Model\Product\Type\Grouped::class);

        $this->catalogPrice = new \Magento\GroupedProduct\Model\Product\CatalogPrice(
            $this->storeManagerMock,
            $this->commonPriceMock
        );
    }

    public function testGetCatalogPriceWithDefaultStoreAndWhenProductDoesNotHaveAssociatedProducts()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->productTypeMock
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            []
        );
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertNull($this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithDefaultStoreAndSubProductIsNotSalable()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->productTypeMock
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$this->associatedProductMock]
        );
        $this->productMock->expects($this->once())->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->willReturn('group_id');
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setWebsiteId'
        )->willReturn(
            $this->associatedProductMock
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setCustomerGroupId'
        )->with(
            'group_id'
        )->willReturn(
            $this->associatedProductMock
        );
        $this->associatedProductMock->expects($this->once())->method('isSalable')->willReturn(false);
        $this->productMock->expects($this->never())->method('setTaxClassId');
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertNull($this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithCustomStoreAndSubProductIsSalable()
    {
        $storeMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->createMock(\Magento\Store\Api\Data\StoreInterface::class);
        $currentStoreMock->expects($this->once())->method('getId')->willReturn('current_store_id');

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->willReturn(
            $this->productTypeMock
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->willReturn(
            [$this->associatedProductMock]
        );
        $this->productMock->expects($this->once())->method('getWebsiteId')->willReturn('website_id');
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->willReturn('group_id');
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setWebsiteId'
        )->willReturn(
            $this->associatedProductMock
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setCustomerGroupId'
        )->with(
            'group_id'
        )->willReturn(
            $this->associatedProductMock
        );
        $this->associatedProductMock->expects($this->once())->method('isSalable')->willReturn(true);
        $this->commonPriceMock->expects(
            $this->exactly(2)
        )->method(
            'getCatalogPrice'
        )->with(
            $this->associatedProductMock
        )->willReturn(
            15
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'getTaxClassId'
        )->willReturn(
            'tax_class'
        );
        $this->productMock->expects($this->once())->method('setTaxClassId')->with('tax_class');

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
