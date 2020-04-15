<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GiftMessage\Test\Unit\Model\Plugin;

use Magento\GiftMessage\Helper\Message;
use Magento\GiftMessage\Model\Plugin\QuoteItem as QuoteItemPlugin;
use Magento\Quote\Model\Quote\Item\ToOrderItem;
use Magento\Sales\Model\Order\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class QuoteItemTest extends TestCase
{
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
        $this->orderItemMock = $this->createPartialMock(
            Item::class,
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
        $this->subjectMock = $this->createMock(ToOrderItem::class);
        $this->helperMock = $this->createPartialMock(
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

        $this->quoteItemMock->expects($this->any())->method('getStoreId')->will($this->returnValue($storeId));
        $this->quoteItemMock->expects(
            $this->any()
        )->method(
            'getGiftMessageId'
        )->will(
            $this->returnValue($giftMessageId)
        );

        $this->helperMock->expects(
            $this->once()
        )->method(
            'isMessagesAllowed'
        )->with(
            'item',
            $this->quoteItemMock,
            $storeId
        )->will(
            $this->returnValue($isMessageAvailable)
        );
        $this->orderItemMock->expects($this->once())->method('setGiftMessageId')->with($giftMessageId);
        $this->orderItemMock->expects($this->once())->method('setGiftMessageAvailable')->with($isMessageAvailable);

        $this->assertSame(
            $this->orderItemMock,
            $this->model->afterConvert($this->subjectMock, $this->orderItemMock, $this->quoteItemMock, [])
        );
    }
}
