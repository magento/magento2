<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Invoice;

use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Comment;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Relation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RelationTest extends TestCase
{
    /**
     * @var Relation
     */
    protected $relationProcessor;

    /**
     * @var Item|MockObject
     */
    protected $invoiceItemResourceMock;

    /**
     * @var Comment|MockObject
     */
    protected $invoiceCommentResourceMock;

    /**
     * @var Invoice|MockObject
     */
    protected $invoiceMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Item|MockObject
     */
    protected $invoiceItemMock;

    /**
     * @var \Magento\Sales\Model\Order\Invoice\Comment|MockObject
     */
    protected $invoiceCommentMock;

    /**
     * @var \Magento\Sales\Model\Order\Item|MockObject
     */
    protected $orderItemMock;

    protected function setUp(): void
    {
        $this->invoiceItemResourceMock = $this->getMockBuilder(
            Item::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->invoiceCommentResourceMock =
            $this->getMockBuilder(Comment::class)
                ->disableOriginalConstructor()
                ->onlyMethods(
                    [
                        'save'
                    ]
                )
                ->getMock();
        $this->invoiceMock = $this->getMockBuilder(Invoice::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId',
                    'getItems',
                    'getComments'
                ]
            )
            ->getMock();
        $this->invoiceItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->invoiceCommentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice\Comment::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationProcessor = new Relation(
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
