<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Api\Data\ShipmentCommentCreationInterface;
use Magento\Sales\Api\Data\ShipmentItemCreationInterface;
use Magento\Sales\Model\Order\ShipmentFactory;
use Magento\Sales\Model\Order\ShipmentDocumentFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\ShipmentInterface;

/**
 * Class InvoiceDocumentFactoryTest
 */
class ShipmentDocumentFactoryTest extends \PHPUnit_Framework_TestCase
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
    private $invoiceDocumentFactory;

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
            ->setMethods(['addComment'])
            ->getMockForAbstractClass();

        $this->invoiceDocumentFactory = new ShipmentDocumentFactory($this->shipmentFactoryMock);
    }

    public function testCreate()
    {
        $tracks = ["1234567890"];
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
                $items,
                $tracks
            )
            ->willReturn($this->shipmentMock);

        if ($appendComment) {
            $comment = "New comment!";
            $visibleOnFront = true;
            $this->commentMock->expects($this->once())
                ->method('getComment')
                ->willReturn($comment);

            $this->commentMock->expects($this->once())
                ->method('getIsVisibleOnFront')
                ->willReturn($visibleOnFront);

            $this->shipmentMock->expects($this->once())
                ->method('addComment')
                ->with($comment, $appendComment, $visibleOnFront)
                ->willReturnSelf();
        }

        $this->assertEquals(
            $this->invoiceDocumentFactory->create(
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
