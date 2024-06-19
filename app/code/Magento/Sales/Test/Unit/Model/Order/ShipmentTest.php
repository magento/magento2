<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ShipmentTest extends TestCase
{
    /**
     * @var CollectionFactory|MockObject
     */
    private $commentCollectionFactory;

    /**
     * @var Collection|MockObject
     */
    private $commentCollection;

    /**
     * @var Shipment
     */
    private $shipmentModel;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $helperManager = new ObjectManager($this);

        $this->initCommentsCollectionFactoryMock();

        $this->shipmentModel = $helperManager->getObject(Shipment::class, [
            'commentCollectionFactory' => $this->commentCollectionFactory
        ]);
    }

    /**
     * Test to Returns increment id
     *
     * @return void
     */
    public function testGetIncrementId()
    {
        $this->shipmentModel->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->shipmentModel->getIncrementId());
    }

    /**
     * Test to Retrieves comments collection
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetCommentsCollection()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setShipment'])
            ->getMock();
        $shipmentItem->method('setShipment')
            ->with($this->shipmentModel);
        $collection = [$shipmentItem];

        $this->commentCollection->expects(self::once())
            ->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();
        $this->commentCollection->expects(self::once())
            ->method('setCreatedAtOrder')
            ->willReturnSelf();

        $reflection = new \ReflectionClass(Collection::class);
        $reflectionProperty = $reflection->getProperty('_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->commentCollection, $collection);

        $actual = $this->shipmentModel->getCommentsCollection();

        self::assertIsObject($actual);
        self::assertEquals($this->commentCollection, $actual);
    }

    /**
     * Test to Returns comments
     *
     * @return void
     * @throws \ReflectionException
     */
    public function testGetComments()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setShipment'])
            ->getMock();
        $shipmentItem->expects(self::once())
            ->method('setShipment')
            ->with($this->shipmentModel);
        $collection = [$shipmentItem];

        $this->commentCollection->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();

        $reflection = new \ReflectionClass(Collection::class);
        $reflectionProperty = $reflection->getProperty('_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->commentCollection, $collection);

        $this->commentCollection->expects(self::once())
            ->method('getItems')
            ->willReturn($collection);

        $actual = $this->shipmentModel->getComments();
        self::assertIsArray($actual);
        self::assertEquals($collection, $actual);
    }

    /**
     * Creates mock for comments collection factory
     * @return void
     */
    private function initCommentsCollectionFactoryMock()
    {
        $this->commentCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setShipmentFilter', 'setCreatedAtOrder', 'getItems', 'load'])
            ->getMock();

        $this->commentCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $this->commentCollectionFactory->method('create')
            ->willReturn($this->commentCollection);
    }
}
