<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\Resource\Order\Shipment;

/**
 * Class RelationTest
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Relation
     */
    protected $relationProcessor;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemResourceMock;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackResourceMock;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Track|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $trackMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $shipmentMock;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    public function setUp()
    {
        $this->itemResourceMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Shipment\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->commentResourceMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Shipment\Comment')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->trackResourceMock = $this->getMockBuilder('Magento\Sales\Model\Resource\Order\Shipment\Track')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->shipmentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getItems',
                    'getTracks',
                    'getComments'
                ]
            )
            ->getMock();
        $this->itemMock = $this->getMockBuilder('Magento\Sales\Model\Order\Item')
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParentId'
                ]
            )
            ->getMock();
        $this->trackMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment\Track')
            ->disableOriginalConstructor()
            ->getMock();
        $this->commentMock = $this->getMockBuilder('Magento\Sales\Model\Order\Shipment')
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationProcessor = new \Magento\Sales\Model\Resource\Order\Shipment\Relation(
            $this->itemResourceMock,
            $this->trackResourceMock,
            $this->commentResourceMock
        );
    }

    public function testProcessRelations()
    {
        $this->shipmentMock->expects($this->once())
            ->method('getId')
            ->willReturn('shipment-id-value');
        $this->shipmentMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$this->itemMock]);
        $this->shipmentMock->expects($this->exactly(2))
            ->method('getComments')
            ->willReturn([$this->commentMock]);
        $this->shipmentMock->expects($this->exactly(2))
            ->method('getTracks')
            ->willReturn([$this->trackMock]);
        $this->itemMock->expects($this->once())
            ->method('setParentId')
            ->with('shipment-id-value')
            ->willReturnSelf();
        $this->itemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->itemMock)
            ->willReturnSelf();
        $this->commentResourceMock->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->trackResourceMock->expects($this->once())
            ->method('save')
            ->with($this->trackMock)
            ->willReturnSelf();
        $this->relationProcessor->processRelation($this->shipmentMock);
    }
}
