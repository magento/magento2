<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\GroupedProduct\Model\Product\CatalogPrice;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
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
    protected $productMock;

    /**
     * @var MockObject
     */
    protected $priceModelMock;

    /**
     * @var MockObject
     */
    protected $productTypeMock;

    /**
     * @var MockObject
     */
    protected $associatedProductMock;

    protected function setUp(): void
    {
        $this->storeManagerMock = $this->getMockForAbstractClass(StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->addMethods(['getWebsiteId', 'getCustomerGroupId', 'setTaxClassId'])
            ->onlyMethods(['__wakeup', 'getTypeInstance'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->associatedProductMock = $this->getMockBuilder(Product::class)
            ->addMethods(['setWebsiteId', 'setCustomerGroupId', 'getTaxClassId'])
            ->onlyMethods(['isSalable', '__wakeup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->priceModelMock = $this->getMockBuilder(Price::class)
            ->addMethods(['getTotalPrices'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->productTypeMock = $this->createMock(Grouped::class);

        $this->catalogPrice = new CatalogPrice(
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
        $storeMock = $this->getMockForAbstractClass(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->getMockForAbstractClass(StoreInterface::class);
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
