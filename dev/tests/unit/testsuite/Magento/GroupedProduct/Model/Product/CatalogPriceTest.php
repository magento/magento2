<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Model\Product;

class CatalogPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GroupedProduct\Model\Product\CatalogPrice
     */
    protected $catalogPrice;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeManagerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $commonPriceMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productTypeMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $associatedProductMock;

    protected function setUp()
    {
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->commonPriceMock = $this->getMock(
            'Magento\Catalog\Model\Product\CatalogPrice',
            [],
            [],
            '',
            false
        );
        $productMethods = ['getWebsiteId', 'getCustomerGroupId', '__wakeup', 'getTypeInstance', 'setTaxClassId'];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $productMethods, [], '', false);
        $methods = ['setWebsiteId', 'isSalable', '__wakeup', 'setCustomerGroupId', 'getTaxClassId'];
        $this->associatedProductMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);
        $this->priceModelMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Price',
            ['getTotalPrices'],
            [],
            '',
            false
        );
        $this->productTypeMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            [],
            [],
            '',
            false
        );

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
        $storeMock = $this->getMock('Magento\Store\Model\Store', [], [], '', false);
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
        $this->storeManagerMock->expects($this->exactly(2))->method('setCurrentStore');
        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    public function testGetCatalogRegularPrice()
    {
        $this->assertEquals(null, $this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
