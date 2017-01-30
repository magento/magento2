<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\GiftMessage\Observer\MultishippingEventCreateOrdersObserver as Observer;

class MultishippingEventCreateOrdersObserverTest extends \PHPUnit_Framework_TestCase
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
        $observerMock = $this->getMock('\Magento\Framework\Event\Observer');
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getOrder', 'getAddress']);
        $addressMock = $this->getMock('\Magento\Quote\Model\Quote\Address', ['getGiftMessageId'], [], '', false);
        $orderMock = $this->getMock('\Magento\Sales\Model\Order', ['setGiftMessageId'], [], '', false);
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
