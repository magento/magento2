<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\Item as ShipmentItem;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Comment\CollectionFactory;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ShipmentTest extends \PHPUnit_Framework_TestCase
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
     * @var \Magento\Sales\Model\Order\shipment
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

    /**
     * @covers \Magento\Sales\Model\Order\Shipment::getCommentsCollection
     */
    public function testGetCommentsCollection()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment'])
            ->getMock();
        $shipmentItem->expects(static::once())
            ->method('setShipment')
            ->with($this->shipmentModel);
        $collection = [$shipmentItem];

        $this->commentCollection->expects(static::once())
            ->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();
        $this->commentCollection->expects(static::once())
            ->method('setCreatedAtOrder')
            ->willReturnSelf();

        $this->commentCollection->expects(static::once())
            ->method('load')
            ->willReturnSelf();

        $reflection = new \ReflectionClass(Collection::class);
        $reflectionProperty = $reflection->getProperty('_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->commentCollection, $collection);

        $expected = $this->shipmentModel->getCommentsCollection();

        static::assertEquals($expected, $this->commentCollection);
    }

    /**
     * @covers \Magento\Sales\Model\Order\Shipment::getComments
     */
    public function testGetComments()
    {
        $shipmentId = 1;
        $this->shipmentModel->setId($shipmentId);

        $shipmentItem = $this->getMockBuilder(ShipmentItem::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipment'])
            ->getMock();
        $shipmentItem->expects(static::once())
            ->method('setShipment')
            ->with($this->shipmentModel);
        $collection = [$shipmentItem];

        $this->commentCollection->expects(static::once())
            ->method('setShipmentFilter')
            ->with($shipmentId)
            ->willReturnSelf();

        $this->commentCollection->expects(static::once())
            ->method('load')
            ->willReturnSelf();

        $reflection = new \ReflectionClass(Collection::class);
        $reflectionProperty = $reflection->getProperty('_items');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->commentCollection, $collection);
        
        $this->commentCollection->expects(static::once())
            ->method('getItems')
            ->willReturn($collection);
        
        static::assertEquals($this->shipmentModel->getComments(), $collection);
    }

    /**
     * Creates mock for comments collection factory
     * @return void
     */
    private function initCommentsCollectionFactoryMock()
    {
        $this->commentCollection = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setShipmentFilter', 'setCreatedAtOrder', 'getItems', 'load', '__wakeup'])
            ->getMock();

        $this->commentCollectionFactory = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->commentCollectionFactory->expects(static::any())
            ->method('create')
            ->willReturn($this->commentCollection);
    }
}
