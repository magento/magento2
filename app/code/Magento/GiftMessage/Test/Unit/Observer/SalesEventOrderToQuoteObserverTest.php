<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Observer;

use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Framework\Message\MessageInterface;
use Magento\GiftMessage\Helper\Message;
use Magento\GiftMessage\Model\Message as MessageModel;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Observer\SalesEventOrderToQuoteObserver;
use Magento\Quote\Model\Quote;
use Magento\Sales\Model\Order;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SalesEventOrderToQuoteObserverTest extends TestCase
{
    /**
     * @var SalesEventOrderToQuoteObserver
     */
    private $observer;

    /**
     * @var MessageFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var Message|MockObject
     */
    private $giftMessageMock;

    /**
     * @var Observer|MockObject
     */
    private $observerMock;

    /**
     * @var Event|MockObject
     */
    private $eventMock;

    /**
     * @var Order|MockObject
     */
    private $orderMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var MessageInterface|MockObject
     */
    private $messageMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageFactoryMock = $this->createMock(MessageFactory::class);
        $this->giftMessageMock = $this->createMock(Message::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->addMethods(['getOrder', 'getQuote'])
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(['getReordered', 'getGiftMessageId'])
            ->onlyMethods(['getStore'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->quoteMock = $this->getMockBuilder(Quote::class)
            ->addMethods(['setGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->storeMock = $this->createMock(Store::class);
        $this->messageMock = $this->createMock(MessageModel::class);

        $this->observer = new SalesEventOrderToQuoteObserver(
            $this->messageFactoryMock,
            $this->giftMessageMock
        );
    }

    /**
     * Tests duplicating gift message from order to quote
     *
     * @dataProvider giftMessageDataProvider
     *
     * @param bool $orderIsReordered
     * @param bool $isMessagesAllowed
     */
    public function testExecute(bool $orderIsReordered, bool $isMessagesAllowed): void
    {
        $giftMessageId = 1;
        $newGiftMessageId = 2;

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);
        $this->observerMock
            ->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        if (!$orderIsReordered && $isMessagesAllowed) {
            $this->eventMock
                ->expects($this->atLeastOnce())
                ->method('getQuote')
                ->willReturn($this->quoteMock);
            $this->orderMock->expects($this->once())
                ->method('getReordered')
                ->willReturn($orderIsReordered);
            $this->orderMock->expects($this->once())
                ->method('getGiftMessageId')
                ->willReturn($giftMessageId);
            $this->giftMessageMock->expects($this->once())
                ->method('isMessagesAllowed')
                ->willReturn($isMessagesAllowed);
            $this->messageFactoryMock->expects($this->once())
                ->method('create')
                ->willReturn($this->messageMock);
            $this->messageMock->expects($this->once())
                ->method('load')
                ->with($giftMessageId)
                ->willReturnSelf();
            $this->messageMock->expects($this->once())
                ->method('setId')
                ->with(null)
                ->willReturnSelf();
            $this->messageMock->expects($this->once())
                ->method('save')
                ->willReturnSelf();
            $this->messageMock->expects($this->once())
                ->method('getId')
                ->willReturn($newGiftMessageId);
            $this->quoteMock->expects($this->once())
                ->method('setGiftMessageId')
                ->with($newGiftMessageId)
                ->willReturnSelf();
        }

        $this->observer->execute($this->observerMock);
    }

    /**
     * Providing gift message data
     *
     * @return array
     */
    public static function giftMessageDataProvider(): array
    {
        return [
            [false, true],
            [true, true],
            [false, true],
            [false, false],
        ];
    }
}
