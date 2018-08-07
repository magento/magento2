<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ShipmentTest extends \PHPUnit\Framework\TestCase
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

    protected function setUp()
    {
        $helperManager = new ObjectManager($this);

        $this->initCommentsCollectionFactoryMock();

        $this->shipmentModel = $helperManager->getObject(Shipment::class, [
            'commentCollectionFactory' => $this->commentCollectionFactory
        ]);
    }

    public function testGetIncrementId()
    {
        $this->shipmentModel->setIncrementId('test_increment_id');
        $this->assertEquals('test_increment_id', $this->shipmentModel->getIncrementId());
    }

    public function testGetCommentsCollection()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment'])
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

        self::assertTrue(is_object($actual));
        self::assertEquals($this->commentCollection, $actual);
    }

    public function testGetComments()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment'])
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
        self::assertTrue(is_array($actual));
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
            ->setMethods(['setShipmentFilter', 'setCreatedAtOrder', 'getItems', 'load'])
            ->getMock();

        $this->commentCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->commentCollectionFactory->method('create')
            ->willReturn($this->commentCollection);
    }
}
