<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GiftMessage\Observer\SalesEventQuoteSubmitBeforeObserver as Observer;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SalesEventQuoteSubmitBeforeObserverTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\GiftMessage\Observer\SalesEventQuoteSubmitBeforeObserver
     */
    protected $salesEventQuoteSubmitBeforeObserver;

    protected function setUp(): void
    {
        $this->salesEventQuoteSubmitBeforeObserver = new Observer();
    }

    public function testSalesEventQuoteSubmitBefore()
    {
        $giftMessageId = 42;
        $observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $eventMock = $this->createPartialMockWithReflection(
            Event::class,
            ['getOrder', 'getQuote']
        );
        $quoteMock = $this->createPartialMockWithReflection(
            Quote::class,
            ['getGiftMessageId']
        );
        $orderMock = $this->createPartialMockWithReflection(
            Order::class,
            ['setGiftMessageId']
        );
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
