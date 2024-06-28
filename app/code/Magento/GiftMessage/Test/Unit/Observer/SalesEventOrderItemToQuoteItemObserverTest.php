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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GiftMessage\Helper\Message as MessageHelper;
use Magento\GiftMessage\Model\Message as MessageModel;
use Magento\GiftMessage\Model\MessageFactory;
use Magento\GiftMessage\Observer\SalesEventOrderItemToQuoteItemObserver;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Store\Model\Store;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SalesEventOrderItemToQuoteItemObserverTest extends TestCase
{
    /**
     * Stub message id
     */
    private const STUB_MESSAGE_ID = 1;

    /**
     * Stub new message id
     */
    private const STUB_NEW_MESSAGE_ID = 2;

    /**
     * @var SalesEventOrderItemToQuoteItemObserver
     */
    private $observer;

    /**
     * @var MessageFactory|MockObject
     */
    private $messageFactoryMock;

    /**
     * @var MessageHelper|MockObject
     */
    private $giftMessageHelperMock;

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
     * @var OrderItem|MockObject
     */
    private $orderItemMock;

    /**
     * @var Store|MockObject
     */
    private $storeMock;

    /**
     * @var MessageInterface|MockObject
     */
    private $messageMock;

    /**
     * @var QuoteItem|MockObject
     */
    private $quoteItemMock;

    /**
     * Prepare environment for test
     */
    protected function setUp(): void
    {
        $this->messageFactoryMock = $this->getMockBuilder(MessageFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $this->giftMessageHelperMock = $this->createMock(MessageHelper::class);
        $this->observerMock = $this->createMock(Observer::class);
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->addMethods(['getOrderItem', 'getQuoteItem'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderItemMock = $this->getMockBuilder(OrderItem::class)
            ->addMethods(['getGiftMessageId'])
            ->onlyMethods(['getOrder', 'getStoreId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->quoteItemMock = $this->getMockBuilder(QuoteItem::class)
            ->addMethods(['setGiftMessageId'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->addMethods(['getReordered'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->storeMock = $this->createMock(Store::class);
        $this->messageMock = $this->createMock(MessageModel::class);

        $this->eventMock->expects($this->atLeastOnce())
            ->method('getOrderItem')
            ->willReturn($this->orderItemMock);

        $this->orderItemMock->expects($this->atLeastOnce())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->observerMock->expects($this->atLeastOnce())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $objectManager = new ObjectManager($this);

        $this->observer = $objectManager->getObject(
            SalesEventOrderItemToQuoteItemObserver::class,
            [
                'messageFactory' => $this->messageFactoryMock,
                'giftMessageMessage' => $this->giftMessageHelperMock
            ]
        );
    }

    /**
     * Test when the order is reorder
     */
    public function testReorder()
    {
        $this->orderMock->expects($this->once())
            ->method('getReordered')
            ->willReturn(true);

        $this->giftMessageHelperMock->expects($this->never())
            ->method('isMessagesAllowed');

        $this->eventMock
            ->expects($this->never())
            ->method('getQuoteItem')
            ->willReturn($this->quoteItemMock);

        /** Run observer */
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test when the order is new reorder and gift message is not allowed
     */
    public function testNewOrderWhenGiftMessageIsNotAllowed()
    {
        $this->orderMock->expects($this->once())
            ->method('getReordered')
            ->willReturn(false);

        $this->giftMessageHelperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->willReturn(false);

        $this->eventMock
            ->expects($this->never())
            ->method('getQuoteItem')
            ->willReturn($this->quoteItemMock);

        /** Run observer */
        $this->observer->execute($this->observerMock);
    }

    /**
     * Test when the order is new reorder and gift message is allowed
     */
    public function testNewOrderWhenGiftMessageIsAllowed()
    {
        $this->orderMock->expects($this->once())
            ->method('getReordered')
            ->willReturn(false);

        $this->giftMessageHelperMock->expects($this->once())
            ->method('isMessagesAllowed')
            ->willReturn(true);

        $this->eventMock
            ->expects($this->atLeastOnce())
            ->method('getQuoteItem')
            ->willReturn($this->quoteItemMock);

        $this->orderItemMock->expects($this->once())
            ->method('getGiftMessageId')
            ->willReturn(self::STUB_MESSAGE_ID);

        $this->messageFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($this->messageMock);
        $this->messageMock->expects($this->once())
            ->method('load')
            ->with(self::STUB_MESSAGE_ID)
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
            ->willReturn(self::STUB_NEW_MESSAGE_ID);
        $this->quoteItemMock->expects($this->once())
            ->method('setGiftMessageId')
            ->with(self::STUB_NEW_MESSAGE_ID)
            ->willReturnSelf();

        /** Run observer */
        $this->observer->execute($this->observerMock);
    }
}
