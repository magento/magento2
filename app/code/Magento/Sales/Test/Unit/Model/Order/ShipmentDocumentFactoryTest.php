<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Framework\EntityManager\HydratorPool;
use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Api\Data\ShipmentTrackCreationInterface;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Framework\EntityManager\HydratorInterface;

/**
 * Class ShipmentDocumentFactoryTest
 */
class ShipmentDocumentFactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentFactory
     */
    private $shipmentFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Order
     */
    private $orderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentItemCreationInterface
     */
    private $itemMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentCommentCreationInterface
     */
    private $commentMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|ShipmentInterface
     */
    private $shipmentMock;

    /**
     * @var ShipmentDocumentFactory
     */
    private $shipmentDocumentFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HydratorPool
     */
    private $hydratorPoolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|TrackFactory
     */
    private $trackFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|HydratorInterface
     */
    private $hydratorMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Track
     */
    private $trackMock;

    protected function setUp()
    {
        $this->shipmentFactoryMock = $this->getMockBuilder(ShipmentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->itemMock = $this->getMockBuilder(ShipmentItemCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->commentMock = $this->getMockBuilder(ShipmentCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->shipmentMock = $this->getMockBuilder(ShipmentInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['addComment', 'addTrack', 'setCustomerNote', 'setCustomerNoteNotify'])
            ->getMockForAbstractClass();

        $this->hydratorPoolMock = $this->getMockBuilder(HydratorPool::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackFactoryMock = $this->getMockBuilder(TrackFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackMock = $this->getMockBuilder(Track::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->hydratorMock = $this->getMockBuilder(HydratorInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->shipmentDocumentFactory = new ShipmentDocumentFactory(
            $this->shipmentFactoryMock,
            $this->hydratorPoolMock,
            $this->trackFactoryMock
        );
    }

    public function testCreate()
    {
        $trackNum = "123456789";
        $trackData = [$trackNum];
        $tracks = [$this->trackMock];
        $appendComment = true;
        $packages = [];
        $items = [1 => 10];

        $this->itemMock->expects($this->once())
            ->method('getOrderItemId')
            ->willReturn(1);

        $this->itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn(10);

        $this->shipmentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items
            )
            ->willReturn($this->shipmentMock);

        $this->shipmentMock->expects($this->once())
            ->method('addTrack')
            ->willReturnSelf();

        $this->hydratorPoolMock->expects($this->once())
            ->method('getHydrator')
            ->with(ShipmentTrackCreationInterface::class)
            ->willReturn($this->hydratorMock);

        $this->hydratorMock->expects($this->once())
            ->method('extract')
            ->with($this->trackMock)
            ->willReturn($trackData);

        $this->trackFactoryMock->expects($this->once())
            ->method('create')
            ->with(['data' => $trackData])
            ->willReturn($this->trackMock);

        if ($appendComment) {
            $comment = "New comment!";
            $visibleOnFront = true;
            $this->commentMock->expects($this->exactly(2))
                ->method('getComment')
                ->willReturn($comment);

            $this->commentMock->expects($this->once())
                ->method('getIsVisibleOnFront')
                ->willReturn($visibleOnFront);

            $this->shipmentMock->expects($this->once())
                ->method('addComment')
                ->with($comment, $appendComment, $visibleOnFront)
                ->willReturnSelf();

            $this->shipmentMock->expects($this->once())
                ->method('setCustomerNoteNotify')
                ->with(true);
        }

        $this->assertEquals(
            $this->shipmentDocumentFactory->create(
                $this->orderMock,
                [$this->itemMock],
                $tracks,
                $this->commentMock,
                $appendComment,
                $packages
            ),
            $this->shipmentMock
        );
    }
}
