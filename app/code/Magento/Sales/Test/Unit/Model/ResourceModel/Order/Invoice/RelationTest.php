<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Invoice;

/**
 * Class RelationTest
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Relation
     */
    protected $relationProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceItemResourceMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceCommentResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $invoiceCommentMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderItemMock;

    protected function setUp()
    {
        $this->invoiceItemResourceMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Invoice\Item::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->invoiceCommentResourceMock =
            $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Invoice\Comment::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->invoiceMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getItems',
                    'getComments'
                ]
            )
            ->getMock();
        $this->invoiceItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->invoiceCommentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Comment::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods([])
            ->getMock();
        $this->relationProcessor = new \Magento\Sales\Model\ResourceModel\Order\Invoice\Relation(
            $this->invoiceItemResourceMock,
            $this->invoiceCommentResourceMock
        );
    }

    public function testProcessRelation()
    {
        $this->invoiceMock->expects($this->once())
            ->method('getId')
            ->willReturn('invoice-id-value');
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$this->invoiceItemMock]);
        $this->invoiceItemMock->expects($this->once())
            ->method('setParentId')
            ->with('invoice-id-value')
            ->willReturnSelf();
        $this->invoiceItemMock->expects($this->once())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);
        $this->invoiceItemMock->expects($this->once())
            ->method('setOrderItem')
            ->with($this->orderItemMock)
            ->willReturnSelf();
        $this->invoiceItemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->invoiceItemMock)
            ->willReturnSelf();
        $this->invoiceMock->expects($this->exactly(2))
            ->method('getComments')
            ->willReturn([$this->invoiceCommentMock]);
        $this->invoiceCommentResourceMock->expects($this->once())
            ->method('save')
            ->with($this->invoiceCommentMock)
            ->willReturnSelf();
        $this->relationProcessor->processRelation($this->invoiceMock);
    }
}
