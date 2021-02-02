<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

class QuoteItemTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Bundle\Model\Plugin\QuoteItem
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $quoteItemMock;

    /**
     * @var \Closure
     */
    protected $closureMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $orderItemMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $helperMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $subjectMock;

    protected function setUp(): void
    {
        $this->orderItemMock = $this->createPartialMock(
            \Magento\Sales\Model\Order\Item::class,
            ['setGiftMessageId', 'setGiftMessageAvailable', '__wakeup']
        );
        $this->quoteItemMock = $this->createPartialMock(
            \Magento\Quote\Model\Quote\Item::class,
            ['getGiftMessageId', 'getStoreId', '__wakeup']
        );
        $orderItems = $this->orderItemMock;
        $this->closureMock = function () use ($orderItems) {
            return $orderItems;
        };
        $this->subjectMock = $this->createMock(\Magento\Quote\Model\Quote\Item\ToOrderItem::class);
        $this->helperMock = $this->createPartialMock(
            \Magento\GiftMessage\Helper\Message::class,
            ['setGiftMessageId', 'isMessagesAllowed']
        );
        $this->model = new \Magento\GiftMessage\Model\Plugin\QuoteItem($this->helperMock);
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
        $this->orderItemMock->expects($this->once())->method('setGiftMessageId')->with($giftMessageId);
        $this->orderItemMock->expects($this->once())->method('setGiftMessageAvailable')->with($isMessageAvailable);

        $this->assertSame(
            $this->orderItemMock,
            $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock, [])
        );
    }
}
