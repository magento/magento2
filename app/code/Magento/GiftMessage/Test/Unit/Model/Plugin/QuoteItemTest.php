<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\GiftMessage\Helper\Message;
use Magento\GiftMessage\Model\Plugin\QuoteItem as QuoteItemPlugin;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteItemTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var \Magento\Bundle\Model\Plugin\QuoteItem
     */
    protected $model;

    /**
     * @var MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var MockObject
     */
    protected $orderItemMock;

    /**
     * @var MockObject
     */
    protected $helperMock;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->orderItemMock = $this->createPartialMockWithReflection(
            Item::class,
            ['setGiftMessageId', 'setGiftMessageAvailable', '__wakeup']
        );
        $this->quoteItemMock = $this->createPartialMockWithReflection(
            QuoteItem::class,
            ['getGiftMessageId', 'getStoreId', '__wakeup']
        );
        $orderItems = $this->orderItemMock;
        $this->closureMock = function () use ($orderItems) {
            return $orderItems;
        };
        $this->subjectMock = $this->createMock(ToOrderItem::class);
        $this->helperMock = $this->createPartialMockWithReflection(
            Message::class,
            ['setGiftMessageId', 'isMessagesAllowed']
        );
        $this->model = new QuoteItemPlugin($this->helperMock);
    }

    public function testAfterItemToOrderItem()
    {
        $storeId = 1;
        $giftMessageId = 1;
        $isMessageAvailable = true;

        $this->quoteItemMock->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $this->quoteItemMock->expects(
            $this->any()
        )->method(
            'getGiftMessageId'
        )->willReturn(
            $giftMessageId
        );

        $this->helperMock->expects(
            $this->once()
        )->method(
            'isMessagesAllowed'
        )->with(
            'item',
            $this->quoteItemMock,
            $storeId
        )->willReturn(
            $isMessageAvailable
        );
        $this->orderItemMock->expects($this->once())
            ->method('setGiftMessageId')->with($giftMessageId);
        $this->orderItemMock->expects($this->once())
            ->method('setGiftMessageAvailable')->with($isMessageAvailable);

        $this->assertSame(
            $this->orderItemMock,
            $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock, [])
        );
    }
}
