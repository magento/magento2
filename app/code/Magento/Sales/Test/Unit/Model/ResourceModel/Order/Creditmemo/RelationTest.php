<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Creditmemo;

use Magento\Sales\Model\Order\Creditmemo;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment as CreditMemoComment;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item;
use Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation;
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
    protected $itemResourceMock;

    /**
     * @var Comment|MockObject
     */
    protected $commentMock;

    /**
     * @var Creditmemo|MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Item|MockObject
     */
    protected $itemMock;

    /**
     * @var CreditMemoComment|MockObject
     */
    protected $commentResourceMock;

    protected function setUp(): void
    {
        $this->itemResourceMock = $this->getMockBuilder(Item::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->commentResourceMock = $this->getMockBuilder(
            CreditMemoComment::class
        )
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'getId',
                    'getItems',
                    'getComments'
                ]
            )
            ->getMock();
        $this->itemMock = $this->getMockBuilder(OrderItem::class)
            ->disableOriginalConstructor()
            ->addMethods(
                [
                    'setParentId'
                ]
            )
            ->getMock();
        $this->commentMock = $this->getMockBuilder(Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationProcessor = new Relation(
            $this->itemResourceMock,
            $this->commentResourceMock
        );
    }

    public function testProcessRelations()
    {
        $this->creditmemoMock->expects($this->once())
            ->method('getId')
            ->willReturn('creditmemo-id-value');
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('getItems')
            ->willReturn([$this->itemMock]);
        $this->creditmemoMock->expects($this->exactly(2))
            ->method('getComments')
            ->willReturn([$this->commentMock]);
        $this->itemMock->expects($this->once())
            ->method('setParentId')
            ->with('creditmemo-id-value')
            ->willReturnSelf();
        $this->itemResourceMock->expects($this->once())
            ->method('save')
            ->with($this->itemMock)
            ->willReturnSelf();
        $this->commentResourceMock->expects($this->once())
            ->method('save')
            ->with($this->commentMock)
            ->willReturnSelf();
        $this->relationProcessor->processRelation($this->creditmemoMock);
    }
}
