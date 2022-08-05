<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Test\Unit\Fixtures;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\ConfigurableProduct\Api\LinkManagementInterface;
use Magento\ConfigurableProduct\Api\OptionRepositoryInterface;
use Magento\Framework\ObjectManager\ObjectManager;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Model\ResourceModel\Order;
use Magento\Setup\Fixtures\FixtureModel;
use Magento\Setup\Fixtures\OrdersFixture;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class OrdersFixtureTest extends TestCase
{

    /**
     * @var MockObject|FixtureModel
     */
    private $fixtureModelMock;

    /**
     * @var OrdersFixture
     */
    private $model;

    public function testExecute()
    {
        $storeManagerMock = $this->getMockBuilder(StoreManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $productCollectionFactoryMock = $this->getMockBuilder(
            CollectionFactory::class
        )
            ->disableOriginalConstructor()
            ->getMock();

        $productRepositoryMock = $this->getMockBuilder(ProductRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $optionRepositoryMock = $this->getMockBuilder(OptionRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $linkManagementMock = $this->getMockBuilder(LinkManagementInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $serializerMock = $this->getMockBuilder(SerializerInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->fixtureModelMock = $this->getMockBuilder(FixtureModel::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new OrdersFixture(
            $storeManagerMock,
            $productCollectionFactoryMock,
            $productRepositoryMock,
            $optionRepositoryMock,
            $linkManagementMock,
            $serializerMock,
            $this->fixtureModelMock
        );

        $orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(['getTableName', 'query', 'fetchColumn'])
            ->onlyMethods(['getTable', 'getConnection'])
            ->disableOriginalConstructor()
            ->getMock();

        $path = explode('\\', Order::class);
        $name = array_pop($path);

        $orderMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getTable')
            ->willReturn(strtolower($name) . '_table_name');
        $orderMock->expects($this->once())
            ->method('query')
            ->willReturn($orderMock);
        $orderMock->expects($this->once())
            ->method('getTableName')
            ->willReturn(strtolower($name) . '_table_name');

        $objectManagerMock = $this->createMock(ObjectManager::class);
        $objectManagerMock->expects($this->atLeastOnce())
            ->method('get')
            ->willReturn($orderMock);

        $this->fixtureModelMock
            ->expects($this->atLeastOnce())
            ->method('getObjectManager')
            ->willReturn($objectManagerMock);

        $this->model->execute();
    }
}
