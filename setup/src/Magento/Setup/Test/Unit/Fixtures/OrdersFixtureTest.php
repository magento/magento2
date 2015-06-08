<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\OrdersFixture;

class OrdersFixtureTest extends \PHPUnit_Framework_TestCase
{
    private $mockObjectNames = array(
        'Magento\Quote\Model\Resource\Quote',
        'Magento\Quote\Model\Resource\Quote\Address',
        'Magento\Quote\Model\Resource\Quote\Item',
        'Magento\Quote\Model\Resource\Quote\Item\Option',
        'Magento\Quote\Model\Resource\Quote\Payment',
        'Magento\Quote\Model\Resource\Quote\Address\Rate',
        'Magento\Reports\Model\Resource\Event',
        'Magento\Sales\Model\Resource\Order',
        'Magento\Sales\Model\Resource\Order\Grid',
        'Magento\Sales\Model\Resource\Order\Item',
        'Magento\Sales\Model\Resource\Order\Payment',
        'Magento\Sales\Model\Resource\Order\Status\History',
        '\Magento\Eav\Model\Resource\Entity\Store'
    );

    private $mockObjects;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMockBuilder('\Magento\Setup\Fixtures\FixtureModel')->disableOriginalConstructor()->getMock();
    }

    public function testExecute()
    {
        $adapterInterfaceMock = $this->getMockBuilder('\Magento\Framework\DB\Adapter\AdapterInterface')->disableOriginalConstructor()->getMockForAbstractClass();
        $adapterInterfaceMock->expects($this->any())
            ->method('getTableName')
            ->willReturn('table_name');

        $resourceMock = $this->getMockBuilder('Magento\Framework\App\Resource')->disableOriginalConstructor()->getMock();
        $resourceMock->expects($this->any())
            ->method('getConnection')
            ->willReturn($adapterInterfaceMock);


        foreach ($this->mockObjectNames as $mockObjectName) {
            $mockObject = $this->getMockBuilder($mockObjectName)->disableOriginalConstructor()->getMock();
            $path = explode('\\', $mockObjectName);
            $name = array_pop($path);
            if (strcasecmp($mockObjectName, 'Magento\Sales\Model\Resource\Order') == 0) {
                $mockObject->expects($this->exactly(2))
                    ->method('getTable')
                    ->willReturn(strtolower($name) . '_table_name');
            } else {
                $mockObject->expects($this->once())
                    ->method('getTable')
                    ->willReturn(strtolower($name) . '_table_name');
            }
            $map = array($mockObjectName, $mockObject);
            $this->mockObjects[] = $map;
        }

        $websiteMock = $this->getMockBuilder('\Magento\Store\Model\Website')->disableOriginalConstructor()->setMethods(array('getId', 'getName'))->getMock();
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn('website_id');
        $websiteMock->expects($this->once())
            ->method('getName')
            ->willReturn('website_name');

        $groupMock = $this->getMockBuilder('\Magento\Store\Model\Group')->disableOriginalConstructor()->setMethods(array('getName'))->getMock();
        $groupMock->expects($this->once())
            ->method('getName')
            ->willReturn('group_name');

        $storeMock = $this->getMockBuilder('\Magento\Store\Model\Store')->disableOriginalConstructor()->setMethods(array(
            'getStoreId',
            'getWebsite',
            'getGroup',
            'getName',
            'getRootCategoryId'
        ))->getMock();
        $storeMock->expects($this->once())
            ->method('getStoreId')
            ->willReturn(1);
        $storeMock->expects($this->exactly(2))
            ->method('getWebsite')
            ->willReturn($websiteMock);
        $storeMock->expects($this->once())
            ->method('getGroup')
            ->willReturn($groupMock);
        $storeMock->expects($this->once())
            ->method('getName')
            ->willReturn('store_name');
        $storeMock->expects($this->once())
            ->method('getRootCategoryId')
            ->willReturn(1);

        $storeManager = $this->getMockBuilder('Magento\Store\Model\StoreManager')->disableOriginalConstructor()->getMock();
        $storeManager->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        $contextMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\Context')->disableOriginalConstructor()->getMock();
        $abstractDbMock = $this->getMockBuilder('\Magento\Framework\Model\Resource\Db\AbstractDb')->setConstructorArgs([$contextMock])->setMethods(['getAllChildren'])->getMockForAbstractClass();
        $abstractDbMock->expects($this->any())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMockBuilder('Magento\Catalog\Model\Category')->disableOriginalConstructor()->getMock();
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/category');
        $categoryMock->expects($this->any())
            ->method('getName')
            ->willReturn('category_name');
        $categoryMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->mockObjects[] = array('Magento\Catalog\Model\Category', $categoryMock);

        $productMock = $this->getMockBuilder('\Magento\Catalog\Model\Product')->disableOriginalConstructor()->getMock();
        $productMock->expects($this->any())
            ->method('load')
            ->willReturnSelf();
        $this->mockObjects[] = array('Magento\Catalog\Model\Product', $productMock);
        $this->mockObjects[] = array('Magento\Framework\App\Resource', $resourceMock);

        $selectMock = $this->getMockBuilder('\Magento\Framework\DB\Select')->disableOriginalConstructor()->getMock();

        $collectionMock = $this->getMockBuilder('\Magento\Catalog\Model\Resource\Product\Collection')->disableOriginalConstructor()->getMock();
        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 1]);

        $objectManagerMock = $this->getMockBuilder('Magento\Framework\ObjectManager\ObjectManager')->disableOriginalConstructor()->getMock();
        $objectManagerMock
            ->expects($this->any())
            ->method('get')
            ->will($this->returnValueMap($this->mockObjects));
        $objectManagerMock
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($storeManager, $collectionMock));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->fixtureModelMock
            ->expects($this->any())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $ordersFixture = new OrdersFixture($this->fixtureModelMock);
        $ordersFixture->execute();
    }

    public function testGetActionTitle()
    {
        $ordersFixture = new OrdersFixture($this->fixtureModelMock);
        $this->assertSame('Generating orders', $ordersFixture->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $ordersFixture = new OrdersFixture($this->fixtureModelMock);
        $this->assertSame([
            'orders'     => 'Orders'
        ], $ordersFixture->introduceParamLabels());
    }
}
