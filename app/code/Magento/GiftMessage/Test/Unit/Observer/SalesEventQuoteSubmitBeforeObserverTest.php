<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\GiftMessage\Observer\SalesEventQuoteSubmitBeforeObserver as Observer;

class SalesEventQuoteSubmitBeforeObserverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\GiftMessage\Observer\SalesEventQuoteSubmitBeforeObserver
     */
    protected $salesEventQuoteSubmitBeforeObserver;

    protected function setUp()
    {
        $this->salesEventQuoteSubmitBeforeObserver = new Observer();
    }

    public function testSalesEventQuoteSubmitBefore()
    {
        $giftMessageId = 42;
        $observerMock = $this->getMock('\Magento\Framework\Event\Observer');
        $eventMock = $this->getMock('\Magento\Framework\Event', ['getOrder', 'getQuote']);
        $quoteMock = $this->getMock('\Magento\Quote\Model\Quote', ['getGiftMessageId'], [], '', false);
        $orderMock = $this->getMock('\Magento\Sales\Model\Order', ['setGiftMessageId'], [], '', false);
        $observerMock->expects($this->exactly(2))->method('getEvent')->willReturn($eventMock);
        $eventMock->expects($this->once())->method('getQuote')->willReturn($quoteMock);
        $quoteMock->expects($this->once())->method('getGiftMessageId')->willReturn($giftMessageId);
        $eventMock->expects($this->once())->method('getOrder')->willReturn($orderMock);
        $orderMock->expects($this->once())->method('setGiftMessageId')->with($giftMessageId);
        $this->assertEquals(
            $this->salesEventQuoteSubmitBeforeObserver,
            $this->salesEventQuoteSubmitBeforeObserver->execute($observerMock)
        );
    }
}
