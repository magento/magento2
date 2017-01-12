<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model\Order;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StateResolver;

/**
 * Class StateCheckerTest
 */
class StateResolverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Order
     */
    private $orderMock;

    /**
     * @var StateResolver
     */
    private $orderStateResolver;

    protected function setUp()
    {
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderStateResolver = new StateResolver();
    }

    public function testStateComplete()
    {
        $this->assertEquals(Order::STATE_COMPLETE, $this->orderStateResolver->getStateForOrder($this->orderMock));
    }

    public function testStateClosed()
    {
        $this->orderMock->expects($this->once())
            ->method('getBaseGrandTotal')
            ->willReturn(100);

        $this->orderMock->expects($this->once())
            ->method('canCreditmemo')
            ->willReturn(false);

        $this->orderMock->expects($this->once())
            ->method('getTotalRefunded')
            ->willReturn(10.99);

        $this->assertEquals(Order::STATE_CLOSED, $this->orderStateResolver->getStateForOrder($this->orderMock));
    }

    public function testStateNew()
    {
        $this->orderMock->expects($this->once())
            ->method('isCanceled')
            ->willReturn(true);
        $this->assertEquals(Order::STATE_NEW, $this->orderStateResolver->getStateForOrder($this->orderMock));
    }

    public function testStateProcessing()
    {
        $arguments = [StateResolver::IN_PROGRESS];
        $this->orderMock->expects($this->once())
            ->method('isCanceled')
            ->willReturn(true);

        $this->orderMock->expects($this->any())
            ->method('getState')
            ->willReturn(Order::STATE_NEW);

        $this->assertEquals(
            Order::STATE_PROCESSING,
            $this->orderStateResolver->getStateForOrder($this->orderMock, $arguments)
        );
    }
}
