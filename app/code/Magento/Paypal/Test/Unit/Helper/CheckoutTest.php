<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Paypal\Helper\Checkout
 */
namespace Magento\Paypal\Test\Unit\Helper;

use Magento\Checkout\Model\Session;
use Magento\Paypal\Helper\Checkout;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CheckoutTest extends TestCase
{
    /**
     * @var Session|MockObject
     */
    private $session;

    /**
     * @var Checkout
     */
    private $checkout;

    protected function setUp(): void
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
