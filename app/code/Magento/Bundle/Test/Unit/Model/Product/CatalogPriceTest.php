<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model\Product;

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
        $this->storeManagerMock = $this->getMock('Magento\Store\Model\StoreManagerInterface');
        $this->commonPriceMock = $this->getMock(
            'Magento\Catalog\Model\Product\CatalogPrice',
            [],
            [],
            '',
            false
        );
        $this->coreRegistryMock = $this->getMock('Magento\Framework\Registry', [], [], '', false);
        $methods = ['getStoreId', 'getWebsiteId', 'getCustomerGroupId', 'getPriceModel', '__wakeup'];
        $this->productMock = $this->getMock('Magento\Catalog\Model\Product', $methods, [], '', false);
        $this->priceModelMock = $this->getMock(
            'Magento\Catalog\Model\Product\Type\Price',
            ['getTotalPrices'],
            [],
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
        $storeMock = $this->getMock('Magento\Store\Api\Data\StoreInterface');
        $storeMock->expects($this->once())->method('getId')->willReturn('store_id');
        $currentStoreMock = $this->getMock('Magento\Store\Api\Data\StoreInterface');
        $currentStoreMock->expects($this->once())->method('getId')->willReturn('current_store_id');

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
