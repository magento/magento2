<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource\Order\Handler;

use Magento\Sales\Model\Order;

/**
 * Class StateTest
 */
class StateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Handler\State
     */
    protected $state;
    /**
     * @var \Magento\Sales\Model\Order|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $orderMock;

    public function setUp()
    {
        $this->orderMock = $this->getMock(
            'Magento\Sales\Model\Order',
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
                'getIsInProcess'
            ],
            [],
            '',
            false
        );
        $this->addressMock = $this->getMock(
            'Magento\Sales\Model\Order\Address',
            [],
            [],
            '',
            false
        );
        $this->addressCollectionMock = $this->getMock(
            'Magento\Sales\Model\Resource\Order\Address\Collection',
            [],
            [],
            '',
            false
        );
        $this->state = new \Magento\Sales\Model\Resource\Order\Handler\State();
    }

    /**
     * test check order - order without id
     */
    public function testCheckOrderEmpty()
    {
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $this->assertEquals($this->orderMock, $this->state->check($this->orderMock));
    }

    /**
     * test check order - set state complete
     */
    public function testCheckSetStateComplete()
    {
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('hasCustomerNoteNotify')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->will($this->returnValue(true));
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
            ->with(Order::STATE_COMPLETE, true, '', true)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }

    /**
     * test check order - set state closed
     */
    public function testCheckSetStateClosed()
    {
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('hasCustomerNoteNotify')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->will($this->returnValue(true));
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
            ->with(Order::STATE_CLOSED, true, '', true)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }

    /**
     * test check order - set state processing
     */
    public function testCheckSetStateProcessing()
    {
        $this->orderMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(1));
        $this->orderMock->expects($this->once())
            ->method('hasCustomerNoteNotify')
            ->will($this->returnValue(true));
        $this->orderMock->expects($this->once())
            ->method('getCustomerNoteNotify')
            ->will($this->returnValue(true));
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
            ->with(Order::STATE_PROCESSING, true, '', true)
            ->will($this->returnSelf());
        $this->assertEquals($this->state, $this->state->check($this->orderMock));
    }
}
