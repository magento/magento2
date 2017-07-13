<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Creditmemo;

/**
 * Class RelationTest
 */
class RelationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation
     */
    protected $relationProcessor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemResourceMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $creditmemoMock;

    /**
     * @var \Magento\Sales\Model\Order\Creditmemo\Item|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $itemMock;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $commentResourceMock;

    protected function setUp()
    {
        $this->itemResourceMock = $this->getMockBuilder(\Magento\Sales\Model\ResourceModel\Order\Creditmemo\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->commentResourceMock = $this->getMockBuilder(
            \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Comment::class
        )
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'save'
                ]
            )
            ->getMock();
        $this->creditmemoMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'getId',
                    'getItems',
                    'getComments'
                ]
            )
            ->getMock();
        $this->itemMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Item::class)
            ->disableOriginalConstructor()
            ->setMethods(
                [
                    'setParentId'
                ]
            )
            ->getMock();
        $this->commentMock = $this->getMockBuilder(\Magento\Sales\Model\Order\Creditmemo::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->relationProcessor = new \Magento\Sales\Model\ResourceModel\Order\Creditmemo\Relation(
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
