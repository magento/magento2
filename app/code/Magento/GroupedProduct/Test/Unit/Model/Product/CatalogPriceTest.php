<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
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
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPrice::class);
        $productMethods = ['getWebsiteId', 'getCustomerGroupId', '__wakeup', 'getTypeInstance', 'setTaxClassId'];
        $this->productMock = $this->createPartialMock(Product::class, $productMethods);
        $methods = ['setWebsiteId', 'isSalable', '__wakeup', 'setCustomerGroupId', 'getTaxClassId'];
        $this->associatedProductMock = $this->createPartialMock(Product::class, $methods);
        $this->priceModelMock = $this->createPartialMock(
            Price::class,
            ['getTotalPrices']
        );
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
        )->will(
            $this->returnValue($this->productTypeMock)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([])
        );
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertEquals(null, $this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithDefaultStoreAndSubProductIsNotSalable()
    {
        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->productTypeMock)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([$this->associatedProductMock])
        );
        $this->productMock->expects($this->once())->method('getWebsiteId')->will($this->returnValue('website_id'));
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue('group_id'));
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setWebsiteId'
        )->will(
            $this->returnValue($this->associatedProductMock)
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setCustomerGroupId'
        )->with(
            'group_id'
        )->will(
            $this->returnValue($this->associatedProductMock)
        );
        $this->associatedProductMock->expects($this->once())->method('isSalable')->will($this->returnValue(false));
        $this->productMock->expects($this->never())->method('setTaxClassId');
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertEquals(null, $this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithCustomStoreAndSubProductIsSalable()
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->createMock(StoreInterface::class);
        $currentStoreMock->expects($this->once())->method('getId')->willReturn('current_store_id');

        $this->productMock->expects(
            $this->once()
        )->method(
            'getTypeInstance'
        )->will(
            $this->returnValue($this->productTypeMock)
        );
        $this->productTypeMock->expects(
            $this->once()
        )->method(
            'getAssociatedProducts'
        )->with(
            $this->productMock
        )->will(
            $this->returnValue([$this->associatedProductMock])
        );
        $this->productMock->expects($this->once())->method('getWebsiteId')->will($this->returnValue('website_id'));
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue('group_id'));
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setWebsiteId'
        )->will(
            $this->returnValue($this->associatedProductMock)
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'setCustomerGroupId'
        )->with(
            'group_id'
        )->will(
            $this->returnValue($this->associatedProductMock)
        );
        $this->associatedProductMock->expects($this->once())->method('isSalable')->will($this->returnValue(true));
        $this->commonPriceMock->expects(
            $this->exactly(2)
        )->method(
            'getCatalogPrice'
        )->with(
            $this->associatedProductMock
        )->will(
            $this->returnValue(15)
        );
        $this->associatedProductMock->expects(
            $this->once()
        )->method(
            'getTaxClassId'
        )->will(
            $this->returnValue('tax_class')
        );
        $this->productMock->expects($this->once())->method('setTaxClassId')->with('tax_class');

        $this->storeManagerMock->expects($this->at(0))->method('getStore')->willReturn($currentStoreMock);
        $this->storeManagerMock->expects($this->at(1))->method('setCurrentStore')->with('store_id');
        $this->storeManagerMock->expects($this->at(2))->method('setCurrentStore')->with('current_store_id');

        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    public function testGetCatalogRegularPrice()
    {
        $this->assertEquals(null, $this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
