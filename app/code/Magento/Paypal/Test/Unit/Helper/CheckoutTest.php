<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Paypal\Helper\Checkout
 */
namespace Magento\Paypal\Test\Unit\Helper;

use Magento\Checkout\Model\Session;
use Magento\Paypal\Helper\Checkout;
use Magento\Sales\Model\Order;

class CheckoutTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Session|\PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var Checkout
     */
    private $checkout;

    protected function setUp()
    {
        $this->session = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkout = new Checkout($this->session);
    }

    public function testCancelCurrentOrder()
    {
        $id = 1;
        $state = Order::STATE_PENDING_PAYMENT;
        $comment = 'Bla Bla';

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session->expects(static::once())
            ->method('getLastRealOrder')
            ->willReturn($order);
        $order->expects(static::once())
            ->method('getId')
            ->willReturn($id);
        $order->expects(static::once())
            ->method('getState')
            ->willReturn($state);
        $order->expects(static::once())
            ->method('registerCancellation')
            ->with($comment)
            ->willReturnSelf();
        $order->expects(static::once())
            ->method('save');

        static::assertTrue($this->checkout->cancelCurrentOrder($comment));
    }

    public function testCancelCurrentOrderWhichIsCancelled()
    {
        $id = 1;
        $state = Order::STATE_CANCELED;
        $comment = 'Bla Bla';

        $order = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session->expects(static::once())
            ->method('getLastRealOrder')
            ->willReturn($order);
        $order->expects(static::once())
            ->method('getId')
            ->willReturn($id);
        $order->expects(static::once())
            ->method('getState')
            ->willReturn($state);
        $order->expects(static::never())
            ->method('registerCancellation')
            ->with($comment)
            ->willReturnSelf();
        $order->expects(static::never())
            ->method('save');

        static::assertFalse($this->checkout->cancelCurrentOrder($comment));
    }

    public function testRestoreQuote()
    {
        $this->session->expects(static::once())
            ->method('restoreQuote')
            ->willReturn(true);

        static::assertTrue($this->checkout->restoreQuote());
    }
}
