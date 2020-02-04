<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\GiftMessage\Observer\MultishippingEventCreateOrdersObserver as Observer;

class MultishippingEventCreateOrdersObserverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GiftMessage\Observer\MultishippingEventCreateOrdersObserver
     */
    protected $multishippingEventCreateOrdersObserver;

    protected function setUp()
    {
        $this->multishippingEventCreateOrdersObserver = new Observer();
    }

    public function testMultishippingEventCreateOrders()
    {
        $giftMessageId = 42;
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMock(\Magento\Framework\Event::class, ['getOrder', 'getAddress']);
        $addressMock = $this->createPartialMock(\Magento\Quote\Model\Quote\Address::class, ['getGiftMessageId']);
        $orderMock = $this->createPartialMock(\Magento\Sales\Model\Order::class, ['setGiftMessageId']);
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
