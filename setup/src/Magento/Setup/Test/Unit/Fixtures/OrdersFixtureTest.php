<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Fixtures;

use \Magento\Setup\Fixtures\OrdersFixture;

class OrdersFixtureTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Magento\Setup\Fixtures\FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var \Magento\Setup\Fixtures\OrdersFixture
     */
    private $model;

    public function setUp()
    {
        $this->fixtureModelMock = $this->getMock('\Magento\Setup\Fixtures\FixtureModel', [], [], '', false);

        $this->model = new OrdersFixture($this->fixtureModelMock);
    }

    /**
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testExecute()
    {
        $mockObjectNames = [
            'Magento\Quote\Model\ResourceModel\Quote',
            'Magento\Quote\Model\ResourceModel\Quote\Address',
            'Magento\Quote\Model\ResourceModel\Quote\Item',
            'Magento\Quote\Model\ResourceModel\Quote\Item\Option',
            'Magento\Quote\Model\ResourceModel\Quote\Payment',
            'Magento\Quote\Model\ResourceModel\Quote\Address\Rate',
            'Magento\Reports\Model\ResourceModel\Event',
            'Magento\Sales\Model\ResourceModel\Order',
            'Magento\Sales\Model\ResourceModel\Order\Grid',
            'Magento\Sales\Model\ResourceModel\Order\Item',
            'Magento\Sales\Model\ResourceModel\Order\Payment',
            'Magento\Sales\Model\ResourceModel\Order\Status\History',
            '\Magento\Eav\Model\ResourceModel\Entity\Store'
        ];
        $mockObjects = [];

        foreach ($mockObjectNames as $mockObjectName) {
            $mockObject = $this->getMock($mockObjectName, ['getTable'], [], '', false);
            $path = explode('\\', $mockObjectName);
            $name = array_pop($path);
            if (strcasecmp($mockObjectName, 'Magento\Sales\Model\ResourceModel\Order') == 0) {
                $mockObject->expects($this->exactly(2))
                    ->method('getTable')
                    ->willReturn(strtolower($name) . '_table_name');
            } else {
                $mockObject->expects($this->once())
                    ->method('getTable')
                    ->willReturn(strtolower($name) . '_table_name');
            }
            $mockObjects[] = [$mockObjectName, $mockObject];
        }

        $connectionInterfaceMock = $this->getMockForAbstractClass(
            '\Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            true,
            true,
            true,
            []
        );
        $connectionInterfaceMock->expects($this->exactly(14))
            ->method('getTableName')
            ->willReturn('table_name');

        $resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $resourceMock->expects($this->exactly(15))
            ->method('getConnection')
            ->willReturn($connectionInterfaceMock);

        $websiteMock = $this->getMock('\Magento\Store\Model\Website', ['getId', 'getName'], [], '', false);
        $websiteMock->expects($this->once())
            ->method('getId')
            ->willReturn('website_id');
        $websiteMock->expects($this->once())
            ->method('getName')
            ->willReturn('website_name');

        $groupMock = $this->getMock('\Magento\Store\Model\Group', ['getName'], [], '', false);
        $groupMock->expects($this->once())
            ->method('getName')
            ->willReturn('group_name');

        $storeMock = $this->getMock(
            '\Magento\Store\Model\Store',
            [
                'getStoreId',
                'getWebsite',
                'getGroup',
                'getName',
                'getRootCategoryId'
            ],
            [],
            '',
            false
        );
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

        $storeManagerMock = $this->getMock('Magento\Store\Model\StoreManager', [], [], '', false);
        $storeManagerMock->expects($this->once())
            ->method('getStores')
            ->willReturn([$storeMock]);

        $contextMock = $this->getMock('\Magento\Framework\Model\ResourceModel\Db\Context', [], [], '', false);
        $abstractDbMock = $this->getMockForAbstractClass(
            '\Magento\Framework\Model\ResourceModel\Db\AbstractDb',
            [$contextMock],
            '',
            true,
            true,
            true,
            ['getAllChildren']
        );
        $abstractDbMock->expects($this->once())
            ->method('getAllChildren')
            ->will($this->returnValue([1]));

        $categoryMock = $this->getMock('Magento\Catalog\Model\Category', [], [], '', false);
        $categoryMock->expects($this->once())
            ->method('getResource')
            ->willReturn($abstractDbMock);
        $categoryMock->expects($this->once())
            ->method('getPath')
            ->willReturn('path/to/category');
        $categoryMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('category_name');
        $categoryMock->expects($this->exactly(5))
            ->method('load')
            ->willReturnSelf();

        $productMock = $this->getMock('\Magento\Catalog\Model\Product', ['load', 'getSku', 'getName'], [], '', false);
        $productMock->expects($this->exactly(2))
            ->method('load')
            ->willReturnSelf();
        $productMock->expects($this->exactly(2))
            ->method('getSku')
            ->willReturn('product_sku');
        $productMock->expects($this->exactly(2))
            ->method('getName')
            ->willReturn('product_name');

        $selectMock = $this->getMock('\Magento\Framework\DB\Select', [], [], '', false);

        $collectionMock = $this->getMock('\Magento\Catalog\Model\ResourceModel\Product\Collection', [], [], '', false);
        $collectionMock->expects($this->once())
            ->method('getSelect')
            ->willReturn($selectMock);
        $collectionMock->expects($this->once())
            ->method('getAllIds')
            ->willReturn([1, 1]);

        array_push(
            $mockObjects,
            ['Magento\Store\Model\StoreManager', [], $storeManagerMock],
            ['Magento\Catalog\Model\Category', $categoryMock],
            ['Magento\Catalog\Model\Product', $productMock],
            ['Magento\Framework\App\ResourceConnection', $resourceMock],
            ['Magento\Catalog\Model\ResourceModel\Product\Collection', [], $collectionMock]
        );

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->exactly(32))
            ->method('get')
            ->will($this->returnValueMap($mockObjects));
        $objectManagerMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnValueMap($mockObjects));

        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(1);
        $this->fixtureModelMock
            ->expects($this->exactly(34))
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }

    public function testNoFixtureConfigValue()
    {
        $connectionMock = $this->getMockForAbstractClass(
            '\Magento\Framework\DB\Adapter\AdapterInterface',
            [],
            '',
            true,
            true,
            true,
            []
        );
        $connectionMock->expects($this->never())
            ->method('query');

        $resourceMock = $this->getMock('Magento\Framework\App\ResourceConnection', [], [], '', false);
        $resourceMock->expects($this->never())
            ->method('getConnection')
            ->with($this->equalTo('write'))
            ->willReturn($connectionMock);

        $objectManagerMock = $this->getMock('Magento\Framework\ObjectManager\ObjectManager', [], [], '', false);
        $objectManagerMock->expects($this->never())
            ->method('get')
            ->with($this->equalTo('Magento\Framework\App\ResourceConnection'))
            ->willReturn($resourceMock);

        $this->fixtureModelMock
            ->expects($this->never())
            ->method('getObjectManagerMock')
            ->willReturn($objectManagerMock);
        $this->fixtureModelMock
            ->expects($this->once())
            ->method('getValue')
            ->willReturn(false);

        $this->model->execute();
    }

    public function testGetActionTitle()
    {
        $this->assertSame('Generating orders', $this->model->getActionTitle());
    }

    public function testIntroduceParamLabels()
    {
        $this->assertSame([
            'orders'     => 'Orders'
        ], $this->model->introduceParamLabels());
    }
}
