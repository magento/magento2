<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type\Price;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GroupedProduct\Model\Product\CatalogPrice;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CatalogPriceTest extends TestCase
{
    use MockCreationTrait;
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

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->commonPriceMock = $this->createMock(Product\CatalogPrice::class);
        $this->productMock = $this->createPartialMockWithReflection(
            \Magento\Catalog\Model\Product::class,
            ['getTypeInstance', 'getWebsiteId', 'getCustomerGroupId', 'setTaxClassId']
        );
        $this->associatedProductMock = $this->createPartialMockWithReflection(
            \Magento\Catalog\Model\Product::class,
            ['isSalable', 'setWebsiteId', 'setCustomerGroupId', 'getTaxClassId']
        );
        $this->priceModelMock = $this->createMock(Price::class);
        $this->productTypeMock = $this->createMock(Grouped::class);

        $this->catalogPrice = new CatalogPrice(
            $this->storeManagerMock,
            $this->commonPriceMock
        );
    }

    /**
     * @return void
     */
    public function testGetCatalogPriceWithDefaultStoreAndWhenProductDoesNotHaveAssociatedProducts(): void
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

    /**
     * @return void
     */
    public function testGetCatalogPriceWithDefaultStoreAndSubProductIsNotSalable(): void
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

    /**
     * @return void
     */
    public function testGetCatalogPriceWithCustomStoreAndSubProductIsSalable(): void
    {
        $storeMock = $this->createMock(StoreInterface::class);
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->createMock(StoreInterface::class);
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

        $this->storeManagerMock
            ->method('getStore')
            ->willReturn($currentStoreMock);
        $this->storeManagerMock
            ->method('setCurrentStore')
            ->willReturnCallback(function ($arg1) {
                if ($arg1 == 'store_id') {
                    return null;
                } elseif ($arg1 == 'current_store_id') {
                    return null;
                }
            });

        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    /**
     * @return void
     */
    public function testGetCatalogRegularPrice(): void
    {
        $this->assertNull($this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
