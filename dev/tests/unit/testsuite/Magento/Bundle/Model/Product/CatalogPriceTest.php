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
namespace Magento\Bundle\Model\Product;

class CatalogPriceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Bundle\Model\Product\CatalogPrice
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
    protected $coreRegistryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $priceModelMock;

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
        $this->coreRegistryMock = $this->getMock('Magento\Framework\Registry', array(), array(), '', false);
        $methods = array('getStoreId', 'getWebsiteId', 'getCustomerGroupId', 'getPriceModel', '__wakeup');
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, array(), '', false);
        $this->priceModelMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Price',
            array('getTotalPrices'),
            array(),
            '',
            false
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
        $this->productMock->expects($this->once())->method('getStoreId')->will($this->returnValue('store_id'));
        $this->productMock->expects($this->once())->method('getWebsiteId')->will($this->returnValue('website_id'));
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue('group_id'));
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPriceModel'
        )->will(
            $this->returnValue($this->priceModelMock)
        );
        $this->priceModelMock->expects(
            $this->once()
        )->method(
            'getTotalPrices'
        )->with(
            $this->productMock,
            'min',
            false
        )->will(
            $this->returnValue(15)
        );
        $this->storeManagerMock->expects($this->never())->method('getStore');
        $this->storeManagerMock->expects($this->never())->method('setCurrentStore');
        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock));
    }

    public function testGetCatalogPriceWithCustomStore()
    {
        $storeMock = $this->getMock('Magento\Store\Model\Store', array(), array(), '', false);
        $this->coreRegistryMock->expects($this->once())->method('unregister')->with('rule_data');
        $this->productMock->expects($this->once())->method('getStoreId')->will($this->returnValue('store_id'));
        $this->productMock->expects($this->once())->method('getWebsiteId')->will($this->returnValue('website_id'));
        $this->productMock->expects($this->once())->method('getCustomerGroupId')->will($this->returnValue('group_id'));
        $this->coreRegistryMock->expects($this->once())->method('register');
        $this->productMock->expects(
            $this->once()
        )->method(
            'getPriceModel'
        )->will(
            $this->returnValue($this->priceModelMock)
        );
        $this->priceModelMock->expects(
            $this->once()
        )->method(
            'getTotalPrices'
        )->with(
            $this->productMock,
            'min',
            true
        )->will(
            $this->returnValue(15)
        );
        $this->storeManagerMock->expects($this->exactly(2))->method('setCurrentStore');
        $this->assertEquals(15, $this->catalogPrice->getCatalogPrice($this->productMock, $storeMock, true));
    }

    public function testGetCatalogRegularPrice()
    {
        $this->assertEquals(null, $this->catalogPrice->getCatalogRegularPrice($this->productMock));
    }
}
