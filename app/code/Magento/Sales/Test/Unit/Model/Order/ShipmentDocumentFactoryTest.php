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

        $this->invoiceDocumentFactory = new ShipmentDocumentFactory($this->shipmentFactoryMock);
    }

    public function testCreate()
    {
        $tracks = ["1234567890"];
        $appendComment = true;
        $packages = [];
        $this->invoiceDocumentFactory->create(
            $this->orderMock,
            $this->itemMock,
            $this->commentMock,
            $appendComment,
            $packages
        );
    }
}
