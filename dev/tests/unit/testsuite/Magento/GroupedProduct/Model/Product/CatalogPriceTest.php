<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
        $this->storeManagerMock = $this->getMock('Magento\Framework\StoreManagerInterface');
        $this->commonPriceMock = $this->getMock(
            'Magento\Catalog\Model\Product\CatalogPrice',
            array(),
            array(),
            '',
            false
        );
        $productMethods = array('getWebsiteId', 'getCustomerGroupId', '__wakeup', 'getTypeInstance', 'setTaxClassId');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $productMethods, array(), '', false);
        $methods = array('setWebsiteId', 'isSalable', '__wakeup', 'setCustomerGroupId', 'getTaxClassId');
        $this->associatedProductMock = $this->getMock('Magento\Catalog\Model\Product', $methods, array(), '', false);
        $this->priceModelMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Price',
            array('getTotalPrices'),
            array(),
            '',
            false
        );
        $this->productTypeMock = $this->getMock(
            'Magento\GroupedProduct\Model\Product\Type\Grouped',
            array(),
            array(),
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
            $this->returnValue(array())
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
            $this->returnValue(array($this->associatedProductMock))
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
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
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
            $this->returnValue(array($this->associatedProductMock))
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
