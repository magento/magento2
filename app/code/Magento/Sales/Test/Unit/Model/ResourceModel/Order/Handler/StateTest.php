<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\ResourceModel\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Class StateTest
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Handler\State
     */
    protected $state;

    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    protected function setUp()
    {
        $this->orderMock = $this->getMock(
            \Magento\Sales\Model\Order::class,
            [
                '__wakeup',
                'getId',
                'hasCustomerNoteNotify',
                'getCustomerNoteNotify',
                'isCanceled',
                'canUnhold',
                'canInvoice',
                'canShip',
                'getBaseGrandTotal',
                'canCreditmemo',
                'getState',
                'setState',
                'getTotalRefunded',
                'hasForcedCanCreditmemo',
                'getIsInProcess',
                'getConfig',
            ],
            [],
            '',
            false
        );
        $this->orderMock->expects($this->any())
            ->method('getConfig')
            ->willReturnSelf();
        $this->addressMock = $this->getMock(
            \Magento\Sales\Model\Order\Address::class,
            [],
            [],
            '',
            false
        );
        $this->addressCollectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Address\Collection::class,
            [],
            [],
            '',
            false
        );
        $this->state = new \Magento\Sales\Model\ResourceModel\Order\Handler\State();
    }

    /**
     * test check order - order without id
     */
    public function testCheckOrderEmpty()
    {
        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(100);
        $this->orderMock->expects($this->never())
            ->method('setState');

        $this->state->check($this->orderMock);
    }

    /**
     * test check order - set state complete
     */
    public function testCheckSetStateComplete()
    {
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('isCanceled')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canUnhold')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canShip')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->will($this->returnValue(100));
        $this->orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->exactly(2))
            ->method('getState')
            ->will($this->returnValue(Order::STATE_PROCESSING));
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_COMPLETE)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }

    /**
     * test check order - set state closed
     */
    public function testCheckSetStateClosed()
    {
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('isCanceled')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canUnhold')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canShip')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->will($this->returnValue(100));
        $this->orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->exactly(2))
            ->method('getTotalRefunded')
            ->will($this->returnValue(null));
        $this->orderMock->expects($this->once())
            ->method('hasForcedCanCreditmemo')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->exactly(2))
            ->method('getState')
            ->will($this->returnValue(Order::STATE_PROCESSING));
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_CLOSED)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }

    /**
     * test check order - set state processing
     */
    public function testCheckSetStateProcessing()
    {
        $this->orderMock->expects($this->any())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('isCanceled')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canUnhold')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canInvoice')
            ->will($this->returnValue(false));
        $this->orderMock->expects($this->once())
            ->method('canShip')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->once())
            ->method('getState')
            ->will($this->returnValue(Order::STATE_NEW));
        $this->orderMock->expects($this->once())
            ->method('getIsInProcess')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }
}
