<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\GiftMessage\Observer\MultishippingEventCreateOrdersObserver as Observer;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;

class MultishippingEventCreateOrdersObserverTest extends TestCase
{
    /**
     * @var \Magento\GiftMessage\Observer\MultishippingEventCreateOrdersObserver
     */
    protected $multishippingEventCreateOrdersObserver;

    protected function setUp(): void
    {
        $this->multishippingEventCreateOrdersObserver = new Observer();
    }

    public function testMultishippingEventCreateOrders()
    {
        $giftMessageId = 42;
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMock(Event::class, ['getOrder', 'getAddress']);
        $addressMock = $this->createPartialMock(Address::class, ['getGiftMessageId']);
        $orderMock = $this->createPartialMock(Order::class, ['setGiftMessageId']);
        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getAddress')->willReturn($addressMock);
        $addressMock->expects($this->once())->method('getGiftMessageId')->willReturn($giftMessageId);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setGiftMessageId')->with($giftMessageId);
        $this->assertEquals(
            $this->multishippingEventCreateOrdersObserver,
            $this->multishippingEventCreateOrdersObserver->execute($observerMock)
        );
    }
}
